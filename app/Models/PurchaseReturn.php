<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'company_id', 'purchase_order_id', 'supplier_id',
        'created_by', 'folio', 'status', 'reason', 'total',
    ];

    protected $casts = ['total' => 'decimal:2'];

    const STATUS = [
        'draft'     => 'Borrador',
        'sent'      => 'Enviada',
        'confirmed' => 'Confirmada',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}