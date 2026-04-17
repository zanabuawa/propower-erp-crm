<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseInvoiceItem extends Model
{
    protected $fillable = [
        'purchase_invoice_id', 'purchase_order_item_id', 'product_id',
        'description', 'quantity', 'unit_price', 'tax_rate', 'subtotal',
        'qty_ordered', 'qty_received', 'qty_rejected', 'price_ordered',
        'match_status', 'variance_notes',
    ];

    protected $casts = [
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
        'tax_rate'      => 'decimal:2',
        'subtotal'      => 'decimal:2',
        'qty_ordered'   => 'decimal:2',
        'qty_received'  => 'decimal:2',
        'qty_rejected'  => 'decimal:2',
        'price_ordered' => 'decimal:2',
    ];

    public const MATCH_STATUS = [
        'unmatched'          => 'Sin OC',
        'matched'            => 'Coincide',
        'qty_variance'       => 'Varianza cantidad',
        'price_variance'     => 'Varianza precio',
        'no_receipt'         => 'Sin recepción',
        'over_invoiced'      => 'Sobre-facturado',
        'rejection_variance' => 'Rechazos pendientes',
    ];

    public const MATCH_COLORS = [
        'unmatched'          => 'bg-gray-100 text-gray-500',
        'matched'            => 'bg-green-100 text-green-700',
        'qty_variance'       => 'bg-yellow-100 text-yellow-700',
        'price_variance'     => 'bg-orange-100 text-orange-700',
        'no_receipt'         => 'bg-red-100 text-red-600',
        'over_invoiced'      => 'bg-red-200 text-red-800',
        'rejection_variance' => 'bg-purple-100 text-purple-700',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
