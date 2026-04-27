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
    public string $filterType   = '';

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
