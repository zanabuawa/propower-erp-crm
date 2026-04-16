<?php

namespace App\Livewire\HR;

use App\Models\HrContract;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Contratos')]
class ContractIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    // Modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $employee_id = null;
    public string $contract_number = '';
    public string $type = 'indefinido';
    public string $start_date = '';
    public string $end_date = '';
    public string $salary = '';
    public string $salary_period = 'monthly';
    public string $work_shift = '';
    public int $work_hours_per_week = 48;
    public string $status = 'draft';
    public string $notes = '';
    // benefits
    public int $aguinaldo_days = 15;
    public int $vacation_days = 6;
    public int $vacation_premium_pct = 25;

    public function updatingSearch(): void { $this->resetPage(); }

    public function openCreate(?int $employeeId = null): void
    {
        $this->reset(['editingId','contract_number','type','end_date','salary','salary_period','work_shift','notes']);
        $this->employee_id         = $employeeId;
        $this->type                = 'indefinido';
        $this->salary_period       = 'monthly';
        $this->work_hours_per_week = 48;
        $this->status              = 'draft';
        $this->start_date          = now()->format('Y-m-d');
        $this->aguinaldo_days      = 15;
        $this->vacation_days       = 6;
        $this->vacation_premium_pct= 25;
        $this->showModal           = true;
    }

    public function openEdit(int $id): void
    {
        $c = HrContract::findOrFail($id);
        $benefits               = $c->benefits ?? [];
        $this->editingId        = $id;
        $this->employee_id      = $c->employee_id;
        $this->contract_number  = $c->contract_number ?? '';
        $this->type             = $c->type;
        $this->start_date       = $c->start_date->format('Y-m-d');
        $this->end_date         = $c->end_date?->format('Y-m-d') ?? '';
        $this->salary           = (string) $c->salary;
        $this->salary_period    = $c->salary_period;
        $this->work_shift       = $c->work_shift ?? '';
        $this->work_hours_per_week = $c->work_hours_per_week;
        $this->status           = $c->status;
        $this->notes            = $c->notes ?? '';
        $this->aguinaldo_days   = $benefits['aguinaldo_days'] ?? 15;
        $this->vacation_days    = $benefits['vacation_days'] ?? 6;
        $this->vacation_premium_pct = $benefits['vacation_premium_pct'] ?? 25;
        $this->showModal        = true;
    }

    public function save(): void
    {
        $this->validate([
            'employee_id'        => 'required|exists:hr_employees,id',
            'type'               => 'required|in:' . implode(',', array_keys(HrContract::TYPES)),
            'start_date'         => 'required|date',
            'end_date'           => 'nullable|date|after:start_date',
            'salary'             => 'required|numeric|min:0',
            'work_hours_per_week'=> 'required|integer|min:1|max:96',
        ]);

        $data = [
            'company_id'          => auth()->user()->company_id,
            'employee_id'         => $this->employee_id,
            'contract_number'     => $this->contract_number ?: null,
            'type'                => $this->type,
            'start_date'          => $this->start_date,
            'end_date'            => $this->end_date ?: null,
            'salary'              => $this->salary,
            'salary_period'       => $this->salary_period,
            'work_shift'          => $this->work_shift ?: null,
            'work_hours_per_week' => $this->work_hours_per_week,
            'benefits'            => [
                'aguinaldo_days'       => $this->aguinaldo_days,
                'vacation_days'        => $this->vacation_days,
                'vacation_premium_pct' => $this->vacation_premium_pct,
            ],
            'status'   => $this->status,
            'notes'    => $this->notes ?: null,
            'created_by' => auth()->id(),
        ];

        if ($this->editingId) {
            HrContract::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Contrato actualizado.');
        } else {
            HrContract::create($data);
            session()->flash('success', 'Contrato creado.');
        }

        $this->showModal = false;
    }

    public function render()
    {
        $contracts = HrContract::with(['employee', 'createdBy'])
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($q2) =>
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest()
            ->paginate(15);

        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.contract-index', compact('contracts', 'employees'));
    }
}
