<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrAttendanceSession extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_attendance_sessions';

    protected $fillable = [
        'company_id', 'name', 'start_date', 'end_date',
        'description', 'labels', 'checklist', 'members', 'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'labels'     => 'array',
        'checklist'  => 'array',
        'members'    => 'array',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(HrAttendance::class, 'session_id');
    }
}
