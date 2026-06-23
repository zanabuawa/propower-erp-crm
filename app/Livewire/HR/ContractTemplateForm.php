<?php

namespace App\Livewire\HR;

use App\Models\HrContract;
use App\Models\HrContractTemplate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Plantilla de Contrato')]
class ContractTemplateForm extends Component
{
    public ?HrContractTemplate $template = null;

    public string $name = '';
    public string $code = '';
    public string $description = '';
    public string $contract_type = 'temporal';
    public string $duration_months = '3';
    public string $work_shift = 'campo';
    public int $work_hours_per_week = 48;
    public string $entry_time = '07:00';
    public string $exit_time = '18:00';
    public array $work_days = [1, 2, 3, 4, 5, 6];
    public float $saturday_hours = 5;
    public int $aguinaldo_days = 15;
    public int $vacation_days = 6;
    public int $vacation_premium_pct = 25;
    public string $print_custom_clauses = '';
    public array $print_pages = [];
    public bool $is_active = true;

    public function mount(?HrContractTemplate $template = null): void
    {
        if ($template && $template->exists) {
            $this->template = $template;
            $benefits = $template->benefits ?? [];

            $this->name = $template->name;
            $this->code = $template->code ?? '';
            $this->description = $template->description ?? '';
            $this->contract_type = $template->contract_type;
            $this->duration_months = (string) ($template->duration_months ?? '');
            $this->work_shift = $template->work_shift;
            $this->work_hours_per_week = $template->work_hours_per_week;
            $this->entry_time = $template->entry_time ? substr($template->entry_time, 0, 5) : '';
            $this->exit_time = $template->exit_time ? substr($template->exit_time, 0, 5) : '';
            $this->work_days = $template->work_days ?? [1, 2, 3, 4, 5];
            $this->saturday_hours = (float) $template->saturday_hours;
            $this->aguinaldo_days = $benefits['aguinaldo_days'] ?? 15;
            $this->vacation_days = $benefits['vacation_days'] ?? 6;
            $this->vacation_premium_pct = $benefits['vacation_premium_pct'] ?? 25;
            $this->print_custom_clauses = $template->print_custom_clauses ?? '';
            $this->print_pages = $template->print_pages_for_editing;
            $this->is_active = $template->is_active;
        } else {
            $this->print_pages = HrContractTemplate::defaultPrintPages();
        }
    }

    public function updatedWorkShift(string $value): void
    {
        if ($value === 'oficina') {
            $this->work_days = array_values(array_diff(array_map('intval', $this->work_days), [6, 7]));
            $this->saturday_hours = 0;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:1000',
            'contract_type' => 'required|in:'.implode(',', array_keys(HrContract::TYPES)),
            'duration_months' => 'nullable|integer|min:1|max:120',
            'work_shift' => 'required|in:oficina,campo',
            'work_hours_per_week' => 'required|integer|min:1|max:96',
            'entry_time' => 'nullable|date_format:H:i',
            'exit_time' => 'nullable|date_format:H:i|after:entry_time',
            'saturday_hours' => 'numeric|min:0|max:12',
            'work_days' => 'required|array|min:1',
            'aguinaldo_days' => 'required|integer|min:0|max:60',
            'vacation_days' => 'required|integer|min:0|max:60',
            'vacation_premium_pct' => 'required|integer|min:0|max:100',
            'print_custom_clauses' => 'nullable|string|max:20000',
            'print_pages' => 'required|array|size:5',
            'print_pages.*' => 'nullable|string|max:30000',
            'is_active' => 'boolean',
        ]);

        $workDays = array_values(array_map('intval', $this->work_days));

        if ($this->work_shift === 'oficina') {
            $workDays = array_values(array_diff($workDays, [6, 7]));
            $data['saturday_hours'] = 0;
        }

        $data['company_id'] = auth()->user()->company_id;
        $data['duration_months'] = $this->duration_months !== '' ? (int) $this->duration_months : null;
        $data['work_days'] = $workDays;
        $data['benefits'] = [
            'aguinaldo_days' => $this->aguinaldo_days,
            'vacation_days' => $this->vacation_days,
            'vacation_premium_pct' => $this->vacation_premium_pct,
        ];
        $data['print_custom_clauses'] = $this->print_custom_clauses ?: null;
        $data['print_pages'] = array_values($this->print_pages);
        $data['created_by'] = auth()->id();

        if ($this->template && $this->template->exists) {
            $this->template->update($data);
            session()->flash('success', 'Plantilla actualizada correctamente.');
        } else {
            HrContractTemplate::create($data);
            session()->flash('success', 'Plantilla creada correctamente.');
        }

        $this->redirect(route('hr.contract-templates.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.contract-template-form');
    }
}
