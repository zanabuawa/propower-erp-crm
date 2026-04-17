<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrCourse extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_courses';

    protected $fillable = [
        'company_id', 'name', 'description', 'provider',
        'type', 'duration_hours', 'cost'
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'duration_hours' => 'integer',
    ];

    public function employeeTrainings(): HasMany
    {
        return $this->hasMany(HrEmployeeTraining::class, 'course_id');
    }
}
