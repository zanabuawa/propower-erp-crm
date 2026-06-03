<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrLeave;
use App\Models\HrVacationBalance;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Solicitud de Vacaciones')]
class VacationForm extends Component
{
    public ?HrLeave $leave = null;

    public ?int   $employee_id = null;
    public string $start_date  = '';
    public string $end_date    = '';
    public string $reason      = '';
    public string $notes       = '';

    public int    $businessDays = 0;
    public ?array $balance      = null;

    public function mount(?HrLeave $leave = null): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->addDays(6)->format('Y-m-d');
        $this->recalculate();

        if ($leave && $leave->exists && $leave->type === 'vacaciones') {
            $this->leave       = $leave;
            $this->employee_id = $leave->employee_id;
            $this->start_date  = $leave->start_date->format('Y-m-d');
            $this->end_date    = $leave->end_date->format('Y-m-d');
            $this->reason      = $leave->reason ?? '';
            $this->notes       = $leave->notes ?? '';
            $this->recalculate();
            $this->loadBalance();
        }
    }

    public function updatedEmployeeId(): void { $this->loadBalance(); }
    public function updatedStartDate(): void  { $this->recalculate(); }
    public function updatedEndDate(): void    { $this->recalculate(); }

    private function recalculate(): void
    {
        if (!$this->start_date || !$this->end_date) { return; }

        $start   = Carbon::parse($this->start_date);
        $end     = Carbon::parse($this->end_date);
        $days    = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if (!$current->isWeekend()) $days++;
            $current->addDay();
        }
        $this->businessDays = $days;
    }

    private function loadBalance(): void
    {
        if (!$this->employee_id) {
            $this->balance = null;
            return;
        }
        $emp = HrEmployee::find($this->employee_id);
        if (!$emp) { $this->balance = null; return; }

        $b = HrVacationBalance::forEmployee($emp, now()->year);
        $this->balance = [
            'earned'    => (float) $b->days_earned,
            'used'      => (float) $b->days_used,
            'pending'   => (float) $b->days_pending_approval,
            'available' => (float) $b->days_available,
        ];
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:hr_employees,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string',
            'notes'       => 'nullable|string|max:1000',
        ];
    }

    public function save(): void
    {
        $this->validate();
        $this->recalculate();

        $data = [
            'company_id'    => auth()->user()->company_id,
            'employee_id'   => $this->employee_id,
            'type'          => 'vacaciones',
            'start_date'    => $this->start_date,
            'end_date'      => $this->end_date,
            'business_days' => $this->businessDays,
            'reason'        => $this->reason ?: null,
            'notes'         => $this->notes ?: null,
            'created_by'    => auth()->id(),
        ];

        if ($this->leave && $this->leave->exists) {
            $this->leave->update($data);
            session()->flash('success', 'Solicitud actualizada correctamente.');
        } else {
            $data['status'] = 'pending';
            $emp     = HrEmployee::findOrFail($this->employee_id);
            $balance = HrVacationBalance::forEmployee($emp, now()->year);
            $balance->days_pending_approval += $this->businessDays;
            $balance->recalculate();
            HrLeave::create($data);
            session()->flash('success', 'Solicitud de vacaciones registrada.');
        }

        $this->redirect(route('hr.vacations.index'), navigate: true);
    }

    public function render()
    {
        $employees = HrEmployee::where('status', 'active')
            ->orWhere('status', 'on_leave')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'second_last_name']);

        return view('livewire.hr.vacation-form', compact('employees'));
    }
}
