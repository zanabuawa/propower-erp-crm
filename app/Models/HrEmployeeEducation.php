<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeEducation extends Model
{
    protected $table = 'hr_employee_education';

    protected $fillable = [
        'employee_id', 'institution', 'degree', 'field_of_study',
        'start_date', 'end_date', 'is_completed', 'certificate_path', 'notes'
    ];

    protected $casts = [
        'start_date'   => 'date',
        'end_date'     => 'date',
        'is_completed' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
