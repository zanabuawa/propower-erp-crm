<?php

namespace App\Livewire\HR;

use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrEmployeeMovement;
use App\Models\HrPosition;
use App\Models\TravelExpense;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Expediente de Empleado')]
class EmployeeShow extends Component
{
    public HrEmployee $employee;

    public string $activeTab = 'info';

    // Modal de movimiento
    public bool   $showMovementModal  = false;
    public string $movementType       = 'otro';
    public string $movementDate       = '';
    public string $movementNotes      = '';

    // Campos según tipo de movimiento
    public ?int    $newPositionId     = null;
    public ?int    $newDepartmentId   = null;
    public string  $newSalary         = '';
    public string  $newStatus         = '';
    public string  $newContractType   = '';

    public array $positionOptions   = [];
    public array $departmentOptions = [];

    protected $listeners = ['refreshEmployeeShow' => '$refresh'];

    public function mount(HrEmployee $employee): void
    {
        $this->employee = $employee->load([
            'department', 'position', 'branch', 'supervisor',
            'activeContract', 'contracts',
            'leaves'    => fn($q) => $q->latest()->limit(10),
            'incidents' => fn($q) => $q->latest()->limit(10),
            'evaluations' => fn($q) => $q->latest()->limit(5),
            'vacationBalances' => fn($q) => $q->where('year', now()->year),
            'projects',
            'education' => fn($q) => $q->latest(),
            'trainings.course',
            'documents' => fn($q) => $q->latest(),
            'movements.registeredBy' => fn($q) => $q->latest('effective_date'),
        ]);

        $companyId = auth()->user()->company_id;
        $this->movementDate     = now()->toDateString();
        $this->positionOptions  = HrPosition::where('company_id', $companyId)->orderBy('name')->get(['id', 'name'])->toArray();
        $this->departmentOptions= HrDepartment::where('company_id', $companyId)->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ── Movimientos ───────────────────────────────────────────────────────────

    public function openMovementModal(): void
    {
        $this->reset(['movementType', 'movementNotes', 'newPositionId', 'newDepartmentId', 'newSalary', 'newStatus', 'newContractType']);
        $this->movementType = 'otro';
        $this->movementDate = now()->toDateString();
        $this->showMovementModal = true;
    }

    public function saveMovement(): void
    {
        $this->validate([
            'movementType' => 'required|string',
            'movementDate' => 'required|date',
        ], [
            'movementType.required' => 'Selecciona el tipo de movimiento.',
            'movementDate.required' => 'La fecha efectiva es obligatoria.',
        ]);

        $previous = [];
        $new      = [];

        // Capturar valores anteriores y nuevos según tipo
        if (in_array($this->movementType, ['ascenso', 'descenso', 'traslado'])) {
            $previous['position_id']   = $this->employee->position_id;
            $previous['position_name'] = $this->employee->position?->name;
            $previous['department_id'] = $this->employee->department_id;
            $previous['department_name'] = $this->employee->department?->name;

            if ($this->newPositionId) {
                $pos = collect($this->positionOptions)->firstWhere('id', $this->newPositionId);
                $new['position_id']   = $this->newPositionId;
                $new['position_name'] = $pos['name'] ?? '';
                $this->employee->update(['position_id' => $this->newPositionId]);
            }
            if ($this->newDepartmentId) {
                $dep = collect($this->departmentOptions)->firstWhere('id', $this->newDepartmentId);
                $new['department_id']   = $this->newDepartmentId;
                $new['department_name'] = $dep['name'] ?? '';
                $this->employee->update(['department_id' => $this->newDepartmentId]);
            }
        }

        if ($this->movementType === 'cambio_salario') {
            $previous['salary']        = $this->employee->salary;
            $previous['salary_period'] = $this->employee->salary_period;
            if ($this->newSalary) {
                $new['salary'] = $this->newSalary;
                $this->employee->update(['salary' => $this->newSalary]);
            }
        }

        if (in_array($this->movementType, ['baja', 'suspension', 'reactivacion'])) {
            $previous['status'] = $this->employee->status;
            $newStatus = match($this->movementType) {
                'baja'        => 'inactive',
                'suspension'  => 'suspended',
                'reactivacion'=> 'active',
                default       => $this->employee->status,
            };
            $new['status'] = $newStatus;
            $this->employee->update(['status' => $newStatus]);
        }

        if ($this->movementType === 'cambio_contrato' && $this->newContractType) {
            $previous['contract_type'] = $this->employee->contract_type;
            $new['contract_type']      = $this->newContractType;
            $this->employee->update(['contract_type' => $this->newContractType]);
        }

        HrEmployeeMovement::create([
            'company_id'     => auth()->user()->company_id,
            'employee_id'    => $this->employee->id,
            'registered_by'  => auth()->id(),
            'movement_type'  => $this->movementType,
            'effective_date' => $this->movementDate,
            'previous_value' => $previous ?: null,
            'new_value'      => $new ?: null,
            'notes'          => $this->movementNotes ?: null,
        ]);

        $this->showMovementModal = false;
        $this->employee->refresh()->load([
            'department', 'position', 'movements.registeredBy',
        ]);
        session()->flash('success', 'Movimiento registrado correctamente.');
    }

    // ── Viáticos ──────────────────────────────────────────────────────────────

    public function confirmTrip(int $travelId): void
    {
        $travel = TravelExpense::where('employee_id', $this->employee->id)
            ->where('status', 'pagado')
            ->findOrFail($travelId);

        $travel->update([
            'trip_confirmed'    => true,
            'trip_confirmed_at' => now(),
            'trip_confirmed_by' => auth()->id(),
        ]);

        session()->flash('success', "Viaje {$travel->folio} confirmado.");
    }

    public function render()
    {
        $recentPayrolls = $this->employee->payrollItems()
            ->with('payroll')
            ->latest()
            ->limit(6)
            ->get();

        $travelExpenses = TravelExpense::with(['assignedBy', 'project', 'financeAccount'])
            ->where('employee_id', $this->employee->id)
            ->latest()
            ->get();

        return view('livewire.hr.employee-show', compact('recentPayrolls', 'travelExpenses'));
    }
}
