<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo APU — categorías jerárquicas
        Schema::create('tender_catalog_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('tender_catalog_categories')->nullOnDelete();
            $table->string('code', 20)->nullable();
            $table->string('name');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'parent_id']);
        });

        // Catálogo APU — conceptos/ítems de trabajo
        Schema::create('tender_catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('tender_catalog_categories')->cascadeOnDelete();
            $table->string('code', 30)->nullable();
            $table->string('name');
            $table->string('unit', 20)->nullable();
            $table->text('description')->nullable();
            $table->decimal('indirect_pct', 5, 2)->default(0);
            $table->decimal('overhead_pct', 5, 2)->default(0);
            $table->decimal('utility_pct', 5, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'category_id']);
        });

        // Catálogo APU — insumos (APU desglosado)
        Schema::create('tender_catalog_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('tender_catalog_items')->cascadeOnDelete();
            $table->enum('type', ['material', 'labor', 'equipment'])->default('material');
            $table->string('description');
            $table->string('unit', 20)->nullable();
            $table->decimal('quantity', 12, 4)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->timestamps();
        });

        // Licitaciones
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('folio', 30)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['contrato_servicios', 'obra_publica', 'obra_privada', 'suministro', 'mixto'])->default('obra_privada');
            $table->enum('status', ['borrador', 'publicada', 'en_evaluacion', 'adjudicada', 'desierta', 'cancelada'])->default('borrador');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('submission_date')->nullable();
            $table->date('opening_date')->nullable();
            $table->date('award_date')->nullable();
            $table->decimal('estimated_budget', 16, 2)->nullable();
            $table->decimal('awarded_amount', 16, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
        });

        // Partidas de la licitación
        Schema::create('tender_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalog_item_id')->nullable()->constrained('tender_catalog_items')->nullOnDelete();
            $table->string('code', 30)->nullable();
            $table->string('category')->nullable();
            $table->string('description');
            $table->string('unit', 20)->nullable();
            $table->decimal('quantity', 14, 4)->default(0);
            $table->decimal('unit_price', 14, 4)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Cotizaciones multi-empresa
        Schema::create('tender_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issuing_company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('folio', 30)->nullable();
            $table->enum('status', ['borrador', 'enviada', 'aceptada', 'rechazada'])->default('borrador');
            $table->date('valid_until')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Ítems de cotización
        Schema::create('tender_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained('tender_quotations')->cascadeOnDelete();
            $table->foreignId('tender_item_id')->nullable()->constrained('tender_items')->nullOnDelete();
            $table->string('description');
            $table->string('unit', 20)->nullable();
            $table->decimal('quantity', 14, 4)->default(0);
            $table->decimal('unit_price', 14, 4)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_quotation_items');
        Schema::dropIfExists('tender_quotations');
        Schema::dropIfExists('tender_items');
        Schema::dropIfExists('tenders');
        Schema::dropIfExists('tender_catalog_resources');
        Schema::dropIfExists('tender_catalog_items');
        Schema::dropIfExists('tender_catalog_categories');
    }
};
