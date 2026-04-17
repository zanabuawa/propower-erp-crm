<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierCreditNoteItem extends Model
{
    protected $fillable = [
        'supplier_credit_note_id', 'product_id',
        'description', 'quantity', 'unit_price', 'tax_rate', 'subtotal',
    ];

    protected $casts = [
        'quantity'   => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_rate'   => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function creditNote(): BelongsTo
    {
        return $this->belongsTo(SupplierCreditNote::class, 'supplier_credit_note_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
