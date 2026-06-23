<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->longText('print_custom_clauses')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->dropColumn('print_custom_clauses');
        });
    }
};
