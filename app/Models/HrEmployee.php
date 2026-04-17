<?php

namespace App\Models;

use App\Models\Project;
use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class HrEmployee extends Model
{
    use BelongsToCompany, SoftDeletes;

    protected $table = 'hr_employees';

    protected $fillable = [
        'company_id', 'user_id', 'branch_id', 'department_id', 'position_id', 'supervisor_id',
        'employee_number', 'first_name', 'last_name', 'second_last_name',
        'curp', 'rfc', 'nss', 'email', 'phone', 'birth_date', 'gender',
        'address', 'city', 'state', 'postal_code',
        'hire_date', 'termination_date', 'termination_reason', 'termination_type',
        'contract_type', 'salary', 'salary_period',
        'work_shift', 'status', 'is_external', 'payment_method', 'bank', 'bank_account', 'clabe',
        'imss_regime', 'daily_salary_imss', 'infonavit_credit',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',
        'photo', 'notes',
    ];

    protected $casts = [
        'birth_date'       => 'date',
        'hire_date'        => 'date',
        'termination_date' => 'date',
        'salary'           => 'decimal:2',
        'daily_salary_imss'=> 'decimal:2',
        'is_external'      => 'boolean',
    ];

    const CONTRACT_TYPES = [
        'indefinido'          => 'Tiempo indefinido',
        'temporal'            => 'Temporal',
        'honorarios'          => 'Honorarios',
        'obra_determinada'    => 'Obra determinada',
        'capacitacion_inicial'=> 'Capacitación inicial',
    ];

    const SALARY_PERIODS = [
        'daily'     => 'Diario',
        'weekly'    => 'Semanal',
        'biweekly'  => 'Quincenal',
        'monthly'   => 'Mensual',
    ];

    const STATUSES = [
        'active'    => 'Activo',
        'inactive'  => 'Inactivo',
        'on_leave'  => 'Con permiso',
        'suspended' => 'Suspendido',
    ];

    const STATUS_COLORS = [
        'active'    => 'bg-green-100 text-green-700',
        'inactive'  => 'bg-red-100 text-red-700',
        'on_leave'  => 'bg-yellow-100 text-yellow-700',
        'suspended' => 'bg-orange-100 text-orange-700',
    ];

    const PAYMENT_METHODS = [
        'efectivo'      => 'Efectivo',
        'transferencia' => 'Transferencia bancaria',
        'cheque'        => 'Cheque',
    ];

    const WORK_SHIFTS = [
        'matutino'  => 'Matutino',
        'vespertino'=> 'Vespertino',
        'nocturno'  => 'Nocturno',
        'mixto'     => 'Mixto',
    ];

    const GENDERS = [
        'masculino' => 'Masculino',
        'femenino'  => 'Femenino',
        'otro'      => 'Otro / Prefiero no decir',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(HrDepartment::class, 'department_id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(HrPosition::class, 'position_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'supervisor_id');
    }

    public function subordinates(): HasMany
    {
        return $this->hasMany(HrEmployee::class, 'supervisor_id');
    }

    public function education(): HasMany
    {
        return $this->hasMany(HrEmployeeEducation::class, 'employee_id');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(HrEmployeeTraining::class, 'employee_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(HrEmployeeDocument::class, 'employee_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(HrContract::class, 'employee_id');
    }

    public function activeContract(): HasOne
    {
        return $this->hasOne(HrContract::class, 'employee_id')->where('status', 'active')->latest();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(HrAttendance::class, 'employee_id');
    }

    public function payrollItems(): HasMany
    {
        return $this->hasMany(HrPayrollItem::class, 'employee_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(HrPerformanceEvaluation::class, 'employee_id');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(HrIncident::class, 'employee_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(HrLeave::class, 'employee_id');
    }

    public function vacationBalances(): HasMany
    {
        return $this->hasMany(HrVacationBalance::class, 'employee_id');
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_employees', 'employee_id', 'project_id')
            ->withPivot('role', 'start_date', 'end_date', 'hours_assigned', 'notes', 'is_active')
            ->withTimestamps()
            ->orderByPivot('is_active', 'desc');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name} {$this->second_last_name}");
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    public function getAntiquityYearsAttribute(): float
    {
        return round($this->hire_date->diffInDays(now()) / 365, 1);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /** Salario diario para cálculo de nómina */
    public function getDailySalaryAttribute(): float
    {
        return match ($this->salary_period) {
            'daily'     => (float) $this->salary,
            'weekly'    => (float) $this->salary / 7,
            'biweekly'  => (float) $this->salary / 15,
            'monthly'   => (float) $this->salary / 30,
            default     => (float) $this->salary / 30,
        };
    }

    /** Días de vacaciones según LFT por años de antigüedad */
    public function getVacationDaysByLawAttribute(): int
    {
        $years = floor($this->antiquity_years);
        return match (true) {
            $years >= 25 => 20,
            $years >= 20 => 18,
            $years >= 15 => 16,
            $years >= 10 => 14,
            $years >= 5  => 12,
            $years >= 4  => 12,
            $years >= 3  => 10,
            $years >= 2  => 8,
            $years >= 1  => 6,
            default      => 6,
        };
    }
}
