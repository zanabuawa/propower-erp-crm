<?php

namespace App\Livewire\HR;

use App\Models\HrAttendance;
use App\Models\HrAttendanceLocation;
use App\Models\HrEmployee;
use App\Models\HrEvaluationProcess;
use App\Services\HrPayrollCalculator;
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
    public ?float $locationAccuracy = null;
    public ?float $allowedDistance = null;
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
    public function setCoordinates(float $lat, float $lng, ?float $accuracy = null): void
    {
        $this->latitude   = $lat;
        $this->longitude  = $lng;
        $this->locationAccuracy = $accuracy;
        $this->geoStatus  = 'located';

        if (!$this->employee) return;

        $companyId = auth()->user()->company_id;
        $zones = HrAttendanceLocation::where('company_id', $companyId)->where('is_active', true)->get();
        
        $this->currentDistance = null;
        $this->allowedDistance = null;
        $closest = null;

        foreach ($zones as $zone) {
            $d = $zone->distanceTo($lat, $lng);
            if ($this->currentDistance === null || $d < $this->currentDistance) {
                $this->currentDistance = $d;
                $closest = $zone;
            }
        }

        $tolerance = $this->gpsTolerance();
        $this->allowedDistance = $closest ? ($closest->radius_meters + $tolerance) : null;
        $this->detectedZone  = ($closest && $this->currentDistance <= $this->allowedDistance) ? $closest : null;
        $this->locationValid = $this->detectedZone !== null;
    }

    private function gpsTolerance(): float
    {
        if ($this->locationAccuracy === null) {
            return 0;
        }

        return min(max($this->locationAccuracy, 0), 100);
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
        $workedHours = HrAttendance::calculateWorkedHours(
            $this->todayAttendance->date ?? today(),
            $this->todayAttendance->check_in,
            $checkOutTime->toTimeString()
        );

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

    public function embedVideoUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST) ?: '';
        $path = trim(parse_url($url, PHP_URL_PATH) ?: '', '/');
        $query = [];
        parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);
        $videoId = null;

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        }

        if (! $videoId && str_contains($host, 'youtube.com')) {
            $videoId = $query['v'] ?? null;

            if (! $videoId && preg_match('~(?:embed|shorts|live)/([^/?&]+)~', $path, $matches)) {
                $videoId = $matches[1];
            }
        }

        if ($videoId) {
            $videoId = preg_replace('/[^A-Za-z0-9_-]/', '', $videoId);

            return $videoId ? "https://www.youtube-nocookie.com/embed/{$videoId}?rel=0&modestbranding=1" : null;
        }

        if (str_contains($host, 'vimeo.com') && preg_match('/(\d+)/', $path, $matches)) {
            return "https://player.vimeo.com/video/{$matches[1]}";
        }

        return null;
    }

    public function render()
    {
        $recentAttendances = $this->employee 
            ? HrAttendance::where('employee_id', $this->employee->id)
                ->latest('date')
                ->take(5)
                ->get()
            : collect();

        $evaluationProcesses = $this->employee
            ? HrEvaluationProcess::with([
                'stages.prospectTests.template',
                'stages.prospectTests.attempts',
            ])
                ->where(function ($query) {
                    $query->where('hr_employee_id', $this->employee->id)
                        ->orWhereHas('prospect', fn ($prospectQuery) => $prospectQuery->where('employee_id', $this->employee->id));
                })
                ->whereIn('status', ['active', 'completed'])
                ->latest()
                ->get()
            : collect();

        $evaluationSummary = $evaluationProcesses
            ->map(function (HrEvaluationProcess $process) {
                $stages = $process->stages->map(function ($stage) use ($process) {
                    $tests = $stage->prospectTests->map(function ($test) use ($stage, $process) {
                        $inReview = in_array($test->status, ['pending_review', 'partially_graded'], true);
                        $completed = in_array($test->status, ['completed', 'graded'], true);
                        $withoutAttempts = $test->attempts_count >= $test->max_attempts;
                        $canStart = $process->status === 'active'
                            && $stage->order === $process->current_stage_index
                            && $stage->isAvailable()
                            && ! $inReview
                            && ! $completed
                            && ! $withoutAttempts;

                        return [
                            'model' => $test,
                            'in_review' => $inReview,
                            'completed' => $completed,
                            'without_attempts' => $withoutAttempts,
                            'can_start' => $canStart,
                        ];
                    })->values();

                    return [
                        'model' => $stage,
                        'is_current' => $process->status === 'active' && $stage->order === $process->current_stage_index,
                        'is_available' => $stage->isAvailable(),
                        'is_completed' => $process->status === 'completed' || $stage->status === 'completed' || $stage->order < $process->current_stage_index,
                        'tests' => $tests,
                    ];
                })->values();

                $pendingTestsCount = $process->status === 'active'
                    ? $stages->sum(fn ($stage) => $stage['tests']->where('completed', false)->count())
                    : 0;

                return [
                    'process' => $process,
                    'stages' => $stages,
                    'pending_tests_count' => $pendingTestsCount,
                    'is_completed' => $process->status === 'completed',
                ];
            })
            ->values();

        $payrollItems = $this->employee
            ? $this->employee->payrollItems()
                ->with('payroll')
                ->whereHas('payroll', fn ($query) => $query->whereIn('status', ['paid', 'stamped']))
                ->latest()
                ->take(8)
                ->get()
            : collect();

        $payrollEstimate = null;
        $payrollEstimatePeriod = null;

        if ($this->employee) {
            $estimateStart = now()->copy()->startOfWeek()->format('Y-m-d');
            $estimateEnd = now()->copy()->endOfWeek()->format('Y-m-d');
            $estimateItems = app(HrPayrollCalculator::class)->calculate(
                $this->employee->company_id,
                $estimateStart,
                $estimateEnd,
                $this->employee->id,
            );

            $payrollEstimate = $estimateItems[$this->employee->id] ?? null;
            $payrollEstimatePeriod = [
                'start' => $estimateStart,
                'end' => $estimateEnd,
            ];
        }

        return view('livewire.hr.employee-portal', compact(
            'recentAttendances',
            'evaluationSummary',
            'payrollItems',
            'payrollEstimate',
            'payrollEstimatePeriod',
        ));
    }
}
