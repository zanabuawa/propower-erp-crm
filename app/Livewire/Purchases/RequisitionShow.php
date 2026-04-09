<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseQuotation;
use App\Models\PurchaseQuotationApproval;
use App\Models\PurchaseRequisition;
use App\Notifications\PurchaseNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class RequisitionShow extends Component
{
    public PurchaseRequisition $requisition;

    // ── Panel de cotización (compras crea preliminar o final) ────────────────
    public bool   $showQuotationForm = false;
    public string $quotationType     = 'preliminary'; // 'preliminary' | 'final'
    public array  $qItems            = [];
    public string $qNotes            = '';

    // ── Acciones del solicitante ─────────────────────────────────────────────
    public string $requesterNotes = '';

    // ── Autorización ────────────────────────────────────────────────────────
    public string $approvalComment  = '';
    public string $signatureData    = ''; // base64 PNG capturado en canvas
    public string $authPassword     = ''; // contraseña de verificación antes de firmar
    public bool   $passwordVerified = false;

    // ── Rechazo ──────────────────────────────────────────────────────────────
    public bool   $showRejectForm  = false;
    public string $rejectReason    = '';

    public function mount($requisition): void
    {
        $this->loadRequisition($requisition);
    }

    private function loadRequisition(mixed $req): void
    {
        $this->requisition = $req instanceof PurchaseRequisition
            ? $req
            : PurchaseRequisition::with([
                'items.product',
                'requestedBy',
                'reviewedBy',
                'rejectedBy',
                'branch',
                'order',
                'preliminaryQuotation.items',
                'preliminaryQuotation.approvals',
                'finalQuotation.items',
                'finalQuotation.approvals',
                'finalQuotation.createdBy',
            ])->findOrFail($req);
    }

    // ── Helpers de rol del usuario actual ───────────────────────────────────

    public function getIsCompradorProperty(): bool
    {
        return auth()->user()->hasRole(['comprador', 'admin', 'super-admin']);
    }

    public function getIsRequesterProperty(): bool
    {
        return $this->requisition->requested_by === auth()->id();
    }

    public function getIsAdminProperty(): bool
    {
        return auth()->user()->hasRole(['admin', 'super-admin']);
    }

    public function getIsGerenteProperty(): bool
    {
        return auth()->user()->hasRole(['gerente', 'super-admin']);
    }

    /**
     * Returns the pending approval record that the current user can act on,
     * based on their role (not their specific user ID).
     */
    private function getPendingApprovalForCurrentUser(): ?PurchaseQuotationApproval
    {
        $finalQuotation = $this->requisition->finalQuotation;
        if (!$finalQuotation) return null;

        $user  = auth()->user();
        $roles = $user->getRoleNames()->toArray();

        return $finalQuotation->approvals
            ->where('status', 'pending')
            ->first(fn($approval) => in_array($approval->role, $roles));
    }

    public function getCanApproveProperty(): bool
    {
        return $this->getPendingApprovalForCurrentUser() !== null;
    }

    // ── Abrir formulario de cotización ───────────────────────────────────────

    public function openQuotationForm(string $type = 'preliminary'): void
    {
        $this->quotationType     = $type;
        $this->showQuotationForm = true;
        $this->showRejectForm    = false;

        // Pre-popular con ítems de la requisición
        $this->qItems = $this->requisition->items->map(fn($i) => [
            'description' => $i->description,
            'quantity'    => (float) $i->quantity,
            'unit'        => $i->unit ?? '',
            'unit_price'  => (float) $i->unit_price,
            'tax_rate'    => 16,
        ])->toArray();

        $this->qNotes = '';
    }

    public function addQItem(): void
    {
        $this->qItems[] = ['description' => '', 'quantity' => 1, 'unit' => '', 'unit_price' => 0, 'tax_rate' => 16];
    }

    public function removeQItem(int $index): void
    {
        array_splice($this->qItems, $index, 1);
        $this->qItems = array_values($this->qItems);
    }

    // ── COMPRAS: crear cotización (preliminar o final) ───────────────────────

    public function saveQuotation(): void
    {
        $this->validate([
            'qItems'               => 'required|array|min:1',
            'qItems.*.description' => 'required|string|max:255',
            'qItems.*.quantity'    => 'required|numeric|min:0.01',
            'qItems.*.unit_price'  => 'required|numeric|min:0',
            'qItems.*.tax_rate'    => 'required|numeric|min:0|max:100',
        ]);

        DB::transaction(function () {
            $subtotal = collect($this->qItems)->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            $tax      = collect($this->qItems)->sum(fn($i) => $i['quantity'] * $i['unit_price'] * ($i['tax_rate'] / 100));
            $total    = $subtotal + $tax;

            // Si ya existe cotización del mismo tipo, actualizarla
            $quotation = $this->requisition->quotations()
                ->where('type', $this->quotationType)->latest()->first();

            if ($quotation) {
                $quotation->items()->delete();
                $quotation->approvals()->delete();
                $quotation->update([
                    'status'          => 'pending',
                    'subtotal'        => $subtotal,
                    'tax'             => $tax,
                    'total'           => $total,
                    'notes'           => $this->qNotes,
                    'requester_notes' => null,
                ]);
            } else {
                $quotation = PurchaseQuotation::create([
                    'company_id'              => auth()->user()->company_id,
                    'purchase_requisition_id' => $this->requisition->id,
                    'type'                    => $this->quotationType,
                    'status'                  => 'pending',
                    'subtotal'                => $subtotal,
                    'tax'                     => $tax,
                    'total'                   => $total,
                    'notes'                   => $this->qNotes,
                    'created_by'              => auth()->id(),
                ]);
            }

            foreach ($this->qItems as $item) {
                $quotation->items()->create([
                    'description' => $item['description'],
                    'quantity'    => $item['quantity'],
                    'unit'        => $item['unit'] ?? null,
                    'unit_price'  => $item['unit_price'],
                    'tax_rate'    => $item['tax_rate'],
                    'subtotal'    => $item['quantity'] * $item['unit_price'],
                ]);
            }

            if ($this->quotationType === 'preliminary') {
                $this->requisition->update([
                    'status'      => 'preliminary_quoted',
                    'reviewed_by' => auth()->id(),
                ]);

                $this->requisition->requestedBy->notify(new PurchaseNotification(
                    title: 'Cotización preliminar lista',
                    message: "La requisición {$this->requisition->folio} tiene una cotización preliminar. Revisa y confirma o devuelve con comentarios.",
                    type: 'preliminary_quotation',
                    requisitionId: $this->requisition->id,
                ));
            } else {
                // Cotización final → iniciar proceso de autorización
                $this->requisition->update(['status' => 'pending_auth']);
                $this->startAuthorizationProcess($quotation);
            }
        });

        $this->showQuotationForm = false;
        $this->reset(['qItems', 'qNotes']);
        $this->loadRequisition($this->requisition->id);

        $msg = $this->quotationType === 'preliminary'
            ? 'Cotización preliminar enviada al solicitante.'
            : 'Cotización final creada. Proceso de autorización iniciado.';
        session()->flash('success', $msg);
    }

    // ── COMPRAS: rechazar requisición ────────────────────────────────────────

    public function rejectRequisition(): void
    {
        $this->validate(['rejectReason' => 'required|string|min:5']);

        $this->requisition->update([
            'status'        => 'rejected',
            'reject_reason' => $this->rejectReason,
            'rejected_by'   => auth()->id(),
            'rejected_at'   => now(),
        ]);

        $this->requisition->requestedBy->notify(new PurchaseNotification(
            title: 'Requisición rechazada',
            message: "Tu requisición {$this->requisition->folio} fue rechazada por compras. Motivo: {$this->rejectReason}",
            type: 'requisition_rejected',
            requisitionId: $this->requisition->id,
        ));

        $this->showRejectForm = false;
        $this->reset('rejectReason');
        $this->loadRequisition($this->requisition->id);
        session()->flash('success', 'Requisición rechazada. Se notificó al solicitante.');
    }

    // ── SOLICITANTE: confirmar cotización preliminar ──────────────────────────

    public function confirmQuotation(): void
    {
        $this->requisition->update([
            'status'       => 'requester_confirmed',
            'confirmed_at' => now(),
        ]);

        if ($this->requisition->preliminaryQuotation) {
            $this->requisition->preliminaryQuotation->update(['status' => 'confirmed']);
        }

        $compradores = User::where('company_id', auth()->user()->company_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'comprador'))
            ->get();

        foreach ($compradores as $user) {
            $user->notify(new PurchaseNotification(
                title: 'Solicitante confirmó cotización',
                message: "El solicitante confirmó la cotización preliminar de {$this->requisition->folio}. Procede con la cotización final.",
                type: 'requester_confirmed',
                requisitionId: $this->requisition->id,
            ));
        }

        $this->loadRequisition($this->requisition->id);
        session()->flash('success', 'Cotización confirmada. Compras creará la cotización final.');
    }

    // ── SOLICITANTE: devolver cotización con notas ───────────────────────────

    public function returnQuotation(): void
    {
        $this->validate(['requesterNotes' => 'required|string|min:5']);

        $this->requisition->update(['status' => 'requester_returned']);

        if ($this->requisition->preliminaryQuotation) {
            $this->requisition->preliminaryQuotation->update([
                'status'          => 'returned',
                'requester_notes' => $this->requesterNotes,
                'responded_at'    => now(),
            ]);
        }

        $compradores = User::where('company_id', auth()->user()->company_id)
            ->whereHas('roles', fn($q) => $q->where('name', 'comprador'))
            ->get();

        foreach ($compradores as $user) {
            $user->notify(new PurchaseNotification(
                title: 'Cotización devuelta por solicitante',
                message: "El solicitante devolvió la cotización de {$this->requisition->folio} con comentarios. Revisar y reenviar.",
                type: 'quotation_returned',
                requisitionId: $this->requisition->id,
            ));
        }

        $this->reset('requesterNotes');
        $this->loadRequisition($this->requisition->id);
        session()->flash('success', 'Cotización devuelta. Compras revisará tus comentarios.');
    }

    // ── Proceso de autorización (1 registro por rol, no por usuario) ─────────

    private function startAuthorizationProcess(PurchaseQuotation $quotation): void
    {
        $requiredRoles = $quotation->getRequiredRoles();
        $level         = 1;

        foreach ($requiredRoles as $role) {
            // Un solo registro por rol (cualquier usuario con ese rol puede aprobarlo)
            PurchaseQuotationApproval::create([
                'purchase_quotation_id' => $quotation->id,
                'user_id'               => null, // se asigna cuando alguien aprueba/rechaza
                'role'                  => $role,
                'level'                 => $level,
                'status'                => 'pending',
            ]);

            // Notificar a todos los usuarios con ese rol
            $users = User::where('company_id', auth()->user()->company_id)
                ->whereHas('roles', fn($q) => $q->where('name', $role))
                ->get();

            foreach ($users as $user) {
                $user->notify(new PurchaseNotification(
                    title: 'Cotización pendiente de autorización',
                    message: "La requisición {$this->requisition->folio} requiere autorización de {$role}. Monto: $" . number_format((float) $quotation->total, 2),
                    type: 'quotation_approval_required',
                    requisitionId: $this->requisition->id,
                ));
            }

            $level++;
        }

        // Notificar al solicitante
        $this->requisition->requestedBy->notify(new PurchaseNotification(
            title: 'Tu requisición está siendo autorizada',
            message: "La requisición {$this->requisition->folio} está en proceso de autorización ({$quotation->auth_level} nivel(es) requerido(s)).",
            type: 'pending_authorization',
            requisitionId: $this->requisition->id,
        ));
    }

    // ── AUTORIZADORES: verificar contraseña antes de firmar ─────────────────

    public function verifyAuthPassword(): void
    {
        if (!Hash::check($this->authPassword, auth()->user()->password)) {
            $this->addError('authPassword', 'Contraseña incorrecta.');
            return;
        }

        $this->passwordVerified = true;
        $this->authPassword     = '';
        $this->resetErrorBag('authPassword');
    }

    // ── AUTORIZADORES: aprobar cotización final ──────────────────────────────

    public function approveQuotation(): void
    {
        if (!$this->passwordVerified) {
            $this->addError('authPassword', 'Debes verificar tu contraseña primero.');
            return;
        }

        $approval = $this->getPendingApprovalForCurrentUser();
        if (!$approval) return;

        // Sello digital HMAC-SHA256 (user + quotation + timestamp + signature PNG)
        $signatureHash = $this->signatureData
            ? hash_hmac(
                'sha256',
                implode('|', [auth()->id(), $approval->purchase_quotation_id, $approval->id, now()->toIso8601String(), $this->signatureData]),
                config('app.key')
            )
            : null;

        $approval->update([
            'user_id'        => auth()->id(),
            'status'         => 'approved',
            'comments'       => $this->approvalComment,
            'signature'      => $this->signatureData ?: auth()->user()->signature,
            'signature_hash' => $signatureHash,
            'decided_at'     => now(),
        ]);

        $finalQuotation   = $this->requisition->finalQuotation;
        $remainingPending = $finalQuotation->approvals()->where('status', 'pending')->count();

        if ($remainingPending === 0) {
            // Todos los roles autorizaron → evaluación completa
            $finalQuotation->update(['status' => 'authorized']);
            $this->requisition->update(['status' => 'authorized']);

            // Notificar al solicitante: evaluación aprobada, pronto habrá OC
            $this->requisition->requestedBy->notify(new PurchaseNotification(
                title: '¡Requisición aprobada en evaluación!',
                message: "Tu requisición {$this->requisition->folio} superó el proceso de evaluación. El área de compras generará la orden de compra a la brevedad.",
                type: 'requisition_authorized',
                requisitionId: $this->requisition->id,
            ));
        }

        $this->reset(['approvalComment', 'signatureData', 'passwordVerified']);
        $this->loadRequisition($this->requisition->id);
        session()->flash('success', 'Autorización registrada.');
    }

    // ── AUTORIZADORES: rechazar cotización final ─────────────────────────────

    public function rejectQuotation(): void
    {
        $approval = $this->getPendingApprovalForCurrentUser();
        if (!$approval) return;

        $approval->update([
            'user_id'    => auth()->id(),
            'status'     => 'rejected',
            'comments'   => $this->approvalComment,
            'signature'  => $this->signatureData ?: null,
            'decided_at' => now(),
        ]);

        $this->requisition->finalQuotation->update(['status' => 'rejected']);
        $this->requisition->update([
            'status'      => 'rejected',
            'rejected_at' => now(),
        ]);

        $this->requisition->requestedBy->notify(new PurchaseNotification(
            title: 'Requisición rechazada en autorización',
            message: "Tu requisición {$this->requisition->folio} fue rechazada durante el proceso de autorización por el rol: {$approval->role}.",
            type: 'requisition_rejected',
            requisitionId: $this->requisition->id,
        ));

        $this->reset('approvalComment');
        $this->loadRequisition($this->requisition->id);
        session()->flash('success', 'Cotización rechazada.');
    }

    public function render()
    {
        return view('livewire.purchases.requisition-show');
    }
}
