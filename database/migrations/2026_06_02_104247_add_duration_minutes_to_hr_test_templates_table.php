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
        Schema::table('hr_test_templates', function (Blueprint $table) {
            $table->integer('duration_minutes')->nullable()->after('passing_score')->comment('Time limit in minutes. Null means no limit.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_test_templates', function (Blueprint $table) {
            $table->dropColumn('duration_minutes');
        });
    }
};
