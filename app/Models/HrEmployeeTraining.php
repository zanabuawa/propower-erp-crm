<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeTraining extends Model
{
    protected $table = 'hr_employee_training';

    protected $fillable = [
        'employee_id', 'course_id', 'completion_date', 'expiry_date',
        'score', 'certificate_path', 'status'
    ];

    protected $casts = [
        'completion_date' => 'date',
        'expiry_date'     => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(HrCourse::class, 'course_id');
    }
}
