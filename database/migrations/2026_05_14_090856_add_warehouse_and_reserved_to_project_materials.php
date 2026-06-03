<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_materials', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->after('product_id')
                  ->constrained('warehouses')->nullOnDelete();
            $table->decimal('quantity_reserved', 14, 4)->default(0)->after('quantity_needed');
        });
    }

    public function down(): void
    {
        Schema::table('project_materials', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn(['warehouse_id', 'quantity_reserved']);
        });
    }
};
