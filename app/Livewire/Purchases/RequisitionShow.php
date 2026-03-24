<?php

namespace App\Livewire\Purchases;

use App\Models\PurchaseRequisition;
use App\Models\PurchaseApproval;
use App\Models\PurchaseSetting;
use App\Notifications\PurchaseNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class RequisitionShow extends Component
{
    public PurchaseRequisition $requisition;
    public string $quoteResponse = '';
    public string $quotedAmount = '';
    public string $rejectReason = '';
    public string $approvalComment = '';

    public function mount($requisition): void
    {
        $this->requisition = $requisition instanceof PurchaseRequisition
            ? $requisition
            : PurchaseRequisition::with([
                'items.product', 'requestedBy', 'reviewedBy',
                'approvals.user', 'branch', 'order'
            ])->findOrFail($requisition);
    }

    public function sendQuote(): void
    {
        $this->validate([
            'quoteResponse' => 'required|string',
            'quotedAmount'  => 'required|numeric|min:0',
        ]);

        $this->requisition->update([
            'status'         => 'quoted',
            'quote_response' => $this->quoteResponse,
            'quoted_amount'  => $this->quotedAmount,
            'reviewed_by'    => auth()->id(),
            'quoted_at'      => now(),
        ]);

        // Notificar al requisitor
        $this->requisition->requestedBy->notify(new PurchaseNotification(
            title: 'Cotización lista',
            message: "Tu requisición {$this->requisition->folio} fue cotizada. Revisa y acepta o rechaza.",
            type: 'quote',
            requisitionId: $this->requisition->id,
        ));

        $this->reset(['quoteResponse', 'quotedAmount']);
        $this->requisition->refresh()->load(['items.product', 'requestedBy', 'reviewedBy', 'approvals.user']);
        session()->flash('success', 'Cotización enviada al solicitante.');
    }

    public function acceptQuote(): void
    {
        $settings = PurchaseSetting::where('company_id', auth()->user()->company_id)->first();
        $amount   = $this->requisition->quoted_amount;
        $level    = $settings ? $settings->getApprovalLevel($amount) : 1;

        DB::transaction(function () use ($level) {
            $this->requisition->update(['status' => 'pending_approval']);

            $roles = ['compras'];
            if ($level >= 2) $roles[] = 'administracion';
            if ($level >= 3) $roles[] = 'gerencia';

            foreach ($roles as $role) {
                $approvers = User::where('company_id', auth()->user()->company_id)
                    ->whereHas('roles', fn($q) => $q->where('name', $role))
                    ->get();

                foreach ($approvers as $approver) {
                    PurchaseApproval::create([
                        'purchase_requisition_id' => $this->requisition->id,
                        'user_id'                 => $approver->id,
                        'role'                    => $role,
                        'status'                  => 'pending',
                    ]);

                    $approver->notify(new PurchaseNotification(
                        title: 'Aprobación requerida',
                        message: "La requisición {$this->requisition->folio} requiere tu aprobación.",
                        type: 'approval',
                        requisitionId: $this->requisition->id,
                    ));
                }
            }
        });

        $this->requisition->refresh()->load(['items.product', 'requestedBy', 'reviewedBy', 'approvals.user']);
        session()->flash('success', 'Cotización aceptada. Proceso de aprobación iniciado.');
    }

    public function rejectQuote(): void
    {
        $this->requisition->update(['status' => 'pending_quote']);
        $this->requisition->requestedBy->notify(new PurchaseNotification(
            title: 'Cotización rechazada',
            message: "La cotización de la requisición {$this->requisition->folio} fue rechazada y regresó a compras.",
            type: 'quote_rejected',
            requisitionId: $this->requisition->id,
        ));

        $this->requisition->refresh()->load(['items.product', 'requestedBy', 'reviewedBy', 'approvals.user']);
        session()->flash('success', 'Cotización rechazada. Compras recibirá una notificación.');
    }

    public function approve(): void
    {
        $approval = $this->requisition->approvals
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$approval) return;

        $approval->update([
            'status'       => 'approved',
            'comments'     => $this->approvalComment,
            'responded_at' => now(),
        ]);

        $pendingApprovals = $this->requisition->approvals()->where('status', 'pending')->count();

        if ($pendingApprovals === 0) {
            $this->requisition->update(['status' => 'approved']);
            $this->requisition->requestedBy->notify(new PurchaseNotification(
                title: 'Requisición aprobada',
                message: "Tu requisición {$this->requisition->folio} fue aprobada completamente.",
                type: 'approved',
                requisitionId: $this->requisition->id,
            ));
        }

        $this->reset('approvalComment');
        $this->requisition->refresh()->load(['items.product', 'requestedBy', 'reviewedBy', 'approvals.user']);
        session()->flash('success', 'Aprobación registrada.');
    }

    public function reject(): void
    {
        $approval = $this->requisition->approvals
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->first();

        if (!$approval) return;

        $approval->update([
            'status'       => 'rejected',
            'comments'     => $this->approvalComment,
            'responded_at' => now(),
        ]);

        $this->requisition->update(['status' => 'rejected']);

        $this->requisition->requestedBy->notify(new PurchaseNotification(
            title: 'Requisición rechazada',
            message: "Tu requisición {$this->requisition->folio} fue rechazada.",
            type: 'rejected',
            requisitionId: $this->requisition->id,
        ));

        $this->reset('approvalComment');
        $this->requisition->refresh()->load(['items.product', 'requestedBy', 'reviewedBy', 'approvals.user']);
        session()->flash('success', 'Requisición rechazada.');
    }

    public function render()
    {
        return view('livewire.purchases.requisition-show');
    }
}
