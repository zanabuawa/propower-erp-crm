<?php

namespace App\Livewire\HR;

use App\Models\Branch;
use App\Models\HrAttendanceLocation;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Zonas de Asistencia')]
class AttendanceLocationIndex extends Component
{
    public bool   $showModal  = false;
    public ?int   $editingId  = null;

    public string $name           = '';
    public string $address        = '';
    public string $latitude       = '';
    public string $longitude      = '';
    public int    $radius_meters  = 100;
    public ?int   $branch_id      = null;
    public bool   $is_active      = true;
    public string $notes          = '';

    public array  $branchOptions  = [];

    public function mount(): void
    {
        $this->branchOptions = Branch::where('company_id', auth()->user()->company_id)
            ->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'address', 'latitude', 'longitude', 'radius_meters', 'branch_id', 'notes']);
        $this->radius_meters = 100;
        $this->is_active     = true;
        $this->showModal     = true;
    }

    public function openEdit(HrAttendanceLocation $location): void
    {
        $this->editingId     = $location->id;
        $this->name          = $location->name;
        $this->address       = $location->address ?? '';
        $this->latitude      = (string) $location->latitude;
        $this->longitude     = (string) $location->longitude;
        $this->radius_meters = $location->radius_meters;
        $this->branch_id     = $location->branch_id;
        $this->is_active     = $location->is_active;
        $this->notes         = $location->notes ?? '';
        $this->showModal     = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'         => 'required|string|max:100',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
            'radius_meters'=> 'required|integer|min:10|max:5000',
        ], [
            'name.required'      => 'El nombre de la zona es obligatorio.',
            'latitude.required'  => 'La latitud es obligatoria.',
            'longitude.required' => 'La longitud es obligatoria.',
            'latitude.between'   => 'Latitud inválida (-90 a 90).',
            'longitude.between'  => 'Longitud inválida (-180 a 180).',
            'radius_meters.min'  => 'El radio mínimo es 10 metros.',
            'radius_meters.max'  => 'El radio máximo es 5000 metros.',
        ]);

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

        if ($this->editingId) {
            HrAttendanceLocation::where('id', $this->editingId)->update($data);
            session()->flash('success', 'Zona actualizada.');
        } else {
            HrAttendanceLocation::create(array_merge($data, [
                'company_id' => auth()->user()->company_id,
            ]));
            session()->flash('success', 'Zona de asistencia creada.');
        }

        $this->showModal = false;
    }

    public function toggleActive(HrAttendanceLocation $location): void
    {
        $location->update(['is_active' => ! $location->is_active]);
    }

    public function delete(HrAttendanceLocation $location): void
    {
        $location->delete();
        session()->flash('success', 'Zona eliminada.');
    }

    public function render()
    {
        $locations = HrAttendanceLocation::where('company_id', auth()->user()->company_id)
            ->with('branch')
            ->orderBy('name')
            ->get();

        return view('livewire.hr.attendance-location-index', compact('locations'));
    }
}
