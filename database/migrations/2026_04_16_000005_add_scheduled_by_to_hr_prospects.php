<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->foreignId('scheduled_by_id')->nullable()->after('interviewer_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->dropForeign(['scheduled_by_id']);
            $table->dropColumn('scheduled_by_id');
        });
    }
};
