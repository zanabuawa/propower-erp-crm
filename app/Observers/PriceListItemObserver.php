<?php

namespace App\Observers;

use App\Models\PriceListItemHistory;
use App\Models\PriceListItem;

class PriceListItemObserver
{
    public function created(PriceListItem $item): void
    {
        PriceListItemHistory::create([
            'product_id'       => $item->product_id,
            'price_list_id'    => $item->price_list_id,
            'old_price'        => null,
            'new_price'        => $item->price,
            'old_discount_pct' => 0,
            'new_discount_pct' => $item->discount_pct ?? 0,
            'changed_by'       => auth()->id() ?? 1,
            'changed_at'       => now(),
        ]);
    }

    public function updated(PriceListItem $item): void
    {
        if (! $item->wasChanged(['price', 'discount_pct'])) {
            return;
        }

        PriceListItemHistory::create([
            'product_id'       => $item->product_id,
            'price_list_id'    => $item->price_list_id,
            'old_price'        => $item->getOriginal('price'),
            'new_price'        => $item->price,
            'old_discount_pct' => $item->getOriginal('discount_pct') ?? 0,
            'new_discount_pct' => $item->discount_pct ?? 0,
            'changed_by'       => auth()->id() ?? 1,
            'changed_at'       => now(),
        ]);
    }
}
