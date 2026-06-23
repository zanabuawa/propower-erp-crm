<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('hr_attendances')
            ->whereNotNull('worked_hours')
            ->where('worked_hours', '<', 0)
            ->update([
                'worked_hours' => DB::raw('ABS(worked_hours)'),
            ]);

        DB::table('hr_attendances')
            ->whereNotNull('overtime_hours')
            ->where('overtime_hours', '<', 0)
            ->update([
                'overtime_hours' => 0,
            ]);
    }

    public function down(): void
    {
        //
    }
};
