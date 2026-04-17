<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fixed_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('loaned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('loaned_to_name')->nullable(); // para cuadrillas o externos sin usuario
            $table->foreignId('created_by')->constrained('users');
            $table->string('folio')->unique();
            $table->date('loan_date');
            $table->date('expected_return_date')->nullable();
            $table->date('actual_return_date')->nullable();
            $table->string('condition_on_loan')->default('good'); // good, fair, damaged
            $table->string('condition_on_return')->nullable(); // good, fair, damaged, lost
            $table->string('status')->default('active'); // active, returned, lost, damaged
            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();
            $table->text('return_notes')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_loans');
    }
};
