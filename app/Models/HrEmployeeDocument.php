<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrEmployeeDocument extends Model
{
    protected $table = 'hr_employee_documents';

    protected $fillable = [
        'employee_id', 'document_type', 'file_path',
        'issue_date', 'expiry_date', 'notes'
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'expiry_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }
}
