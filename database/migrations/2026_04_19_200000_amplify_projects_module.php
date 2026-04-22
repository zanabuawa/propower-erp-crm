<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliar projects: nuevos tipos + pedido de venta + referencia contrato
        DB::statement("ALTER TABLE projects MODIFY COLUMN type ENUM('interno','externo','licitacion','mantenimiento','instalacion','servicio') NOT NULL DEFAULT 'externo'");

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('sale_order_id')->nullable()->after('customer_id')
                  ->constrained('sale_orders')->nullOnDelete();
            $table->string('contract_reference', 100)->nullable()->after('sale_order_id');
        });

        // 2. Agregar start_date a project_tasks
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('parent_task_id');
        });

        // 3. Versiones de presupuesto
        Schema::create('project_budget_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->enum('status', ['borrador', 'aprobado', 'vigente', 'historico'])->default('borrador');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'version']);
        });

        // 4. Líneas de presupuesto por concepto
        Schema::create('project_budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('version_id')->constrained('project_budget_versions')->cascadeOnDelete();
            $table->enum('category', ['material', 'mano_obra', 'subcontrato', 'viaticos', 'indirectos', 'otros']);
            $table->string('concept', 255);
            $table->text('description')->nullable();
            $table->string('unit', 30)->nullable();
            $table->decimal('quantity', 12, 4)->default(1);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('budgeted_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // 5. Materiales y equipos asignados al proyecto
        Schema::create('project_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('name', 255);
            $table->enum('resource_type', ['material', 'equipo', 'herramienta', 'otro'])->default('material');
            $table->string('unit', 30)->nullable();
            $table->decimal('quantity_needed', 12, 4)->default(1);
            $table->decimal('quantity_used', 12, 4)->default(0);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->enum('status', ['pendiente', 'solicitado', 'adquirido', 'utilizado', 'devuelto'])->default('pendiente');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_materials');
        Schema::dropIfExists('project_budget_lines');
        Schema::dropIfExists('project_budget_versions');

        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn('start_date');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['sale_order_id']);
            $table->dropColumn(['sale_order_id', 'contract_reference']);
        });

        DB::statement("ALTER TABLE projects MODIFY COLUMN type ENUM('interno','externo','licitacion') NOT NULL DEFAULT 'externo'");
    }
};
