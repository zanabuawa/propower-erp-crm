<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use App\Notifications\IncompleteProductNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckIncompleteProducts extends Command
{
    protected $signature   = 'notify:incomplete-products';
    protected $description = 'Notifica productos/servicios activos con campos obligatorios incompletos';

    /**
     * Campos requeridos para emitir CFDI y operar correctamente.
     * Clave => etiqueta legible para el mensaje.
     */
    private const REQUIRED_FIELDS = [
        'category_id'        => 'categoría',
        'unit_of_measure_id' => 'unidad de medida',
        'sat_product_code'   => 'clave SAT de producto/servicio',
        'sat_unit_code'      => 'clave SAT de unidad',
        'description'        => 'descripción',
    ];

    public function handle(): void
    {
        $products = Product::query()
            ->where('is_active', true)
            ->with('primaryImage')
            ->get();

        foreach ($products as $product) {
            $missing = [];

            foreach (self::REQUIRED_FIELDS as $field => $label) {
                if (empty($product->{$field})) {
                    $missing[] = $label;
                }
            }

            // Imagen principal faltante (aplica a productos y servicios)
            if (!$product->primaryImage) {
                $missing[] = 'imagen';
            }

            // Solo productos físicos: precio de obtención mayor a 0
            if ($product->type === 'product' && (float) $product->purchase_price <= 0) {
                $missing[] = 'precio de obtención';
            }

            if (empty($missing)) {
                continue;
            }

            $label = $product->type === 'service' ? 'servicio' : 'producto';

            // Evitar duplicados: no reenviar si ya se notificó en los últimos 7 días
            $alreadySent = DB::table('notifications')
                ->where('type', IncompleteProductNotification::class)
                ->where('created_at', '>=', now()->subDays(7))
                ->whereRaw("JSON_EXTRACT(data, '$.product_id') = ?", [$product->id])
                ->exists();

            if ($alreadySent) {
                continue;
            }

            $missingList = implode(', ', $missing);

            $notification = new IncompleteProductNotification(
                title:       "Datos incompletos: {$product->name}",
                message:     "El {$label} \"{$product->name}\" le falta: {$missingList}.",
                productId:   $product->id,
                productName: $product->name,
                productType: $product->type,
            );

            // Notificar a usuarios con permiso de editar inventario
            User::where('company_id', $product->company_id)
                ->whereNull('deleted_at')
                ->permission('edit inventory')
                ->each(fn(User $user) => $user->notify(clone $notification));
        }

        $this->info('Revisión de datos incompletos completada.');
    }
}
