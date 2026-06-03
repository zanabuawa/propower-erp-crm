<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrLeave;
use App\Models\HrVacationBalance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
#[Title('Vacaciones')]
class VacationIndex extends Component
{
    use WithPagination;

    public string $filterStatus   = '';
    public string $filterEmployee = '';
    public string $search         = '';

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

        $balance = HrVacationBalance::forEmployee($leave->employee, now()->year);
        $balance->days_used             += $leave->business_days;
        $balance->days_pending_approval  = max(0, $balance->days_pending_approval - $leave->business_days);
        $balance->recalculate();

        $leave->employee->update(['status' => 'on_leave']);

        session()->flash('success', 'Vacaciones aprobadas correctamente.');
    }

    public function reject(int $id): void
    {
        $this->authorize('edit hr');

        $leave = HrLeave::with('employee')->findOrFail($id);
        $leave->update(['status' => 'rejected']);

        $balance = HrVacationBalance::forEmployee($leave->employee, now()->year);
        $balance->days_pending_approval = max(0, $balance->days_pending_approval - $leave->business_days);
        $balance->recalculate();

        session()->flash('success', 'Solicitud rechazada.');
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $leaves = HrLeave::with(['employee', 'approvedBy'])
            ->where('type', 'vacaciones')
            ->when($this->filterStatus,   fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterEmployee, fn($q) => $q->where('employee_id', $this->filterEmployee))
            ->when($this->search, fn($q) => $q->whereHas('employee', fn($q2) =>
                $q2->where('first_name', 'like', "%{$this->search}%")
                   ->orWhere('last_name',  'like', "%{$this->search}%")
            ))
            ->latest('start_date')
            ->paginate(15);

        $employees = HrEmployee::where('status', 'active')
            ->orWhere('status', 'on_leave')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name']);

        $summary = [
            'pending'      => HrLeave::where('type', 'vacaciones')->where('status', 'pending')->count(),
            'approved'     => HrLeave::where('type', 'vacaciones')->where('status', 'approved')->whereYear('start_date', now()->year)->count(),
            'days_pending' => (int) HrLeave::where('type', 'vacaciones')->where('status', 'pending')->sum('business_days'),
        ];

        return view('livewire.hr.vacation-index', compact('leaves', 'employees', 'summary'));
    }
}
