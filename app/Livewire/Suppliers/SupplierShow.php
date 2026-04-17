<?php

namespace App\Livewire\Suppliers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierContact;
use App\Models\SupplierEvaluation;
use App\Models\SupplierNote;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class SupplierShow extends Component
{
    public Supplier $supplier;
    public string $activeTab = 'contacts';

    // Notas
    public bool   $showNoteForm = false;
    public string $noteType     = 'note';
    public string $noteTitle    = '';
    public string $noteBody     = '';

    // Contactos
    public bool   $showContactForm    = false;
    public string $contactFirstName   = '';
    public string $contactLastName    = '';
    public string $contactPosition    = '';
    public string $contactPhone       = '';
    public string $contactEmail       = '';
    public bool   $contactIsPrimary   = false;

    // Evaluaciones
    public bool   $showEvalForm       = false;
    public string $evalOrderId        = '';
    public int    $evalPrice          = 3;
    public int    $evalQuality        = 3;
    public int    $evalDelivery       = 3;
    public int    $evalCompliance     = 3;
    public string $evalNotes          = '';
    public string $evalDate           = '';

    public function mount($supplier): void
    {
        $this->supplier = $supplier instanceof Supplier
            ? $supplier
            : Supplier::with([
                'phones', 'emails', 'contacts',
                'notes.user', 'assignedTo', 'bankAccounts',
            ])->findOrFail($supplier);

        $this->evalDate = now()->toDateString();
    }

    // ── Notas ──────────────────────────────────────────────────────────────

    public function saveNote(): void
    {
        $this->validate([
            'noteTitle' => 'required|string|max:255',
            'noteType'  => 'required|in:note,call,email,meeting,task',
            'noteBody'  => 'nullable|string',
        ]);

        $this->supplier->notes()->create([
            'user_id'  => auth()->id(),
            'type'     => $this->noteType,
            'title'    => $this->noteTitle,
            'body'     => $this->noteBody,
            'noted_at' => now(),
        ]);

        $this->reset(['noteTitle', 'noteBody', 'noteType', 'showNoteForm']);
        $this->supplier->load('notes.user');
        session()->flash('success', 'Nota agregada.');
    }

    public function deleteNote(int $id): void
    {
        SupplierNote::findOrFail($id)->delete();
        $this->supplier->load('notes.user');
    }

    // ── Contactos ──────────────────────────────────────────────────────────

    public function saveContact(): void
    {
        $this->validate([
            'contactFirstName' => 'required|string|max:255',
            'contactLastName'  => 'nullable|string|max:255',
            'contactPosition'  => 'nullable|string|max:255',
            'contactPhone'     => 'nullable|string|max:20',
            'contactEmail'     => 'nullable|email|max:255',
        ]);

        $this->supplier->contacts()->create([
            'first_name' => $this->contactFirstName,
            'last_name'  => $this->contactLastName,
            'position'   => $this->contactPosition,
            'phone'      => $this->contactPhone,
            'email'      => $this->contactEmail,
            'is_primary' => $this->contactIsPrimary,
        ]);

        $this->reset(['contactFirstName', 'contactLastName', 'contactPosition',
                      'contactPhone', 'contactEmail', 'contactIsPrimary', 'showContactForm']);
        $this->supplier->load('contacts');
        session()->flash('success', 'Contacto agregado.');
    }

    public function deleteContact(int $id): void
    {
        SupplierContact::findOrFail($id)->delete();
        $this->supplier->load('contacts');
    }

    // ── Evaluaciones ───────────────────────────────────────────────────────

    public function saveEvaluation(): void
    {
        $this->validate([
            'evalPrice'      => 'required|integer|min:1|max:5',
            'evalQuality'    => 'required|integer|min:1|max:5',
            'evalDelivery'   => 'required|integer|min:1|max:5',
            'evalCompliance' => 'required|integer|min:1|max:5',
            'evalDate'       => 'required|date',
            'evalNotes'      => 'nullable|string|max:1000',
            'evalOrderId'    => 'nullable|exists:purchase_orders,id',
        ]);

        SupplierEvaluation::create([
            'company_id'        => auth()->user()->company_id,
            'supplier_id'       => $this->supplier->id,
            'purchase_order_id' => $this->evalOrderId ?: null,
            'evaluated_by'      => auth()->id(),
            'score_price'       => $this->evalPrice,
            'score_quality'     => $this->evalQuality,
            'score_delivery'    => $this->evalDelivery,
            'score_compliance'  => $this->evalCompliance,
            'notes'             => $this->evalNotes ?: null,
            'evaluated_at'      => $this->evalDate,
        ]);

        $this->reset(['showEvalForm', 'evalOrderId', 'evalNotes']);
        $this->evalPrice = $this->evalQuality = $this->evalDelivery = $this->evalCompliance = 3;
        $this->evalDate  = now()->toDateString();
        unset($this->evaluations, $this->kpis);

        session()->flash('success', 'Evaluación registrada.');
    }

    public function deleteEvaluation(int $id): void
    {
        SupplierEvaluation::where('supplier_id', $this->supplier->id)->findOrFail($id)->delete();
        unset($this->evaluations, $this->kpis);
    }

    // ── Computed ───────────────────────────────────────────────────────────

    #[Computed]
    public function purchaseOrders()
    {
        return PurchaseOrder::where('supplier_id', $this->supplier->id)
            ->with('items')
            ->latest()
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function evaluations()
    {
        return SupplierEvaluation::where('supplier_id', $this->supplier->id)
            ->with(['evaluatedBy', 'purchaseOrder'])
            ->latest('evaluated_at')
            ->get();
    }

    #[Computed]
    public function kpis(): array
    {
        $orders = $this->purchaseOrders;

        $totalSpent = PurchaseOrder::where('supplier_id', $this->supplier->id)
            ->whereIn('status', ['received', 'invoiced', 'partial_received'])
            ->sum('total');

        $totalOrders = PurchaseOrder::where('supplier_id', $this->supplier->id)->count();

        $completedOrders = PurchaseOrder::where('supplier_id', $this->supplier->id)
            ->whereIn('status', ['received', 'invoiced'])->count();

        $completionRate = $totalOrders > 0
            ? round($completedOrders / $totalOrders * 100, 1)
            : 0;

        $evals = $this->evaluations;
        $avgScore   = $evals->count() > 0 ? round($evals->avg('score_overall'), 2) : null;
        $avgPrice   = $evals->count() > 0 ? round($evals->avg('score_price'), 1) : null;
        $avgQuality = $evals->count() > 0 ? round($evals->avg('score_quality'), 1) : null;
        $avgDelivery    = $evals->count() > 0 ? round($evals->avg('score_delivery'), 1) : null;
        $avgCompliance  = $evals->count() > 0 ? round($evals->avg('score_compliance'), 1) : null;

        return compact(
            'totalSpent', 'totalOrders', 'completedOrders',
            'completionRate', 'avgScore', 'avgPrice',
            'avgQuality', 'avgDelivery', 'avgCompliance'
        );
    }

    #[Computed]
    public function availableOrders()
    {
        return PurchaseOrder::where('supplier_id', $this->supplier->id)
            ->whereIn('status', ['received', 'invoiced', 'partial_received'])
            ->latest()
            ->get(['id', 'folio', 'created_at', 'total']);
    }

    public function render()
    {
        return view('livewire.suppliers.supplier-show');
    }
}
