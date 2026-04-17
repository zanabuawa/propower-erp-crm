<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\ScheduledPayment;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ScheduledPaymentIndex extends Component
{
    use WithPagination;

    public string $search          = '';
    public string $filterStatus    = '';
    public string $filterCategory  = '';
    public string $filterAccount   = '';
    public string $view            = 'list'; // list | calendar

    // Formulario inline (crear/editar)
    public bool   $showForm        = false;
    public ?int   $editId          = null;

    public string $concept         = '';
    public string $category        = 'otro';
    public string $frequency       = 'once';
    public string $amount          = '';
    public string $currency        = 'MXN';
    public string $scheduledDate   = '';
    public string $endDate         = '';
    public ?int   $financeAccountId = null;
    public ?int   $supplierId      = null;
    public ?int   $purchaseInvoiceId = null;
    public string $reference       = '';
    public string $notes           = '';

    // Ejecutar pago
    public bool   $showExecuteModal  = false;
    public ?int   $executeId         = null;
    public string $executeNotes      = '';

    // Cancelar
    public bool   $showCancelModal   = false;
    public ?int   $cancelId          = null;

    public array  $accounts          = [];
    public array  $suppliers         = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->scheduledDate = now()->toDateString();

        $this->accounts = FinanceAccount::where('company_id', $companyId)
            ->where('is_active', true)->orderBy('name')
            ->get(['id', 'name', 'currency', 'current_balance'])->toArray();

        $this->suppliers = Supplier::where('company_id', $companyId)
            ->where('status', 'active')->orderBy('name')
            ->get(['id', 'name'])->toArray();
    }

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingFilterStatus(): void  { $this->resetPage(); }
    public function updatingFilterCategory(): void{ $this->resetPage(); }

    // ── Formulario ────────────────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->editId   = null;
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $sp = ScheduledPayment::findOrFail($id);
        $this->editId             = $id;
        $this->concept            = $sp->concept;
        $this->category           = $sp->category;
        $this->frequency          = $sp->frequency;
        $this->amount             = (string)(float) $sp->amount;
        $this->currency           = $sp->currency;
        $this->scheduledDate      = $sp->scheduled_date->toDateString();
        $this->endDate            = $sp->end_date?->toDateString() ?? '';
        $this->financeAccountId   = $sp->finance_account_id;
        $this->supplierId         = $sp->supplier_id;
        $this->purchaseInvoiceId  = $sp->purchase_invoice_id;
        $this->reference          = $sp->reference ?? '';
        $this->notes              = $sp->notes ?? '';
        $this->showForm           = true;
    }

    private function resetForm(): void
    {
        $this->concept           = '';
        $this->category          = 'otro';
        $this->frequency         = 'once';
        $this->amount            = '';
        $this->currency          = 'MXN';
        $this->scheduledDate     = now()->toDateString();
        $this->endDate           = '';
        $this->financeAccountId  = null;
        $this->supplierId        = null;
        $this->purchaseInvoiceId = null;
        $this->reference         = '';
        $this->notes             = '';
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'concept'          => 'required|string|max:255',
            'amount'           => 'required|numeric|min:0.01',
            'scheduledDate'    => 'required|date',
            'financeAccountId' => 'nullable|exists:finance_accounts,id',
            'endDate'          => 'nullable|date|after_or_equal:scheduledDate',
        ], [
            'concept.required'       => 'El concepto es obligatorio.',
            'amount.required'        => 'El monto es obligatorio.',
            'scheduledDate.required' => 'La fecha de pago es obligatoria.',
        ]);

        $companyId = auth()->user()->company_id;

        if ($this->editId) {
            ScheduledPayment::where('id', $this->editId)
                ->where('company_id', $companyId)
                ->update($this->formData());
            session()->flash('success', 'Pago programado actualizado.');
        } else {
            $folio = 'PP-' . str_pad(
                ScheduledPayment::where('company_id', $companyId)->count() + 1,
                6, '0', STR_PAD_LEFT
            );
            ScheduledPayment::create(array_merge($this->formData(), [
                'company_id' => $companyId,
                'folio'      => $folio,
                'status'     => 'pending',
                'created_by' => auth()->id(),
            ]));
            session()->flash('success', 'Pago programado registrado.');
        }

        $this->showForm = false;
        $this->resetForm();
    }

    private function formData(): array
    {
        return [
            'concept'              => $this->concept,
            'category'             => $this->category,
            'frequency'            => $this->frequency,
            'amount'               => $this->amount,
            'currency'             => $this->currency,
            'scheduled_date'       => $this->scheduledDate,
            'end_date'             => $this->endDate ?: null,
            'finance_account_id'   => $this->financeAccountId,
            'supplier_id'          => $this->supplierId ?: null,
            'purchase_invoice_id'  => $this->purchaseInvoiceId ?: null,
            'reference'            => $this->reference ?: null,
            'notes'                => $this->notes ?: null,
        ];
    }

    // ── Ejecutar pago ─────────────────────────────────────────────────────
    public function openExecute(int $id): void
    {
        $this->executeId    = $id;
        $this->executeNotes = '';
        $this->showExecuteModal = true;
    }

    public function executePayment(): void
    {
        $sp = ScheduledPayment::where('id', $this->executeId)
            ->where('company_id', auth()->user()->company_id)
            ->firstOrFail();

        try {
            DB::transaction(function () use ($sp) {
                // Generar transacción de egreso
                $tx = FinanceTransaction::create([
                    'account_id'       => $sp->finance_account_id,
                    'registered_by'    => auth()->id(),
                    'folio'            => 'TXN-' . $sp->folio,
                    'type'             => 'egreso',
                    'concept'          => $sp->concept,
                    'category'         => $sp->category === 'proveedor' ? 'compra' : ($sp->category === 'nomina' ? 'nómina' : 'otro'),
                    'amount'           => $sp->amount,
                    'currency'         => $sp->currency,
                    'exchange_rate'    => $sp->exchange_rate,
                    'transaction_date' => now()->toDateString(),
                    'reference'        => $sp->folio,
                    'status'           => 'confirmado',
                    'notes'            => trim(($sp->notes ?? '') . ' ' . ($this->executeNotes ?? '')),
                ]);

                $sp->update([
                    'status'                 => 'paid',
                    'paid_at'                => now(),
                    'executed_by'            => auth()->id(),
                    'finance_transaction_id' => $tx->id,
                    'notes'                  => trim(($sp->notes ?? '') . "\n" . ($this->executeNotes ?? '')),
                ]);

                // Si es recurrente, crear el siguiente pago programado
                if ($sp->frequency !== 'once') {
                    $next = $sp->nextOccurrence();
                    if ($next && (! $sp->end_date || $next->lte($sp->end_date))) {
                        $folio = 'PP-' . str_pad(
                            ScheduledPayment::where('company_id', $sp->company_id)->count() + 1,
                            6, '0', STR_PAD_LEFT
                        );
                        ScheduledPayment::create([
                            'company_id'          => $sp->company_id,
                            'finance_account_id'  => $sp->finance_account_id,
                            'supplier_id'         => $sp->supplier_id,
                            'purchase_invoice_id' => $sp->purchase_invoice_id,
                            'created_by'          => $sp->created_by,
                            'folio'               => $folio,
                            'concept'             => $sp->concept,
                            'category'            => $sp->category,
                            'frequency'           => $sp->frequency,
                            'status'              => 'pending',
                            'amount'              => $sp->amount,
                            'currency'            => $sp->currency,
                            'exchange_rate'       => $sp->exchange_rate,
                            'scheduled_date'      => $next->toDateString(),
                            'end_date'            => $sp->end_date?->toDateString(),
                            'reference'           => $sp->reference,
                            'notes'               => $sp->notes,
                        ]);
                    }
                }
            });

            $this->showExecuteModal = false;
            $this->executeId        = null;
            $this->executeNotes     = '';
            session()->flash('success', 'Pago ejecutado y transacción registrada.');

        } catch (\Throwable $e) {
            Log::error('ScheduledPaymentIndex executePayment', ['error' => $e->getMessage()]);
            session()->flash('error', 'Error al ejecutar: ' . $e->getMessage());
        }
    }

    // ── Cancelar ─────────────────────────────────────────────────────────
    public function openCancel(int $id): void
    {
        $this->cancelId       = $id;
        $this->showCancelModal = true;
    }

    public function cancelPayment(): void
    {
        ScheduledPayment::where('id', $this->cancelId)
            ->where('company_id', auth()->user()->company_id)
            ->update(['status' => 'cancelled']);

        $this->showCancelModal = false;
        $this->cancelId        = null;
        session()->flash('success', 'Pago cancelado.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        // Marcar como vencidos los pendientes con fecha pasada
        ScheduledPayment::where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('scheduled_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $payments = ScheduledPayment::with(['financeAccount', 'supplier', 'createdBy', 'executedBy'])
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('folio', 'like', "%{$this->search}%")
                  ->orWhere('concept', 'like', "%{$this->search}%")
                  ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$this->search}%"));
            }))
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterAccount,  fn($q) => $q->where('finance_account_id', $this->filterAccount))
            ->orderByRaw("FIELD(status,'overdue','pending','paid','cancelled')")
            ->orderBy('scheduled_date')
            ->paginate(20);

        // KPIs
        $base = ScheduledPayment::where('company_id', $companyId);
        $kpis = [
            'overdue'       => (clone $base)->where('status', 'overdue')->count(),
            'due_today'     => (clone $base)->where('status', 'pending')
                                    ->whereDate('scheduled_date', today())->count(),
            'due_week'      => (clone $base)->where('status', 'pending')
                                    ->whereBetween('scheduled_date', [today(), today()->addDays(7)])->count(),
            'total_pending' => (float)(clone $base)->whereIn('status', ['pending', 'overdue'])->sum('amount'),
        ];

        return view('livewire.finance.scheduled-payment-index', compact('payments', 'kpis'));
    }
}
