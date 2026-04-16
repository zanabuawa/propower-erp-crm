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

    // Modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public ?int $employee_id = null;
    public string $type = 'vacaciones';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';
    public string $notes = '';
    public string $imss_certificate_number = '';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset(['editingId','employee_id','type','start_date','end_date','reason','notes','imss_certificate_number']);
        $this->type       = 'vacaciones';
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->addDays(6)->format('Y-m-d');
        $this->showModal  = true;
    }

    public function openEdit(int $id): void
    {
        $leave = HrLeave::findOrFail($id);
        $this->editingId               = $id;
        $this->employee_id             = $leave->employee_id;
        $this->type                    = $leave->type;
        $this->start_date              = $leave->start_date->format('Y-m-d');
        $this->end_date                = $leave->end_date->format('Y-m-d');
        $this->reason                  = $leave->reason ?? '';
        $this->notes                   = $leave->notes ?? '';
        $this->imss_certificate_number = $leave->imss_certificate_number ?? '';
        $this->showModal               = true;
    }

    public function save(): void
    {
        $this->validate([
            'employee_id' => 'required|exists:hr_employees,id',
            'type'        => 'required|in:' . implode(',', array_keys(HrLeave::TYPES)),
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        $start = \Carbon\Carbon::parse($this->start_date);
        $end   = \Carbon\Carbon::parse($this->end_date);
        $businessDays = $this->countBusinessDays($start, $end);

        $data = [
            'company_id'             => auth()->user()->company_id,
            'employee_id'            => $this->employee_id,
            'type'                   => $this->type,
            'start_date'             => $this->start_date,
            'end_date'               => $this->end_date,
            'business_days'          => $businessDays,
            'reason'                 => $this->reason ?: null,
            'notes'                  => $this->notes ?: null,
            'imss_certificate_number'=> $this->imss_certificate_number ?: null,
            'status'                 => 'pending',
            'created_by'             => auth()->id(),
        ];

        if ($this->editingId) {
            HrLeave::findOrFail($this->editingId)->update($data);
        } else {
            // Update vacation balance pending days
            if ($this->type === 'vacaciones') {
                $emp = HrEmployee::find($this->employee_id);
                $balance = HrVacationBalance::forEmployee($emp, now()->year);
                $balance->days_pending_approval += $businessDays;
                $balance->recalculate();
            }
            HrLeave::create($data);
        }

        session()->flash('success', 'Permiso/baja temporal registrado.');
        $this->showModal = false;
    }

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
