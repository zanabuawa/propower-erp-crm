<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankStatementLine extends Model
{
    protected $fillable = [
        'bank_reconciliation_id', 'finance_transaction_id',
        'transaction_date', 'description', 'reference',
        'amount', 'balance', 'flow', 'match_status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'decimal:2',
        'balance'          => 'decimal:2',
    ];

    public const MATCH_COLORS = [
        'unmatched' => 'bg-red-50 text-red-600',
        'matched'   => 'bg-green-50 text-green-700',
        'manual'    => 'bg-blue-50 text-blue-700',
    ];

    public const MATCH_LABELS = [
        'unmatched' => 'Sin match',
        'matched'   => 'Automático',
        'manual'    => 'Manual',
    ];

    public function reconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class, 'bank_reconciliation_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(FinanceTransaction::class, 'finance_transaction_id');
    }
}
