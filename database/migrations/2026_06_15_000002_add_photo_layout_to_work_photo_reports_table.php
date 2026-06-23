<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_photo_reports', function (Blueprint $table) {
            $table->json('photo_layout')->nullable()->after('photos');
        });
    }

    public function down(): void
    {
        Schema::table('work_photo_reports', function (Blueprint $table) {
            $table->dropColumn('photo_layout');
        });
    }
};
