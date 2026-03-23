<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::table('stock_movements', function (Blueprint $table) {
        $table->dropForeign(['product_id']);
        $table->dropForeign(['warehouse_id']);
        $table->dropColumn(['product_id', 'warehouse_id', 'quantity', 'quantity_before', 'quantity_after']);
    });

    Schema::table('stock_movements', function (Blueprint $table) {
        $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete()->after('company_id');
        $table->foreignId('warehouse_destination_id')->nullable()->constrained('warehouses')->nullOnDelete()->after('warehouse_id');
        $table->string('folio')->nullable()->after('type');
        $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft')->after('folio');
    });
}

public function down(): void
{
    Schema::table('stock_movements', function (Blueprint $table) {
        $table->dropForeign(['warehouse_id']);
        $table->dropForeign(['warehouse_destination_id']);
        $table->dropColumn(['warehouse_id', 'warehouse_destination_id', 'folio', 'status']);
        $table->foreignId('product_id')->constrained()->cascadeOnDelete();
        $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
        $table->decimal('quantity', 10, 2);
        $table->decimal('quantity_before', 10, 2)->default(0);
        $table->decimal('quantity_after', 10, 2)->default(0);
    });
}
};
