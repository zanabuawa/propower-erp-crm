<?php

namespace App\Livewire\Finance;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\HrEmployee;
use App\Models\Project;
use App\Models\TravelExpense;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class TravelExpenseIndex extends Component
{
    use WithPagination;

    public string $search         = '';
    public string $filterStatus   = '';
    public string $filterEmployee = '';
    public string $filterDateFrom = '';
    public string $filterDateTo   = '';

    // Approve modal
    public bool   $showApproveModal   = false;
    public ?int   $approvingId        = null;
    public string $approveAccountId   = '';
    public string $approveNotes       = '';

    // Reject modal
    public bool   $showRejectModal    = false;
    public ?int   $rejectingId        = null;
    public string $rejectReason       = '';

    // Pay modal
    public bool   $showPayModal       = false;
    public ?int   $payingId           = null;
    public string $payNotes           = '';

    // Comprobar modal
    public bool   $showComprobarModal = false;
    public ?int   $comprobarId        = null;
    public string $amountSpent        = '';
    public string $comprobarNotes     = '';

    public function updatingSearch(): void    { $this->resetPage(); }
    public function updatingFilterStatus(): void   { $this->resetPage(); }
    public function updatingFilterEmployee(): void { $this->resetPage(); }

    // ── Approve ──────────────────────────────────────────────────────────────
    public function openApprove(int $id): void
    {
        $this->approvingId      = $id;
        $this->approveAccountId = '';
        $this->approveNotes     = '';
        $this->showApproveModal = true;
    }

    public function approve(): void
    {
        $this->validate([
            'approveAccountId' => 'required|exists:finance_accounts,id',
        ], ['approveAccountId.required' => 'Selecciona la cuenta de pago.']);

        $travel = TravelExpense::findOrFail($this->approvingId);
        $travel->update([
            'status'             => 'aprobado',
            'finance_account_id' => $this->approveAccountId,
            'notes'              => $this->approveNotes ?: $travel->notes,
        ]);

        $this->showApproveModal = false;
        session()->flash('success', "Viático {$travel->folio} aprobado.");
    }

    // ── Reject ───────────────────────────────────────────────────────────────
    public function openReject(int $id): void
    {
        $this->rejectingId     = $id;
        $this->rejectReason    = '';
        $this->showRejectModal = true;
    }

    public function reject(): void
    {
        $this->validate(['rejectReason' => 'required|string|max:255'],
            ['rejectReason.required' => 'Indica el motivo del rechazo.']);

        TravelExpense::findOrFail($this->rejectingId)->update([
            'status'           => 'rechazado',
            'rejection_reason' => $this->rejectReason,
        ]);

        $this->showRejectModal = false;
        session()->flash('success', 'Viático rechazado.');
    }

    // ── Pay ──────────────────────────────────────────────────────────────────
    public function openPay(int $id): void
    {
        $this->payingId      = $id;
        $this->payNotes      = '';
        $this->showPayModal  = true;
    }

    public function pay(): void
    {
        $travel = TravelExpense::with(['employee', 'financeAccount'])->findOrFail($this->payingId);

        DB::transaction(function () use ($travel) {
            $companyId = auth()->user()->company_id;

            $folio = 'VIA-' . str_pad(
                FinanceTransaction::where('category', 'viatico')
                    ->whereHas('account', fn($q) => $q->where('company_id', $companyId))
                    ->count() + 1,
                5, '0', STR_PAD_LEFT
            );

            // Crear transacción financiera
            $tx = FinanceTransaction::create([
                'account_id'       => $travel->finance_account_id,
                'project_id'       => $travel->project_id,
                'registered_by'    => auth()->id(),
                'folio'            => $folio,
                'type'             => 'egreso',
                'concept'          => "Viático {$travel->folio} — {$travel->employee->full_name} — {$travel->destination}",
                'category'         => 'viatico',
                'amount'           => $travel->amount_approved,
                'currency'         => $travel->currency,
                'exchange_rate'    => 1,
                'transaction_date' => now()->toDateString(),
                'reference'        => $travel->folio,
                'status'           => 'confirmado',
                'notes'            => $this->payNotes ?: null,
            ]);

            // Actualizar saldo de la cuenta
            $travel->financeAccount?->decrement('current_balance', $travel->amount_approved);

            // Si tiene proyecto, sumar al costo real
            if ($travel->project_id) {
                $totalViáticos = TravelExpense::where('project_id', $travel->project_id)
                    ->where('status', 'pagado')
                    ->sum('amount_approved');
                $travel->project?->increment('cost_actual', $travel->amount_approved);
            }

            $travel->update([
                'status'                 => 'pagado',
                'finance_transaction_id' => $tx->id,
            ]);
        });

        $this->showPayModal = false;
        session()->flash('success', 'Pago registrado y transacción financiera creada.');
    }

    // ── Comprobar ─────────────────────────────────────────────────────────────
    public function openComprobar(int $id): void
    {
        $travel = TravelExpense::findOrFail($id);
        $this->comprobarId    = $id;
        $this->amountSpent    = (string) $travel->amount_approved;
        $this->comprobarNotes = '';
        $this->showComprobarModal = true;
    }

    public function comprobar(): void
    {
        $this->validate([
            'amountSpent' => 'required|numeric|min:0',
        ]);

        TravelExpense::findOrFail($this->comprobarId)->update([
            'status'       => 'comprobado',
            'amount_spent' => $this->amountSpent,
            'notes'        => $this->comprobarNotes ?: null,
        ]);

        $this->showComprobarModal = false;
        session()->flash('success', 'Comprobación registrada. Viático cerrado.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $travels = TravelExpense::with(['employee', 'assignedBy', 'project', 'financeAccount'])
            ->where('company_id', $companyId)
            ->when($this->search, fn($q) => $q
                ->where('folio', 'like', "%{$this->search}%")
                ->orWhere('destination', 'like', "%{$this->search}%")
                ->orWhere('purpose', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->when($this->filterDateFrom, fn($q) => $q->where('departure_date', '>=', $this->filterDateFrom))
            ->when($this->filterDateTo, fn($q) => $q->where('departure_date', '<=', $this->filterDateTo))
            ->latest()
            ->paginate(20);

        $counts = TravelExpense::where('company_id', $companyId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $employees = HrEmployee::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name', 'employee_number']);

        $accounts = $this->showApproveModal
            ? FinanceAccount::where('company_id', $companyId)->where('is_active', true)->get()
            : collect();

        return view('livewire.finance.travel-expense-index', compact('travels', 'counts', 'employees', 'accounts'));
    }
}
