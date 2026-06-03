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
        Schema::table('hr_evaluation_processes', function (Blueprint $table) {
            $table->foreignId('hr_employee_id')->nullable()->after('hr_prospect_id')->constrained('hr_employees')->onDelete('cascade');
            $table->foreignId('hr_prospect_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_evaluation_processes', function (Blueprint $table) {
            $table->dropForeign(['hr_employee_id']);
            $table->dropColumn('hr_employee_id');
            $table->foreignId('hr_prospect_id')->nullable(false)->change();
        });
    }
};
