<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_evaluation_stages', function (Blueprint $table) {
            $table->json('guide_paths')->nullable()->after('guide_path');
        });
    }

    public function down(): void
    {
        Schema::table('hr_evaluation_stages', function (Blueprint $table) {
            $table->dropColumn('guide_paths');
        });
    }
};
