<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stock extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'quantity', 'committed_quantity'];

    protected $casts = [
        'quantity'           => 'decimal:4',
        'committed_quantity' => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** Cantidad disponible para nueva venta o asignación */
    public function getAvailableQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - (float) $this->committed_quantity);
    }

    /** Reserva stock al confirmar una orden de venta */
    public function commit(float $qty): void
    {
        $this->increment('committed_quantity', $qty);
    }

    /** Libera reserva cuando se cancela una orden o se entrega */
    public function release(float $qty): void
    {
        $new = max(0, (float) $this->committed_quantity - $qty);
        $this->update(['committed_quantity' => $new]);
    }

    /** Libera reserva y descuenta stock al completar entrega */
    public function deliver(float $qty): void
    {
        $this->decrement('quantity', $qty);
        $this->release($qty);
    }
}
