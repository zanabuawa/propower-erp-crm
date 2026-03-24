<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoice extends Model
{
    protected $fillable = [
        'company_id', 'purchase_order_id', 'supplier_id', 'folio',
        'supplier_invoice_number', 'currency', 'subtotal', 'tax', 'total',
        'status', 'issued_at', 'due_at', 'paid_at',
    ];

    protected $casts = [
        'subtotal'  => 'decimal:2',
        'tax'       => 'decimal:2',
        'total'     => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at'    => 'datetime',
        'paid_at'   => 'datetime',
    ];

    const STATUS = [
        'pending'   => 'Pendiente',
        'paid'      => 'Pagada',
        'cancelled' => 'Cancelada',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}