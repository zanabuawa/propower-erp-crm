<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceCashflow extends Model
{
    use SoftDeletes;

    protected $table = 'finance_cashflow';

    protected $fillable = [
        'account_id', 'project_id', 'tender_id', 'libranza_id', 'budget_id', 'concept',
        'type', 'flow', 'category', 'amount', 'currency',
        'expected_date', 'realized_date', 'is_realized', 'notes',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'expected_date' => 'date',
        'realized_date' => 'date',
        'is_realized'   => 'boolean',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function libranza(): BelongsTo
    {
        return $this->belongsTo(WorkLibranza::class, 'libranza_id');
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(FinanceBudget::class, 'budget_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        return !$this->is_realized && $this->expected_date->isPast();
    }
}
