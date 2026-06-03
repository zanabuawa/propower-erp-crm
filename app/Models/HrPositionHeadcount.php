<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrPositionHeadcount extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_position_headcounts';

    protected $fillable = [
        'company_id', 'position_id', 'branch_id', 'headcount',
    ];

    protected $casts = [
        'headcount' => 'integer',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(HrPosition::class, 'position_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Number of active employees currently filling this slot.
     */
    public function filledCount(): int
    {
        return HrEmployee::where('position_id', $this->position_id)
            ->where('branch_id', $this->branch_id)
            ->whereIn('status', ['active', 'on_leave'])
            ->count();
    }

    /**
     * Available slots = headcount - filled employees.
     */
    public function availableCount(): int
    {
        return max(0, $this->headcount - $this->filledCount());
    }
}
