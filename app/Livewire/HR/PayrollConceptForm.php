<?php

namespace App\Livewire\HR;

use App\Models\HrPayrollConcept;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Concepto de Nómina')]
class PayrollConceptForm extends Component
{
    public ?HrPayrollConcept $payrollConcept = null;

    public string $name         = '';
    public string $code         = '';
    public string $type         = 'perception';
    public bool   $is_taxable   = true;
    public bool   $is_active    = true;

    public function mount(?HrPayrollConcept $payrollConcept = null): void
    {
        if ($payrollConcept && $payrollConcept->exists) {
            $this->payrollConcept = $payrollConcept;
            $this->name           = $payrollConcept->name;
            $this->code           = $payrollConcept->code ?? '';
            $this->type           = $payrollConcept->type;
            $this->is_taxable     = $payrollConcept->is_taxable;
            $this->is_active      = $payrollConcept->is_active;
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'type' => 'required|in:perception,deduction',
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'       => $this->name,
            'code'       => $this->code ?: null,
            'type'       => $this->type,
            'is_taxable' => $this->is_taxable,
            'is_active'  => $this->is_active,
        ];

        if ($this->payrollConcept && $this->payrollConcept->exists) {
            $this->payrollConcept->update($data);
            session()->flash('success', 'Concepto actualizado correctamente.');
        } else {
            HrPayrollConcept::create(array_merge($data, [
                'company_id' => auth()->user()->company_id,
            ]));
            session()->flash('success', 'Concepto creado correctamente.');
        }

        $this->redirect(route('hr.payroll.concepts'), navigate: true);
    }

    public function render()
    {
        return view('livewire.hr.payroll-concept-form');
    }
}
