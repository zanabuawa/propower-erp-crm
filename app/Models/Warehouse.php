<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'branch_id', 'name', 'code', 'location', 'is_active', 'is_defective',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_defective' => 'boolean',
    ];

    /**
     * Warehouses accessible to a user: own branch + cross-branch if has permission.
     *
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Warehouse>
     */
    public static function forUser(\App\Models\User $user): \Illuminate\Database\Eloquent\Builder
    {
        $query = static::query()->where('company_id', $user->company_id)->where('is_active', true);

        try {
            $hasCrossBranch = $user->hasPermissionTo('access other branches warehouses');
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist) {
            $hasCrossBranch = false;
        }

        if (!$hasCrossBranch && $user->branch_id) {
            $query->where('branch_id', $user->branch_id);
        }

        return $query;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }
}