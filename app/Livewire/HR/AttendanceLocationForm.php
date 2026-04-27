<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrAttendanceLocation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Formulario de Zona de Asistencia')]
class AttendanceLocationForm extends Component
{
    public ?HrAttendanceLocation $attendanceLocation = null;

    public string $name           = '';
    public string $address        = '';
    public string $latitude       = '';
    public string $longitude      = '';
    public int    $radius_meters  = 100;
    public ?int   $branch_id      = null;
    public bool   $is_active      = true;
    public string $notes          = '';

    public function mount(?HrAttendanceLocation $attendanceLocation = null): void
    {
        if ($attendanceLocation && $attendanceLocation->exists) {
            $this->attendanceLocation = $attendanceLocation;
            $this->name               = $attendanceLocation->name;
            $this->address            = $attendanceLocation->address ?? '';
            $this->latitude           = (string) $attendanceLocation->latitude;
            $this->longitude          = (string) $attendanceLocation->longitude;
            $this->radius_meters      = $attendanceLocation->radius_meters;
            $this->branch_id          = $attendanceLocation->branch_id;
            $this->is_active          = $attendanceLocation->is_active;
            $this->notes              = $attendanceLocation->notes ?? '';
        }
    }

    public function rules(): array
    {
        return [
            'name'         => 'required|string|max:100',
            'address'      => 'nullable|string|max:255',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'radius_meters'=> 'required|integer|min:10|max:5000',
            'branch_id'    => 'nullable|exists:branches,id',
            'is_active'    => 'boolean',
            'notes'        => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'          => $this->name,
            'address'       => $this->address ?: null,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'radius_meters' => $this->radius_meters,
            'branch_id'     => $this->branch_id ?: null,
            'is_active'     => $this->is_active,
            'notes'         => $this->notes ?: null,
        ];

        if ($this->attendanceLocation && $this->attendanceLocation->exists) {
            $this->attendanceLocation->update($data);
            session()->flash('success', 'Zona actualizada correctamente.');
        } else {
            HrAttendanceLocation::create(array_merge($data, [
                'company_id' => auth()->user()->company_id,
            ]));
            session()->flash('success', 'Zona de asistencia creada correctamente.');
        }

        $this->redirect(route('hr.attendance.locations'), navigate: true);
    }

    public function render()
    {
        $branches = Branch::where('company_id', auth()->user()->company_id)
            ->orderBy('name')->get(['id', 'name']);

        return view('livewire.hr.attendance-location-form', compact('branches'));
    }
}
