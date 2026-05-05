<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenderItem extends Model
{
    protected $fillable = [
        'tender_id', 'catalog_item_id', 'product_id', 'code', 'category',
        'description', 'unit', 'quantity', 'unit_price', 'total', 'sort_order',
    ];

    protected $casts = [
        'quantity'   => 'float',
        'unit_price' => 'float',
        'total'      => 'float',
    ];

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(TenderCatalogItem::class, 'catalog_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
