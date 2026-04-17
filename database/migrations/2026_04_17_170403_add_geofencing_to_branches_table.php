<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('latitude', 10, 8)->nullable()->after('state');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            $table->integer('allowed_radius')->default(200)->after('longitude')->comment('Allowed radius in meters for attendance');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'allowed_radius']);
        });
    }
};
