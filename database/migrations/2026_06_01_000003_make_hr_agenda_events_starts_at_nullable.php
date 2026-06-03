<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('hr_agenda_events', 'starts_at')) {
            DB::statement('ALTER TABLE hr_agenda_events MODIFY starts_at DATETIME NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('hr_agenda_events', 'starts_at')) {
            DB::table('hr_agenda_events')->whereNull('starts_at')->update(['starts_at' => now()]);
            DB::statement('ALTER TABLE hr_agenda_events MODIFY starts_at DATETIME NOT NULL');
        }
    }
};
