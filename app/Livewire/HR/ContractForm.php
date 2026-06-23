<?php

namespace App\Livewire\HR;

use App\Models\HrContract;
use App\Models\HrContractTemplate;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Contrato')]
class ContractForm extends Component
{
    public ?HrContract $contract = null;

    public ?int $employee_id = null;
    public ?int $hr_contract_template_id = null;
    public string $contract_number = '';
    public string $type = 'indefinido';
    public string $start_date = '';
    public string $end_date = '';
    public string $salary = '';
    public string $salary_period = 'monthly';
    public string $work_shift = 'oficina';
    public int $work_hours_per_week = 48;
    // Schedule
    public string $entry_time = '08:00';
    public string $exit_time = '17:00';
    public array $work_days = [1, 2, 3, 4, 5];
    public float $saturday_hours = 0;
    public int $tolerance_minutes = 10;
    public string $status = 'draft';
    public string $notes = '';
    public string $print_custom_clauses = '';
    public array $print_pages = [];
    // benefits
    public int $aguinaldo_days = 15;
    public int $vacation_days = 6;
    public int $vacation_premium_pct = 25;

    public function mount(?HrContract $contract = null, ?int $employeeId = null): void
    {
        HrContractTemplate::ensureDefaultsForCompany(auth()->user()->company_id, auth()->id());

        $this->start_date = now()->format('Y-m-d');
        $this->contract_number = $this->nextContractNumber();

        if ($employeeId) {
            $this->employee_id = $employeeId;
        }

        if ($contract && $contract->exists) {
            $this->contract         = $contract;
            $benefits               = $contract->benefits ?? [];
            $this->employee_id      = $contract->employee_id;
            $this->hr_contract_template_id = $contract->hr_contract_template_id;
            $this->contract_number  = $contract->contract_number ?? $this->contract_number;
            $this->type             = $contract->type;
            $this->start_date       = $contract->start_date->format('Y-m-d');
            $this->end_date         = $contract->end_date?->format('Y-m-d') ?? '';
            $this->salary           = (string) $contract->salary;
            $this->salary_period    = $contract->salary_period;
            $this->work_shift          = $contract->work_shift ?? 'oficina';
            $this->work_hours_per_week = $contract->work_hours_per_week;
            $this->entry_time          = $contract->entry_time ? substr($contract->entry_time, 0, 5) : '08:00';
            $this->exit_time           = $contract->exit_time  ? substr($contract->exit_time,  0, 5) : '17:00';
            $this->work_days           = $contract->work_days ?? [1, 2, 3, 4, 5];
            $this->saturday_hours      = (float) ($contract->saturday_hours ?? 0);
            $this->tolerance_minutes   = $contract->tolerance_minutes ?? 10;
            $this->status              = $contract->status;
            $this->notes               = $contract->notes ?? '';
            $this->print_custom_clauses = $contract->print_custom_clauses ?? '';
            $this->print_pages = $contract->print_pages_for_editing;
            $this->aguinaldo_days      = $benefits['aguinaldo_days'] ?? 15;
            $this->vacation_days       = $benefits['vacation_days'] ?? 6;
            $this->vacation_premium_pct= $benefits['vacation_premium_pct'] ?? 25;
        } else {
            $this->print_pages = HrContractTemplate::defaultPrintPages();
        }
    }

    public function rules(): array
    {
        return [
            'employee_id'        => 'required|exists:hr_employees,id',
            'hr_contract_template_id' => 'nullable|exists:hr_contract_templates,id',
            'type'               => 'required|in:' . implode(',', array_keys(HrContract::TYPES)),
            'start_date'         => 'required|date',
            'end_date'           => 'nullable|date|after:start_date',
            'salary'             => 'required|numeric|min:0',
            'work_shift'          => 'required|in:oficina,campo',
            'work_hours_per_week'=> 'required|integer|min:1|max:96',
            'entry_time'         => 'nullable|date_format:H:i',
            'exit_time'          => 'nullable|date_format:H:i|after:entry_time',
            'saturday_hours'     => 'numeric|min:0|max:12',
            'tolerance_minutes'  => 'integer|min:0|max:60',
            'notes'              => 'nullable|string',
            'print_custom_clauses'=> 'nullable|string|max:20000',
            'print_pages'         => 'required|array|size:5',
            'print_pages.*'       => 'nullable|string|max:30000',
        ];
    }

    public function updatedWorkShift(string $value): void
    {
        if ($value === 'oficina') {
            $this->work_days = array_values(array_diff(array_map('intval', $this->work_days), [6, 7]));
            $this->saturday_hours = 0;
        }
    }

