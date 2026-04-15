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
    protected $description = 'Envía notificaciones de stock bajo o agotado a los usuarios de cada empresa';

    public function handle(): void
    {
        // Productos activos con stock definido (min_stock > 0 o con stock total = 0)
        $products = Product::query()
            ->where('is_active', true)
            ->where('type', '!=', 'service')   // servicios no tienen stock físico
            ->with('stocks')
            ->get();

        foreach ($products as $product) {
            $total    = (float) $product->stocks->sum('quantity');
            $minStock = (float) $product->min_stock;

            $isNoStock  = $total <= 0;
            $isLowStock = !$isNoStock && $minStock > 0 && $total <= $minStock;

            if (!$isNoStock && !$isLowStock) {
                continue;
            }

            $type = $isNoStock ? 'no_stock' : 'low_stock';

            // Evitar duplicados: no reenviar si ya existe una notif del mismo tipo
            // para este producto en las últimas 24 horas
            $alreadySent = DB::table('notifications')
                ->where('type', LowStockNotification::class)
                ->where('created_at', '>=', now()->subHours(24))
                ->whereRaw("JSON_EXTRACT(data, '$.product_id') = ?", [$product->id])
                ->whereRaw("JSON_EXTRACT(data, '$.type') = ?", [$type])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            [$title, $message] = $isNoStock
                ? [
                    "Stock agotado: {$product->name}",
                    "El producto \"{$product->name}\" no tiene existencia disponible.",
                ]
                : [
                    "Stock bajo: {$product->name}",
                    "El producto \"{$product->name}\" tiene {$total} unidad(es), por debajo del mínimo de {$minStock}.",
                ];

            $notification = new LowStockNotification(
                title:       $title,
                message:     $message,
                type:        $type,
                productId:   $product->id,
                productName: $product->name,
            );

            // Notificar solo a usuarios con permiso de ajustar inventario (almacenista, comprador, gerente, admin)
            User::where('company_id', $product->company_id)
                ->permission('adjust inventory')
                ->each(fn(User $user) => $user->notify(clone $notification));
        }

        $this->info('Revisión de stock completada.');
    }
}
