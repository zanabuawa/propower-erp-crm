<?php

namespace App\Livewire\HR;

use App\Models\HrAttendance;
use App\Models\HrAttendanceLocation;
use App\Models\HrEmployee;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Registrar Asistencia')]
class AttendanceCheckin extends Component
{
    public ?HrEmployee $employee = null;

    // Geolocation state from JS
    public ?float $latitude    = null;
    public ?float $longitude   = null;
    public string $geoStatus   = 'idle'; // idle | loading | located | denied | error

    // Validation result
    public ?HrAttendanceLocation $detectedZone = null;
    public bool $locationValid = false;
    public ?float $currentDistance = null;
    public ?float $locationAccuracy = null;

    // Today's attendance
    public ?HrAttendance $todayAttendance = null;

    public ?object $activeContract = null;

    public function mount(): void
    {
        $user = auth()->user();

        $this->employee = HrEmployee::where('user_id', $user->id)
            ->where('company_id', $user->company_id)
            ->where('status', 'active')
            ->with(['activeContract'])
            ->first();

        if ($this->employee) {
            $this->activeContract = $this->employee->activeContract;
        }

        $this->loadTodayAttendance();
    }

    private function loadTodayAttendance(): void
    {
        if (! $this->employee) {
            return;
        }

        $this->todayAttendance = HrAttendance::where('employee_id', $this->employee->id)
            ->where('date', today())
            ->first();
    }

    // Called from JS after geolocation is obtained
    public function setCoordinates(float $lat, float $lng, ?float $accuracy = null): void
    {
        $this->latitude   = $lat;
        $this->longitude  = $lng;
        $this->locationAccuracy = $accuracy;
        $this->geoStatus  = 'located';

        if (! $this->employee) {
            return;
        }

        $zone = HrAttendanceLocation::findContaining(
            auth()->user()->company_id,
            $lat,
            $lng,
            $this->gpsTolerance()
        );

        $this->detectedZone  = $zone;
        $this->locationValid = $zone !== null;
    }

    private function gpsTolerance(): float
    {
        if ($this->locationAccuracy === null) {
            return 0;
        }

        return min(max($this->locationAccuracy, 0), 100);
    }

    public function geoDenied(): void
    {
        $this->geoStatus = 'denied';
    }

    public function geoError(): void
    {
        $this->geoStatus = 'error';
    }

    public function checkin(): void
    {
        abort_unless($this->employee !== null, 403);
        abort_unless($this->locationValid, 422);

        $this->loadTodayAttendance();

        $att = $this->todayAttendance;

        if ($att && $att->check_in && ! $att->check_out) {
            // Register check-out
            $checkIn  = \Carbon\Carbon::parse($att->check_in);
            $checkOut = now();
            $worked   = round($checkIn->diffInMinutes($checkOut) / 60, 2);

            // Expected hours from contract (for overtime calculation)
            $expectedHours = $this->activeContract
                ? (float) $this->activeContract->expectedHoursOn(today())
                : 8.0;
            $overtime = max(0, round($worked - $expectedHours, 2));

            $att->update([
                'check_out'      => $checkOut->format('H:i:s'),
                'worked_hours'   => $worked,
                'overtime_hours' => $overtime > 0 ? $overtime : null,
            ]);

            session()->flash('success', 'Salida registrada a las ' . $checkOut->format('H:i') . '.');
        } else {
            // Register check-in
            $checkIn   = now();
            $zoneId    = $this->detectedZone->id;
            $companyId = auth()->user()->company_id;

            // Auto-detect tardiness from contract schedule
            $status = 'present';
            if ($this->activeContract && $this->activeContract->isLate($checkIn->format('H:i:s'))) {
                $status = 'late';
            }

            HrAttendance::updateOrCreate(
                ['employee_id' => $this->employee->id, 'date' => today()->toDateString()],
                [
                    'company_id'        => $companyId,
                    'check_in'          => $checkIn->format('H:i:s'),
                    'check_out'         => null,
                    'status'            => $status,
                    'checkin_latitude'  => $this->latitude,
                    'checkin_longitude' => $this->longitude,
                    'location_valid'    => true,
                    'location_id'       => $zoneId,
                    'recorded_by'       => auth()->id(),
                ]
            );

            $msg = 'Entrada registrada a las ' . $checkIn->format('H:i');
            if ($status === 'late') {
                $msg .= ' (tardanza)';
            }
            session()->flash('success', $msg . '.');
        }

        $this->loadTodayAttendance();
    }

    public function render()
    {
        return view('livewire.hr.attendance-checkin');
    }
}
