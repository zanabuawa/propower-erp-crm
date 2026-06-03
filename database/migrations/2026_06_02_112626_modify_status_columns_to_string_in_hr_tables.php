<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE hr_evaluation_stages MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE hr_evaluation_processes MODIFY status VARCHAR(50) NOT NULL DEFAULT 'active'");
        } else {
            Schema::table('hr_evaluation_stages', function (Blueprint $table) {
                $table->string('status', 50)->default('pending')->change();
            });
            Schema::table('hr_evaluation_processes', function (Blueprint $table) {
                $table->string('status', 50)->default('active')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Enums are hard to restore exactly without knowing the original set, 
        // but let's try to be consistent with original migrations if possible.
    }
};
