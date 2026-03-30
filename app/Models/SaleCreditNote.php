<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleCreditNote extends Model
{
    protected $fillable = [
        'company_id', 'sale_invoice_id', 'customer_id', 'created_by',
        'folio', 'currency', 'status', 'reason', 'subtotal', 'tax', 'total',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax'      => 'decimal:2',
        'total'    => 'decimal:2',
    ];

    const STATUS = [
        'draft'     => 'Borrador',
        'applied'   => 'Aplicada',
        'cancelled' => 'Cancelada',
    ];

    public function invoice(): BelongsTo { return $this->belongsTo(SaleInvoice::class, 'sale_invoice_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function items(): HasMany { return $this->hasMany(SaleCreditNoteItem::class); }
}