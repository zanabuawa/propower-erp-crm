<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_invoice_id')->constrained()->cascadeOnDelete();
            // Vinculo con la línea de la OC (para 3-way match)
            $table->foreignId('purchase_order_item_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()
                ->constrained()->nullOnDelete();

            // Datos de la partida tal como aparecen en la factura del proveedor
            $table->string('description');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(16);
            $table->decimal('subtotal', 12, 2)->default(0);

            // Valores de referencia cacheados de la OC y GRN para comparación rápida
            $table->decimal('qty_ordered', 10, 2)->nullable();
            $table->decimal('qty_received', 10, 2)->nullable();
            $table->decimal('price_ordered', 12, 2)->nullable();

            // Resultado de la validación línea por línea
            $table->enum('match_status', [
                'unmatched',      // Sin OC vinculada
                'matched',        // Coincide perfectamente
                'qty_variance',   // Cantidad facturada ≠ cantidad recibida
                'price_variance', // Precio facturado ≠ precio de OC
                'no_receipt',     // No hay recepción de mercancía
                'over_invoiced',  // Cantidad facturada > cantidad recibida
            ])->default('unmatched');

            $table->text('variance_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_invoice_items');
    }
};
