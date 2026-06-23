<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_contract_templates', function (Blueprint $table) {
            $table->json('print_pages')->nullable()->after('print_custom_clauses');
        });

        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->json('print_pages')->nullable()->after('print_custom_clauses');
        });
    }

    public function down(): void
    {
        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->dropColumn('print_pages');
        });

        Schema::table('hr_contract_templates', function (Blueprint $table) {
            $table->dropColumn('print_pages');
        });
    }
};
