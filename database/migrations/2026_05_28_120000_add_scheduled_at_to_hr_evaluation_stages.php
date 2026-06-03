<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_evaluation_stages', function (Blueprint $table) {
            $table->dateTime('scheduled_at')->nullable()->after('order');
        });

        if (! Schema::hasColumn('hr_test_attempts', 'completed_at')) {
            Schema::table('hr_test_attempts', function (Blueprint $table) {
                $table->timestamp('completed_at')->nullable()->after('submitted_at');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE hr_prospect_tests MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
            DB::statement("ALTER TABLE hr_test_attempts MODIFY status VARCHAR(50) NOT NULL DEFAULT 'in_progress'");
        }
    }

    public function down(): void
    {
        Schema::table('hr_evaluation_stages', function (Blueprint $table) {
            $table->dropColumn('scheduled_at');
        });

        if (Schema::hasColumn('hr_test_attempts', 'completed_at')) {
            Schema::table('hr_test_attempts', function (Blueprint $table) {
                $table->dropColumn('completed_at');
            });
        }
    }
};
