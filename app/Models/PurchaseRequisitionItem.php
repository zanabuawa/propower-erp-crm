<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequisitionItem extends Model
{
    protected $fillable = [
        'purchase_requisition_id', 'product_id', 'item_type', 'description',
        'quantity', 'unit_price', 'unit', 'notes',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public const ITEM_TYPES = [
        'product' => 'Producto',
        'service' => 'Servicio',
        'tool'    => 'Herramienta',
        'asset'   => 'Activo fijo',
        'other'   => 'Otro',
    ];

    public const ITEM_TYPE_COLORS = [
        'product' => 'bg-blue-50 text-blue-600',
        'service' => 'bg-purple-50 text-purple-600',
        'tool'    => 'bg-amber-50 text-amber-600',
        'asset'   => 'bg-emerald-50 text-emerald-600',
        'other'   => 'bg-gray-100 text-gray-500',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}