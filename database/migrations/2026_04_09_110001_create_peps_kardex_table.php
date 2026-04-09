<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peps_kardex', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('product_lots')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();

            // ── Tipo de movimiento ──────────────────────────────────────────
            // purchase, return_purchase, sale, return_sale,
            // adjustment_in, adjustment_out, transfer_in, transfer_out,
            // internal_use, scrap, other
            $table->string('movement_type', 30);
            $table->enum('direction', ['in', 'out']); // simplifica queries de saldo

            // ── Datos de cantidad ───────────────────────────────────────────
            $table->decimal('quantity', 12, 4);

            // ── Costos (PEPS) ───────────────────────────────────────────────
            $table->decimal('unit_cost', 12, 4)->default(0);   // precio de obtención
            $table->decimal('total_cost', 12, 2)->default(0);  // quantity * unit_cost

            // ── Precios de venta (null en entradas) ─────────────────────────
            $table->decimal('unit_price', 12, 4)->nullable();    // precio de venta
            $table->decimal('total_revenue', 12, 2)->nullable(); // quantity * unit_price

            // ── Utilidad (null en entradas) ─────────────────────────────────
            $table->decimal('profit', 12, 2)->nullable();        // total_revenue - total_cost
            $table->decimal('profit_pct', 8, 4)->nullable();     // profit / total_cost * 100

            // ── Saldo corrido en el momento del movimiento ──────────────────
            $table->decimal('balance_quantity', 12, 4)->default(0);
            $table->decimal('balance_value', 12, 2)->default(0); // saldo * unit_cost PEPS

            // ── Referencia y trazabilidad ───────────────────────────────────
            $table->string('reference', 255)->nullable(); // folio OC, OV, MOV, REM…
            $table->string('lot_number', 40)->nullable(); // desnormalizado para búsquedas rápidas
            $table->datetime('moved_at');
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'product_id', 'moved_at']);
            $table->index(['product_id', 'warehouse_id', 'moved_at']);
            $table->index(['lot_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peps_kardex');
    }
};
