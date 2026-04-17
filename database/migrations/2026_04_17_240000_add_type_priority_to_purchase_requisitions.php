<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->enum('requisition_type', ['material', 'service', 'tool', 'asset', 'mixed'])
                  ->default('material')->after('currency');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])
                  ->default('normal')->after('requisition_type');
            $table->enum('expense_type', ['operational', 'capital', 'maintenance', 'project', 'other'])
                  ->nullable()->after('priority');
            $table->string('project_name')->nullable()->after('expense_type');
        });

        Schema::table('purchase_requisition_items', function (Blueprint $table) {
            $table->enum('item_type', ['product', 'service', 'tool', 'asset', 'other'])
                  ->default('product')->after('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropColumn(['requisition_type', 'priority', 'expense_type', 'project_name']);
        });
        Schema::table('purchase_requisition_items', function (Blueprint $table) {
            $table->dropColumn('item_type');
        });
    }
};
