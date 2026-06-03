<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('hr_employees', 'country')) {
            Schema::table('hr_employees', function (Blueprint $table) {
                $table->string('country')->nullable()->after('state');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hr_employees', 'country')) {
            Schema::table('hr_employees', function (Blueprint $table) {
                $table->dropColumn('country');
            });
        }
    }
};
