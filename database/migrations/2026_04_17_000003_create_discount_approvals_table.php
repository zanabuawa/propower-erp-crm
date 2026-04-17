<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            // Modelo polimórfico: sale_quotation o sale_order
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_type', 'model_id']);
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->decimal('requested_discount_pct', 6, 2); // descuento global solicitado
            $table->decimal('max_allowed_pct', 6, 2);        // máximo sin aprobación
            $table->text('requester_notes')->nullable();
            $table->text('approver_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        // Agregar campos de aprobación a cotizaciones y órdenes
        Schema::table('sale_quotations', function (Blueprint $table) {
            $table->string('approval_status')->nullable()->after('status'); // null | pending | approved | rejected
            $table->unsignedBigInteger('approval_id')->nullable()->after('approval_status');
        });

        Schema::table('sale_orders', function (Blueprint $table) {
            $table->string('approval_status')->nullable()->after('status');
            $table->unsignedBigInteger('approval_id')->nullable()->after('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('sale_quotations', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approval_id']);
        });
        Schema::table('sale_orders', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approval_id']);
        });
        Schema::dropIfExists('discount_approvals');
    }
};
