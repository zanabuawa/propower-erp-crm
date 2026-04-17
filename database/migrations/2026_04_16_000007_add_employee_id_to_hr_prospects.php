<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->after('status')->constrained('hr_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
