<?php

namespace App\Livewire\HR;

use App\Models\HrPayrollConcept;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Conceptos de Nómina')]
class PayrollConceptIndex extends Component
{
    public bool   $showModal    = false;
    public ?int   $editingId    = null;
    public string $name         = '';
    public string $code         = '';
    public string $type         = 'perception';
    public bool   $is_taxable   = true;
    public bool   $is_active    = true;

    public string $filterType   = '';

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'code', 'type', 'is_taxable', 'is_active']);
        $this->type       = 'perception';
        $this->is_taxable = true;
        $this->is_active  = true;
        $this->showModal  = true;
    }

    public function openEdit(HrPayrollConcept $concept): void
    {
        $this->editingId  = $concept->id;
        $this->name       = $concept->name;
        $this->code       = $concept->code ?? '';
        $this->type       = $concept->type;
        $this->is_taxable = $concept->is_taxable;
        $this->is_active  = $concept->is_active;
        $this->showModal  = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:20',
            'type' => 'required|in:perception,deduction',
        ], [
            'name.required' => 'El nombre del concepto es obligatorio.',
            'type.required' => 'Selecciona el tipo.',
        ]);

        $data = [
            'name'       => $this->name,
            'code'       => $this->code ?: null,
            'type'       => $this->type,
            'is_taxable' => $this->is_taxable,
            'is_active'  => $this->is_active,
        ];

        if ($this->editingId) {
            HrPayrollConcept::where('id', $this->editingId)->update($data);
            session()->flash('success', 'Concepto actualizado.');
        } else {
            HrPayrollConcept::create(array_merge($data, [
                'company_id' => auth()->user()->company_id,
            ]));
            session()->flash('success', 'Concepto creado.');
        }

        $this->showModal = false;
    }

    public function toggleActive(HrPayrollConcept $concept): void
    {
        $concept->update(['is_active' => ! $concept->is_active]);
    }

    public function delete(HrPayrollConcept $concept): void
    {
        $concept->delete();
        session()->flash('success', 'Concepto eliminado.');
    }

    public function render()
    {
        $concepts = HrPayrollConcept::where('company_id', auth()->user()->company_id)
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('livewire.hr.payroll-concept-index', compact('concepts'));
    }
}
