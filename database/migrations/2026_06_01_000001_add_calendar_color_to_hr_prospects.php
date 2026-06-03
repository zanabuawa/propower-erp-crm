<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('hr_prospects', 'calendar_color')) {
            Schema::table('hr_prospects', function (Blueprint $table) {
                $table->string('calendar_color', 7)->nullable()->after('interview_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hr_prospects', 'calendar_color')) {
            Schema::table('hr_prospects', function (Blueprint $table) {
                $table->dropColumn('calendar_color');
            });
        }
    }
};
