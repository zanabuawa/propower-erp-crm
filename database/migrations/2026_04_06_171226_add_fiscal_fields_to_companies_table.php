<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('fiscal_regime', 10)->nullable()->after('rfc');
            $table->string('fiscal_postal_code', 5)->nullable()->after('fiscal_regime');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['fiscal_regime', 'fiscal_postal_code']);
        });
    }
};
