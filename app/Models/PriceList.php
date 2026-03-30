<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'name',
        'currency',
        'is_default',
        'is_active',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function customers()
    {
        return $this->belongsToMany(Customer::class);
    }

    public function getPriceForProduct(int $productId): ?float
    {
        $item = $this->items->firstWhere('product_id', $productId);
        if (!$item)
            return null;
        return $item->price * (1 - $item->discount_pct / 100);
    }
}