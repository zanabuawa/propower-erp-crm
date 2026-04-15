<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            // ID interno de FacturAPI (para operaciones de descarga y cancelación vía API)
            $table->string('facturapi_id')->nullable()->after('cfdi_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('sale_invoices', function (Blueprint $table) {
            $table->dropColumn('facturapi_id');
        });
    }
};
