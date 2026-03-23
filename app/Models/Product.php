<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id', 'category_id', 'unit_of_measure_id',
        'name', 'sku', 'barcode', 'description',
        'purchase_price', 'sale_price', 'min_stock', 'max_stock', 'is_active',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'purchase_price' => 'decimal:2',
        'sale_price'     => 'decimal:2',
        'min_stock'      => 'decimal:2',
        'max_stock'      => 'decimal:2',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getTotalStockAttribute(): float
    {
        return $this->stocks->sum('quantity');
    }
}