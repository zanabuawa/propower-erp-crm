<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrVacationBalance extends Model
{
    use BelongsToCompany;

    protected $table = 'hr_vacation_balances';

    protected $fillable = [
        'company_id', 'employee_id', 'year',
        'days_earned', 'days_used', 'days_pending_approval', 'days_available',
    ];

    protected $casts = [
        'days_earned'            => 'decimal:2',
        'days_used'              => 'decimal:2',
        'days_pending_approval'  => 'decimal:2',
        'days_available'         => 'decimal:2',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrEmployee::class, 'employee_id');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    public function recalculate(): void
    {
        $this->days_available = max(0, $this->days_earned - $this->days_used - $this->days_pending_approval);
        $this->save();
    }

    /** Get or create balance record for given employee + year */
    public static function forEmployee(HrEmployee $employee, int $year): self
    {
        return static::firstOrCreate(
            ['employee_id' => $employee->id, 'year' => $year, 'company_id' => $employee->company_id],
            [
                'days_earned'           => $employee->vacation_days_by_law,
                'days_used'             => 0,
                'days_pending_approval' => 0,
                'days_available'        => $employee->vacation_days_by_law,
            ]
        );
    }
}
