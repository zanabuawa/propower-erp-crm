<?php

namespace App\Livewire\Tenders;

use App\Models\FinanceAccount;
use App\Models\FinanceCashflow;
use App\Models\FinanceTransaction;
use App\Models\Project;
use App\Models\Tender;
use App\Models\User;
use App\Models\WorkLibranza;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Libranzas / Estimaciones')]
class WorkLibranzaIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public ?int $project_id = null;
    public ?int $tender_id = null;
    public string $concept = '';
    public string $period_start = '';
    public string $period_end = '';
    public string $amount = '';
    public string $advance_pct = '';
    public string $status = 'borrador';
    public string $notes = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        $this->period_start = now()->startOfMonth()->format('Y-m-d');
        $this->period_end   = now()->endOfMonth()->format('Y-m-d');
        if ($id) {
            $l = WorkLibranza::findOrFail($id);
            $this->editingId    = $id;
            $this->project_id   = $l->project_id;
            $this->tender_id    = $l->tender_id;
            $this->concept      = $l->concept;
            $this->period_start = $l->period_start->format('Y-m-d');
            $this->period_end   = $l->period_end->format('Y-m-d');
            $this->amount       = (string) $l->amount;
            $this->advance_pct  = (string) $l->advance_pct;
            $this->status       = $l->status;
            $this->notes        = $l->notes ?? '';
        }
        $this->showModal = true;
    }

    public function approve(int $id): void
    {
        $l = WorkLibranza::with('project')->findOrFail($id);
        $l->update(['status' => 'aprobada', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        // Auto-generate projected cashflow entry in Finance module
        $account = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('id')->first();

        if ($account) {
            FinanceCashflow::create([
                'account_id'    => $account->id,
                'project_id'    => $l->project_id,
                'tender_id'     => $l->tender_id,
                'libranza_id'   => $l->id,
                'concept'       => "Libranza #{$l->number}: {$l->concept}",
                'type'          => 'proyectado',
                'flow'          => 'entrada',
                'category'      => 'operacion',
                'amount'        => $l->amount,
                'currency'      => 'MXN',
                'expected_date' => $l->period_end,
                'notes'         => "Generado automáticamente al aprobar libranza #{$l->number}",
            ]);
        }

        session()->flash('success', 'Libranza aprobada y flujo de caja proyectado.');
    }

    public function markPaid(int $id): void
    {
        $l = WorkLibranza::with('project')->findOrFail($id);
        $l->update(['status' => 'pagada']);

        // Realize the cashflow entry linked to this libranza
        FinanceCashflow::where('libranza_id', $l->id)
            ->where('is_realized', false)
            ->update(['is_realized' => true, 'realized_date' => now()->toDateString(), 'type' => 'real']);

        // Create confirmed finance transaction
        $account = FinanceAccount::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)->orderBy('id')->first();

        if ($account) {
            FinanceTransaction::create([
                'account_id'       => $account->id,
                'project_id'       => $l->project_id,
                'tender_id'        => $l->tender_id,
                'libranza_id'      => $l->id,
                'registered_by'    => auth()->id(),
                'folio'            => 'LIB-' . str_pad($l->id, 6, '0', STR_PAD_LEFT),
                'type'             => 'ingreso',
                'concept'          => "Cobro libranza #{$l->number}: {$l->concept}",
                'category'         => 'proyecto',
                'amount'           => $l->amount,
                'currency'         => 'MXN',
                'exchange_rate'    => 1,
                'transaction_date' => now()->toDateString(),
                'status'           => 'confirmado',
                'notes'            => "Pago del período {$l->period_start->format('d/m/Y')} – {$l->period_end->format('d/m/Y')}",
            ]);
        }

        session()->flash('success', 'Libranza marcada como pagada y transacción registrada en finanzas.');
    }

    public function save(): void
    {
        $this->validate([
            'project_id'   => 'required|exists:projects,id',
            'concept'      => 'required|string|max:300',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'amount'       => 'required|numeric|min:0',
            'advance_pct'  => 'nullable|numeric|min:0|max:100',
        ]);

        $projectId = $this->project_id;
        $number = WorkLibranza::where('project_id', $projectId)->max('number') + 1;

        $data = [
            'project_id'   => $projectId,
            'tender_id'    => $this->tender_id ?: null,
            'number'       => $this->editingId ? WorkLibranza::findOrFail($this->editingId)->number : $number,
            'concept'      => $this->concept,
            'period_start' => $this->period_start,
            'period_end'   => $this->period_end,
            'amount'       => $this->amount,
            'advance_pct'  => $this->advance_pct ?: 0,
            'status'       => $this->status,
            'notes'        => $this->notes ?: null,
        ];

        if ($this->editingId) {
            WorkLibranza::findOrFail($this->editingId)->update($data);
        } else {
            WorkLibranza::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Libranza guardada.');
    }

    public function delete(int $id): void
    {
        WorkLibranza::findOrFail($id)->delete();
        session()->flash('success', 'Libranza eliminada.');
    }

    private function resetForm(): void
    {
        $this->editingId = null; $this->project_id = null; $this->tender_id = null;
        $this->concept = ''; $this->period_start = ''; $this->period_end = '';
        $this->amount = ''; $this->advance_pct = ''; $this->status = 'borrador'; $this->notes = '';
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;
        $libranzas = WorkLibranza::whereHas('project', fn($q) => $q->where('company_id', $companyId))
            ->when($this->search, fn($q) => $q->where('concept', 'like', '%' . $this->search . '%'))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->with(['project', 'tender', 'approvedBy'])
            ->latest()
            ->paginate(15);

        return view('livewire.tenders.work-libranza-index', [
            'libranzas' => $libranzas,
            'projects'  => Project::where('company_id', $companyId)->orderBy('name')->get(),
            'tenders'   => Tender::where('company_id', $companyId)->orderBy('name')->get(),
            'statuses'  => WorkLibranza::STATUSES,
        ]);
    }
}
