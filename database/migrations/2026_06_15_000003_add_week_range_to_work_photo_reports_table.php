<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_photo_reports', function (Blueprint $table) {
            $table->date('week_start')->nullable()->after('report_date');
            $table->date('week_end')->nullable()->after('week_start');
        });
    }

    public function down(): void
    {
        Schema::table('work_photo_reports', function (Blueprint $table) {
            $table->dropColumn(['week_start', 'week_end']);
        });
    }
};
