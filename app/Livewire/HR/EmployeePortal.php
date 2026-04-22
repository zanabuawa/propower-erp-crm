<?php

namespace App\Livewire\HR;

use App\Models\HrAttendance;
use App\Models\HrEmployee;
use App\Models\HrAttendanceLocation;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Mi Portal de Empleado')]
class EmployeePortal extends Component
{
    public ?HrEmployee $employee = null;
    public ?HrAttendance $todayAttendance = null;
    public string $currentTime;

    // Propiedades para ubicación
    public ?float $latitude        = null;
    public ?float $longitude       = null;
    public string $geoStatus       = 'idle'; // idle | loading | located | denied | error
    public bool   $locationValid   = false;
    public ?float $currentDistance = null;
    public ?HrAttendanceLocation $detectedZone = null;

    public function mount()
    {
        $user = auth()->user();
        
        // Vincular con el registro de empleado
        $this->employee = HrEmployee::where('user_id', $user->id)->first();

        if (!$this->employee) {
            session()->flash('error', 'No se encontró un registro de empleado vinculado a tu usuario.');
            return;
        }

        $this->loadTodayAttendance();
        $this->currentTime = now()->format('H:i:s');
    }

    public function loadTodayAttendance()
    {
        $this->todayAttendance = HrAttendance::where('employee_id', $this->employee->id)
            ->where('date', now()->toDateString())
            ->first();
    }

    // Métodos para geolocalización
    public function setCoordinates(float $lat, float $lng): void
    {
        $this->latitude   = $lat;
        $this->longitude  = $lng;
        $this->geoStatus  = 'located';

        if (!$this->employee) return;

        $companyId = auth()->user()->company_id;
        $zones = HrAttendanceLocation::where('company_id', $companyId)->where('is_active', true)->get();
        
        $this->currentDistance = null;
        $closest = null;

        foreach ($zones as $zone) {
            $d = $zone->distanceTo($lat, $lng);
            if ($this->currentDistance === null || $d < $this->currentDistance) {
                $this->currentDistance = $d;
                $closest = $zone;
            }
        }

        $this->detectedZone  = ($closest && $this->currentDistance <= $closest->radius_meters) ? $closest : null;
        $this->locationValid = $this->detectedZone !== null;
    }

    public function geoDenied() { $this->geoStatus = 'denied'; }
    public function geoError()  { $this->geoStatus = 'error'; }

    public function checkIn()
    {
        if ($this->todayAttendance || !$this->locationValid) return;

        $checkInTime = now();
        
        $this->todayAttendance = HrAttendance::create([
            'company_id'        => $this->employee->company_id,
            'employee_id'       => $this->employee->id,
            'date'              => $checkInTime->toDateString(),
            'check_in'          => $checkInTime->toTimeString(),
            'status'            => $this->calculateStatus($checkInTime),
            'checkin_latitude'  => $this->latitude,
            'checkin_longitude' => $this->longitude,
            'location_id'       => $this->detectedZone->id,
            'location_valid'    => true,
            'recorded_by'       => auth()->id(),
        ]);

        session()->flash('success', 'Entrada registrada correctamente a las ' . $checkInTime->format('H:i A'));
    }

    public function checkOut()
    {
        if (!$this->todayAttendance || $this->todayAttendance->check_out || !$this->locationValid) return;

        $checkOutTime = now();
        $checkInTime = Carbon::parse($this->todayAttendance->check_in);
        
        // Calcular horas trabajadas
        $diffInMinutes = $checkOutTime->diffInMinutes($checkInTime);
        $workedHours = round($diffInMinutes / 60, 2);

        $this->todayAttendance->update([
            'check_out'    => $checkOutTime->toTimeString(),
            'worked_hours' => $workedHours,
            // Guardar también coordenadas de salida si se desea
            'checkout_latitude'  => $this->latitude,
            'checkout_longitude' => $this->longitude,
        ]);

        session()->flash('success', 'Salida registrada correctamente. Total horas: ' . $workedHours);
    }

    private function calculateStatus($time)
    {
        // Lógica simple: si entra después de las 09:10 es retardo (late)
        $limit = Carbon::today()->setTime(9, 10);
        return $time->greaterThan($limit) ? 'late' : 'present';
    }

    public function render()
    {
        $recentAttendances = $this->employee 
            ? HrAttendance::where('employee_id', $this->employee->id)
                ->latest('date')
                ->take(5)
                ->get()
            : collect();

        return view('livewire.hr.employee-portal', compact('recentAttendances'));
    }
}
