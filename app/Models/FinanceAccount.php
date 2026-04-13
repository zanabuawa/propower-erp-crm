<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'branch_id', 'code', 'name', 'type',
        'bank_name', 'account_number', 'clabe', 'currency',
        'opening_balance', 'current_balance', 'is_active', 'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class, 'account_id');
    }

    public function cashflows(): HasMany
    {
        return $this->hasMany(FinanceCashflow::class, 'account_id');
    }
}
