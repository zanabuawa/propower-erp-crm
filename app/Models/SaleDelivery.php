<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleDelivery extends Model
{
    protected $fillable = [
        'company_id', 'sale_order_id', 'customer_id', 'created_by',
        'warehouse_id', 'folio', 'status', 'notes', 'delivered_at',
    ];

    protected $casts = ['delivered_at' => 'datetime'];

    const STATUS = [
        'draft'     => 'Borrador',
        'delivered' => 'Entregada',
        'cancelled' => 'Cancelada',
    ];

    public function order(): BelongsTo { return $this->belongsTo(SaleOrder::class, 'sale_order_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function items(): HasMany { return $this->hasMany(SaleDeliveryItem::class); }
}