<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrLeave;
use App\Models\HrVacationBalance;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Bajas Temporales y Permisos')]
class LeaveIndex extends Component
{
    use WithPagination;

    public string $filterStatus = '';
    public string $filterType = '';
    public string $filterEmployee = '';
    public string $search = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function approve(int $id): void
    {
        $this->authorize('edit hr');

        $leave = HrLeave::with('employee')->findOrFail($id);
        $leave->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Update vacation balance
        if ($leave->type === 'vacaciones') {
            $balance = HrVacationBalance::forEmployee($leave->employee, now()->year);
            $balance->days_used              += $leave->business_days;
            $balance->days_pending_approval  = max(0, $balance->days_pending_approval - $leave->business_days);
            $balance->recalculate();
        }

        // Update employee status if needed
        if (in_array($leave->type, ['vacaciones', 'permiso_con_goce', 'permiso_sin_goce', 'maternidad', 'paternidad'])) {
            $leave->employee->update(['status' => 'on_leave']);
        }

        session()->flash('success', 'Permiso aprobado.');
    }

    public function reject(int $id): void
    {
        $this->authorize('edit hr');

        $leave = HrLeave::with('employee')->findOrFail($id);
        $leave->update(['status' => 'rejected']);

        if ($leave->type === 'vacaciones') {
            $balance = HrVacationBalance::forEmployee($leave->employee, now()->year);
            $balance->days_pending_approval = max(0, $balance->days_pending_approval - $leave->business_days);
            $balance->recalculate();
        }

        session()->flash('success', 'Permiso rechazado.');
    }

    private function countBusinessDays(\Carbon\Carbon $start, \Carbon\Carbon $end): int
    {
        $days = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend()) $days++;
            $current->addDay();
        }
        return $days;
    }

    public function render()
    {
        $leaves = HrLeave::with(['employee', 'approvedBy', 'createdBy'])
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($q2) =>
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name', 'like', "%{$this->search}%")
            ))
            ->latest('start_date')
            ->paginate(15);

        $employees = HrEmployee::where('status', 'active')
            ->orWhere('status', 'on_leave')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.leave-index', compact('leaves', 'employees'));
    }
}
