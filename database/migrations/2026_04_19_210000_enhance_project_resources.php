<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar project_employees: costo/hora + personal externo
        Schema::table('project_employees', function (Blueprint $table) {
            $table->string('external_name', 200)->nullable()->after('employee_id')
                  ->comment('Nombre si es contratista externo sin registro en RRHH');
            $table->decimal('cost_per_hour', 10, 4)->nullable()->after('hours_assigned')
                  ->comment('Tarifa hora usada para cálculo de costo en el proyecto');
        });

        // 2. Añadir project_id a purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('purchase_requisition_id')
                  ->constrained('projects')->nullOnDelete();
        });

        // 3. Añadir project_id a purchase_requisitions
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('branch_id')
                  ->constrained('projects')->nullOnDelete();
        });

        // 4. Añadir project_id a stock_movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')
                  ->constrained('projects')->nullOnDelete();
        });

        // 5. project_materials: añadir FK formal a products
        Schema::table('project_materials', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
            $table->foreignId('purchase_requisition_id')->nullable()->after('task_id')
                  ->constrained('purchase_requisitions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_materials', function (Blueprint $table) {
            $table->dropForeign(['purchase_requisition_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['purchase_requisition_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::table('project_employees', function (Blueprint $table) {
            $table->dropColumn(['external_name', 'cost_per_hour']);
        });
    }
};
