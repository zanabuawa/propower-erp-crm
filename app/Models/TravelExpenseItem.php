<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelExpenseItem extends Model
{
    protected $fillable = [
        'travel_expense_id', 'category', 'concept',
        'amount', 'receipt_number', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function travelExpense(): BelongsTo
    {
        return $this->belongsTo(TravelExpense::class);
    }
}
