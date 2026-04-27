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
