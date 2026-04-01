<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReceipt extends Model
{
    const RECEPTION_TYPES = [
        'purchase'  => 'Compra',
        'return'    => 'Devolución',
        'transfer'  => 'Transferencia de otro almacén',
        'defective' => 'Defectuoso',
    ];

    protected $fillable = [
        'company_id', 'purchase_order_id', 'received_by',
        'warehouse_id', 'folio', 'status', 'reception_type',
        'operating_expenses', 'notes', 'received_at',
    ];

    protected $casts = [
        'received_at'        => 'datetime',
        'operating_expenses' => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseReceiptItem::class);
    }
}