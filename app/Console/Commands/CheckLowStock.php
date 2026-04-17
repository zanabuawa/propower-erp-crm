<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckLowStock extends Command
{
    protected $signature   = 'notify:low-stock';
    protected $description = 'Envía notificaciones de stock bajo, agotado y exceso a usuarios de cada empresa';

    public function handle(): void
    {
        $products = Product::query()
            ->where('is_active', true)
            ->where('type', '!=', 'service')
            ->with('stocks')
            ->get();

        foreach ($products as $product) {
            $total    = (float) $product->stocks->sum('quantity');
            $minStock = (float) $product->min_stock;
            $maxStock = (float) $product->max_stock;

            $isNoStock   = $total <= 0;
            $isLowStock  = !$isNoStock && $minStock > 0 && $total <= $minStock;
            $isOverStock = $maxStock > 0 && $total > $maxStock;

            foreach (['no_stock' => $isNoStock, 'low_stock' => $isLowStock, 'over_stock' => $isOverStock] as $type => $condition) {
                if (!$condition) continue;

                $alreadySent = DB::table('notifications')
                    ->where('type', LowStockNotification::class)
                    ->where('created_at', '>=', now()->subHours(24))
                    ->whereRaw("JSON_EXTRACT(data, '$.product_id') = ?", [$product->id])
                    ->whereRaw("JSON_EXTRACT(data, '$.type') = ?", [$type])
                    ->exists();

                if ($alreadySent) continue;

                [$title, $message] = match($type) {
                    'no_stock'   => [
                        "Stock agotado: {$product->name}",
                        "El producto \"{$product->name}\" no tiene existencia disponible.",
                    ],
                    'low_stock'  => [
                        "Stock bajo: {$product->name}",
                        "El producto \"{$product->name}\" tiene {$total} unidad(es), por debajo del mínimo de {$minStock}.",
                    ],
                    'over_stock' => [
                        "Exceso de stock: {$product->name}",
                        "El producto \"{$product->name}\" tiene {$total} unidad(es), por encima del máximo de {$maxStock}.",
                    ],
                    default => ['', ''],
                };

                $notification = new LowStockNotification(
                    title:       $title,
                    message:     $message,
                    type:        $type,
                    productId:   $product->id,
                    productName: $product->name,
                );

                User::where('company_id', $product->company_id)
                    ->permission('adjust inventory')
                    ->each(fn(User $user) => $user->notify(clone $notification));
            }
        }

        $this->info('Revisión de stock completada.');
    }
}
