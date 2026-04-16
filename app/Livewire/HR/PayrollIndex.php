<?php

namespace App\Livewire\HR;

use App\Models\HrPayroll;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Nóminas')]
class PayrollIndex extends Component
{
    use WithPagination;

    public string $filterStatus = '';

    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function render()
    {
        $payrolls = HrPayroll::with(['createdBy', 'approvedBy'])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->latest('period_start')
            ->paginate(15);

        return view('livewire.hr.payroll-index', compact('payrolls'));
    }
}
