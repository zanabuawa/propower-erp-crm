<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->time('entry_time')->nullable()->after('work_shift');
            $table->time('exit_time')->nullable()->after('entry_time');
            // JSON array de números ISO (1=Lun … 6=Sáb, 7=Dom). Oficina:[1,2,3,4,5] | Campo:[1,2,3,4,5,6]
            $table->json('work_days')->nullable()->after('exit_time');
            // Horas a trabajar el sábado (0 = descanso)
            $table->decimal('saturday_hours', 4, 2)->default(0)->after('work_days');
            // Minutos de tolerancia antes de marcar tardanza
            $table->unsignedTinyInteger('tolerance_minutes')->default(10)->after('saturday_hours');
        });
    }

    public function down(): void
    {
        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->dropColumn(['entry_time', 'exit_time', 'work_days', 'saturday_hours', 'tolerance_minutes']);
        });
    }
};
