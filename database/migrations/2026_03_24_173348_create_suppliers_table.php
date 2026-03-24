<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['person', 'company'])->default('company');
            $table->string('name');
            $table->string('rfc', 13)->nullable();
            $table->string('tax_regime')->nullable();
            $table->string('image')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->default('México');
            $table->string('zip_code', 10)->nullable();
            $table->string('website')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->unsignedInteger('payment_terms')->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_clabe', 18)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
