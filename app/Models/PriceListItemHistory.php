<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItemHistory extends Model
{
    protected $fillable = [
        'product_id', 'price_list_id',
        'old_price', 'new_price',
        'old_discount_pct', 'new_discount_pct',
        'changed_by', 'changed_at',
    ];

    protected $casts = [
        'old_price'        => 'decimal:4',
        'new_price'        => 'decimal:4',
        'old_discount_pct' => 'decimal:2',
        'new_discount_pct' => 'decimal:2',
        'changed_at'       => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /** Variación absoluta del precio efectivo (con descuento) */
    public function getVariationAttribute(): float
    {
        $oldEff = $this->old_price !== null
            ? (float) $this->old_price * (1 - (float) $this->old_discount_pct / 100)
            : null;
        $newEff = (float) $this->new_price * (1 - (float) $this->new_discount_pct / 100);

        return $oldEff !== null ? round($newEff - $oldEff, 4) : 0;
    }

    /** Variación porcentual del precio efectivo */
    public function getVariationPctAttribute(): ?float
    {
        if ($this->old_price === null || (float) $this->old_price == 0) {
            return null;
        }
        $oldEff = (float) $this->old_price * (1 - (float) $this->old_discount_pct / 100);
        $newEff = (float) $this->new_price * (1 - (float) $this->new_discount_pct / 100);

        return $oldEff != 0 ? round((($newEff - $oldEff) / $oldEff) * 100, 2) : null;
    }
}
