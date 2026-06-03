<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hr_agenda_events')) {
            Schema::create('hr_agenda_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title');
                $table->string('type')->default('general');
                $table->dateTime('starts_at');
                $table->string('color', 7)->default('#2563eb');
                $table->text('description')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['company_id', 'starts_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_agenda_events');
    }
};
