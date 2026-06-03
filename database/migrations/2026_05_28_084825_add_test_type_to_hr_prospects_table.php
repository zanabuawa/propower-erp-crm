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
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->string('test_type')->nullable()->after('status'); // 'segurista', 'supervisor', etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->dropColumn('test_type');
        });
    }
};
