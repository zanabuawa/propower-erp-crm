<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierBankAccount extends Model
{
    protected $fillable = [
        'supplier_id', 'bank_name', 'account_number', 'clabe', 'beneficiary', 'is_primary',
    ];

    protected $casts = ['is_primary' => 'boolean'];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}