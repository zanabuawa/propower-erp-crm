<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class HrAttendanceLog extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_attendance_logs';

    protected $fillable = [
        'company_id', 'device_id', 'external_employee_id',
        'timestamp', 'type', 'raw_data', 'is_processed'
    ];

    protected $casts = [
        'timestamp'    => 'datetime',
        'raw_data'     => 'array',
        'is_processed' => 'boolean',
    ];
}
