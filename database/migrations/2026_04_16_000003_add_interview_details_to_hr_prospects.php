<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->string('interview_type')->nullable()->after('interview_date'); // presencial, virtual
            $table->foreignId('interviewer_id')->nullable()->after('interview_type')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_prospects', function (Blueprint $table) {
            $table->dropForeign(['interviewer_id']);
            $table->dropColumn(['interview_type', 'interviewer_id']);
        });
    }
};
