<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseReceiptItem extends Model
{
    protected $fillable = [
        'purchase_receipt_id', 'purchase_order_item_id', 'product_id',
        'warehouse_id', 'quantity_received', 'notes',
        'quantity_rejected', 'rejection_reason', 'rejected_by', 'rejected_at',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:2',
        'quantity_rejected' => 'decimal:2',
        'rejected_at'       => 'datetime',
    ];

    const REJECTION_REASONS = [
        'damaged'        => 'Producto dañado',
        'qty_mismatch'   => 'Cantidad incorrecta',
        'quality_issue'  => 'Problema de calidad',
        'wrong_product'  => 'Producto incorrecto',
        'expired'        => 'Producto vencido / caducado',
        'other'          => 'Otro motivo',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(PurchaseReceipt::class, 'purchase_receipt_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function hasRejection(): bool
    {
        return (float) $this->quantity_rejected > 0;
    }
}
