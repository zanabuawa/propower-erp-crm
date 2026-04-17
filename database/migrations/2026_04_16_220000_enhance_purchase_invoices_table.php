<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            // Hacer la OC opcional (puede haber factura sin OC)
            $table->foreignId('created_by')->nullable()->after('supplier_id')
                ->constrained('users')->nullOnDelete();

            // Saldo pagado parcial y descuento
            $table->decimal('discount_amount', 12, 2)->default(0)->after('tax');
            $table->decimal('paid_amount', 12, 2)->default(0)->after('total');

            // Estado enriquecido para 3-way match
            $table->enum('match_status', ['pending', 'matched', 'discrepancy', 'approved'])
                ->default('pending')->after('status');

            // Campos de flujo de trabajo
            $table->text('notes')->nullable()->after('paid_at');
            $table->timestamp('received_at')->nullable()->after('issued_at');
        });

        // Hacer purchase_order_id nullable (facturas sin OC)
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'created_by', 'discount_amount', 'paid_amount',
                'match_status', 'notes', 'received_at',
            ]);
            $table->foreignId('purchase_order_id')->nullable(false)->change();
        });
    }
};
