<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinanceTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id', 'transfer_to_account_id', 'project_id', 'tender_id', 'libranza_id', 'registered_by',
        'folio', 'type', 'concept', 'category', 'amount', 'currency',
        'exchange_rate', 'transaction_date', 'reference', 'status', 'notes',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'exchange_rate'    => 'decimal:6',
        'transaction_date' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'account_id');
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'transfer_to_account_id');
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

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function bankStatementLines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class, 'finance_transaction_id');
    }

    public function getAmountMxnAttribute(): float
    {
        return $this->amount * $this->exchange_rate;
    }
}
