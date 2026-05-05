<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained('hr_employees')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->foreignId('finance_transaction_id')->nullable()->constrained('finance_transactions')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('folio', 20)->unique();
            $table->string('destination');
            $table->string('purpose');
            $table->date('departure_date');
            $table->date('return_date');
            $table->enum('status', ['borrador', 'aprobado', 'pagado', 'comprobado', 'rechazado'])->default('borrador');
            $table->decimal('amount_approved', 12, 2)->default(0);
            $table->decimal('amount_spent', 12, 2)->nullable();
            $table->string('currency', 3)->default('MXN');
            $table->boolean('trip_confirmed')->default(false);
            $table->timestamp('trip_confirmed_at')->nullable();
            $table->foreignId('trip_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['employee_id', 'status']);
        });

        Schema::create('travel_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_expense_id')->constrained()->cascadeOnDelete();
            $table->enum('category', [
                'hospedaje', 'transporte', 'alimentacion',
                'viaticos_diarios', 'combustible', 'peajes', 'otro',
            ])->default('otro');
            $table->string('concept');
            $table->decimal('amount', 12, 2);
            $table->string('receipt_number', 60)->nullable();
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_expense_items');
        Schema::dropIfExists('travel_expenses');
    }
};
