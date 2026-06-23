<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_program_activities', function (Blueprint $table) {
            $table->date('actual_start_date')->nullable()->after('end_date');
            $table->date('actual_end_date')->nullable()->after('actual_start_date');
            $table->text('actual_notes')->nullable()->after('actual_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('work_program_activities', function (Blueprint $table) {
            $table->dropColumn(['actual_start_date', 'actual_end_date', 'actual_notes']);
        });
    }
};
