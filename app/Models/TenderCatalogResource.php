<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderCatalogResource extends Model
{
    const TYPES = [
        'material'  => 'Material',
        'labor'     => 'Mano de obra',
        'equipment' => 'Equipo',
    ];

    protected $fillable = ['item_id', 'type', 'description', 'unit', 'quantity', 'unit_cost'];

    protected $casts = [
        'quantity'  => 'float',
        'unit_cost' => 'float',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(TenderCatalogItem::class, 'item_id');
    }

    public function getSubtotalAttribute(): float
    {
        return round($this->quantity * $this->unit_cost, 4);
    }
}
