<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceDailyBalance extends Model
{
    protected $fillable = [
        'finance_account_id', 'balance_date',
        'opening_balance', 'total_income', 'total_expense',
        'closing_balance', 'transaction_count',
    ];

    protected $casts = [
        'balance_date'    => 'date',
        'opening_balance' => 'decimal:2',
        'total_income'    => 'decimal:2',
        'total_expense'   => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }
}
