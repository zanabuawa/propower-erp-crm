<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_attendance_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meters')->default(100); // radio permitido
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        // Agregar location_id a hr_attendances para trazabilidad
        Schema::table('hr_attendances', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()
                ->after('device_id')
                ->constrained('hr_attendance_locations')->nullOnDelete();
            $table->decimal('checkin_latitude', 10, 7)->nullable()->after('check_in');
            $table->decimal('checkin_longitude', 10, 7)->nullable()->after('checkin_latitude');
            $table->boolean('location_valid')->nullable()->after('checkin_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('hr_attendances', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn(['location_id', 'checkin_latitude', 'checkin_longitude', 'location_valid']);
        });
        Schema::dropIfExists('hr_attendance_locations');
    }
};
