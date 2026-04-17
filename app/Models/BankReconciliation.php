<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'finance_account_id', 'created_by',
        'folio', 'period_from', 'period_to', 'status',
        'bank_opening_balance', 'bank_closing_balance',
        'book_opening_balance', 'book_closing_balance',
        'difference', 'notes', 'closed_at',
    ];

    protected $casts = [
        'period_from'          => 'date',
        'period_to'            => 'date',
        'bank_opening_balance' => 'decimal:2',
        'bank_closing_balance' => 'decimal:2',
        'book_opening_balance' => 'decimal:2',
        'book_closing_balance' => 'decimal:2',
        'difference'           => 'decimal:2',
        'closed_at'            => 'datetime',
    ];

    public const STATUS = [
        'draft'    => 'Borrador',
        'reviewed' => 'En revisión',
        'closed'   => 'Cerrada',
    ];

    public const STATUS_COLORS = [
        'draft'    => 'bg-gray-100 text-gray-600',
        'reviewed' => 'bg-amber-50 text-amber-700',
        'closed'   => 'bg-green-50 text-green-700',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'finance_account_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BankStatementLine::class);
    }
}
