<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Logo para impresiones en la empresa
        Schema::table('companies', function (Blueprint $table) {
            $table->string('print_logo')->nullable()->after('icon');
        });

        // Código interno del proveedor (número de proveedor en el sistema del cliente)
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('internal_code', 60)->nullable()->after('name');
        });

        // Campos adicionales en órdenes de compra
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('shipping_address')->nullable()->after('notes');
            $table->string('billing_address')->nullable()->after('shipping_address');
            $table->date('required_at')->nullable()->after('expected_at');
            $table->enum('print_language', ['es', 'en'])->default('es')->after('billing_address');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('print_logo');
        });
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('internal_code');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_address', 'billing_address', 'required_at', 'print_language']);
        });
    }
};
