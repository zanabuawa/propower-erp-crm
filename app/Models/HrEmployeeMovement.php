<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeMovement extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_employee_movements';

    protected $fillable = [
        'company_id', 'employee_id', 'registered_by',
        'movement_type', 'effective_date',
        'previous_value', 'new_value', 'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'previous_value' => 'array',
        'new_value'      => 'array',
    ];

    const TYPES = [
        'alta'            => 'Alta',
        'baja'            => 'Baja',
        'ascenso'         => 'Ascenso',
        'descenso'        => 'Descenso',
        'cambio_salario'  => 'Cambio de salario',
        'traslado'        => 'Traslado',
        'cambio_contrato' => 'Cambio de contrato',
        'suspension'      => 'Suspensión',
        'reactivacion'    => 'Reactivación',
        'otro'            => 'Otro',
    ];

    const TYPE_COLORS = [
        'alta'            => 'bg-green-100 text-green-700',
        'baja'            => 'bg-red-100 text-red-700',
        'ascenso'         => 'bg-blue-100 text-blue-700',
        'descenso'        => 'bg-orange-100 text-orange-700',
        'cambio_salario'  => 'bg-indigo-100 text-indigo-700',
        'traslado'        => 'bg-purple-100 text-purple-700',
        'cambio_contrato' => 'bg-yellow-100 text-yellow-700',
        'suspension'      => 'bg-red-100 text-red-600',
        'reactivacion'    => 'bg-teal-100 text-teal-700',
        'otro'            => 'bg-gray-100 text-gray-600',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
