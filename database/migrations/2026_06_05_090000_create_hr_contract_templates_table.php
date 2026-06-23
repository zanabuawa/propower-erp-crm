<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_contract_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->text('description')->nullable();
            $table->string('contract_type')->default('temporal');
            $table->unsignedSmallInteger('duration_months')->nullable();
            $table->string('work_shift')->default('campo');
            $table->unsignedSmallInteger('work_hours_per_week')->default(48);
            $table->json('work_days')->nullable();
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();
            $table->decimal('saturday_hours', 5, 2)->default(0);
            $table->json('benefits')->nullable();
            $table->longText('print_custom_clauses')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active']);
        });

        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->foreignId('hr_contract_template_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('hr_contract_templates')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hr_contracts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hr_contract_template_id');
        });

        Schema::dropIfExists('hr_contract_templates');
    }
};
