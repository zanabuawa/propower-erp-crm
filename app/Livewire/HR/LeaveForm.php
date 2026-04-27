<?php

namespace App\Livewire\HR;

use App\Models\HrEmployee;
use App\Models\HrLeave;
use App\Models\HrVacationBalance;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Permiso/Baja')]
class LeaveForm extends Component
{
    public ?HrLeave $leave = null;

    public ?int $employee_id = null;
    public string $type = 'vacaciones';
    public string $start_date = '';
    public string $end_date = '';
    public string $reason = '';
    public string $notes = '';
    public string $imss_certificate_number = '';

    public function mount(?HrLeave $leave = null): void
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date   = now()->addDays(6)->format('Y-m-d');

        if ($leave && $leave->exists) {
            $this->leave                   = $leave;
            $this->employee_id             = $leave->employee_id;
            $this->type                    = $leave->type;
            $this->start_date              = $leave->start_date->format('Y-m-d');
            $this->end_date                = $leave->end_date->format('Y-m-d');
            $this->reason                  = $leave->reason ?? '';
            $this->notes                   = $leave->notes ?? '';
            $this->imss_certificate_number = $leave->imss_certificate_number ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:hr_employees,id',
            'type'        => 'required|in:' . implode(',', array_keys(HrLeave::TYPES)),
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string',
            'notes'       => 'nullable|string',
            'imss_certificate_number' => 'nullable|string|max:50',
        ];
    }

    public function save(): void
    {
        $this->validate();

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
            'created_by'             => auth()->id(),
        ];

        if ($this->leave && $this->leave->exists) {
            $this->leave->update($data);
            session()->flash('success', 'Registro actualizado correctamente.');
        } else {
            $data['status'] = 'pending';
            // Update vacation balance pending days if type is vacation
            if ($this->type === 'vacaciones') {
                $emp = HrEmployee::find($this->employee_id);
                $balance = HrVacationBalance::forEmployee($emp, now()->year);
                $balance->days_pending_approval += $businessDays;
                $balance->recalculate();
            }
            HrLeave::create($data);
            session()->flash('success', 'Solicitud registrada correctamente.');
        }

        $this->redirect(route('hr.leaves.index'), navigate: true);
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
        $employees = HrEmployee::where('status', 'active')
            ->orWhere('status', 'on_leave')
            ->orderBy('last_name')
            ->get(['id','first_name','last_name','second_last_name']);

        return view('livewire.hr.leave-form', compact('employees'));
    }
}
