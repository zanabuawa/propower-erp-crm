<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleCreditNoteItem extends Model
{
    protected $fillable = [
        'sale_credit_note_id', 'product_id', 'warehouse_id',
        'description', 'quantity', 'unit_price', 'tax_rate', 'subtotal',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate'   => 'decimal:2',
        'subtotal'   => 'decimal:2',
    ];

    public function creditNote(): BelongsTo { return $this->belongsTo(SaleCreditNote::class, 'sale_credit_note_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}