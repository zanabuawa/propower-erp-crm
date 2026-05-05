<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE finance_transactions MODIFY COLUMN category
            ENUM('venta','compra','nomina','impuesto','prestamo','inversion','proyecto','viatico','otro')
            NOT NULL DEFAULT 'otro'");

        DB::statement("ALTER TABLE finance_cashflow MODIFY COLUMN category
            ENUM('operacion','inversion','financiamiento','viatico')
            NOT NULL DEFAULT 'operacion'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE finance_transactions MODIFY COLUMN category
            ENUM('venta','compra','nomina','impuesto','prestamo','inversion','proyecto','otro')
            NOT NULL DEFAULT 'otro'");

        DB::statement("ALTER TABLE finance_cashflow MODIFY COLUMN category
            ENUM('operacion','inversion','financiamiento')
            NOT NULL DEFAULT 'operacion'");
    }
};