    public function updatedHrContractTemplateId(): void
    {
        $this->applyTemplate();
    }

    public function applyTemplate(): void
    {
        if (! $this->hr_contract_template_id) {
            return;
        }

        $template = HrContractTemplate::find($this->hr_contract_template_id);

        if (! $template) {
            return;
        }

        $benefits = $template->benefits ?? [];

        $this->type = $template->contract_type;
        $this->work_shift = $template->work_shift;
        $this->work_hours_per_week = $template->work_hours_per_week;
        $this->entry_time = $template->entry_time ? substr($template->entry_time, 0, 5) : $this->entry_time;
        $this->exit_time = $template->exit_time ? substr($template->exit_time, 0, 5) : $this->exit_time;
        $this->work_days = $template->work_days ?? $this->work_days;
        $this->saturday_hours = (float) $template->saturday_hours;
        $this->aguinaldo_days = $benefits['aguinaldo_days'] ?? $this->aguinaldo_days;
        $this->vacation_days = $benefits['vacation_days'] ?? $this->vacation_days;
        $this->vacation_premium_pct = $benefits['vacation_premium_pct'] ?? $this->vacation_premium_pct;

        if ($template->duration_months && $this->start_date) {
            $this->end_date = \Carbon\Carbon::parse($this->start_date)
                ->addMonthsNoOverflow($template->duration_months)
                ->subDay()
                ->format('Y-m-d');
        }

        if ($template->print_custom_clauses) {
            $this->print_custom_clauses = $template->print_custom_clauses;
        }

        $this->print_pages = $template->print_pages_for_editing;

        $this->updatedWorkShift($this->work_shift);
    }

    public function save(): void
    {
        $this->persistContract(false);
    }

    public function saveAndPrint(): void
    {
        $this->persistContract(true);
    }

    private function persistContract(bool $printAfterSave = false): void
    {
        $this->validate();

        $workDays = array_values(array_map('intval', $this->work_days));

        if ($this->work_shift === 'oficina') {
            $workDays = array_values(array_diff($workDays, [6, 7]));
            $this->saturday_hours = 0;
        }

        $data = [
            'company_id'          => auth()->user()->company_id,
            'employee_id'         => $this->employee_id,
            'hr_contract_template_id' => $this->hr_contract_template_id,
            'contract_number'     => $this->contract_number ?: $this->nextContractNumber(),
            'type'                => $this->type,
            'start_date'          => $this->start_date,
            'end_date'            => $this->end_date ?: null,
            'salary'              => $this->salary,
            'salary_period'       => $this->salary_period,
            'work_shift'          => $this->work_shift ?: null,
            'work_hours_per_week' => $this->work_hours_per_week,
            'entry_time'          => $this->entry_time ?: null,
            'exit_time'           => $this->exit_time ?: null,
            'work_days'           => $workDays,
            'saturday_hours'      => $this->saturday_hours,
            'tolerance_minutes'   => $this->tolerance_minutes,
            'benefits'            => [
                'aguinaldo_days'       => $this->aguinaldo_days,
                'vacation_days'        => $this->vacation_days,
                'vacation_premium_pct' => $this->vacation_premium_pct,
            ],
            'status'   => $this->status,
            'notes'    => $this->notes ?: null,
            'print_custom_clauses' => $this->print_custom_clauses ?: null,
            'print_pages' => array_values($this->print_pages),
            'created_by' => auth()->id(),
        ];

        if ($this->contract && $this->contract->exists) {
            $this->contract->update($data);
            $contract = $this->contract->refresh();
            session()->flash('success', 'Contrato actualizado correctamente.');
        } else {
            $contract = HrContract::create($data);
            session()->flash('success', 'Contrato registrado correctamente.');
        }

        if ($printAfterSave) {
            $this->redirect(route('hr.contracts.template.print', $contract).'?print=1', navigate: false);
            return;
        }

        $this->redirect(route('hr.contracts.index'), navigate: true);
    }

    private function nextContractNumber(): string
    {
        $companyId = auth()->user()?->company_id;
        $year = now()->format('Y');
        $prefix = "CT-{$year}-";

        $lastNumber = HrContract::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->where('contract_number', 'like', $prefix.'%')
            ->orderByDesc('contract_number')
            ->value('contract_number');

        $sequence = $lastNumber ? ((int) str_replace($prefix, '', $lastNumber)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        $employees = HrEmployee::where('status', 'active')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        $templates = HrContractTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'duration_months']);

        return view('livewire.hr.contract-form', compact('employees', 'templates'));
    }
}
