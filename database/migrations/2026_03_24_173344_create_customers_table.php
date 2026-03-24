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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['person', 'company'])->default('company');
            $table->string('name');
            $table->string('rfc', 13)->nullable();
            $table->string('tax_regime')->nullable();
            $table->date('birthdate')->nullable();
            $table->date('anniversary_date')->nullable();
            $table->string('image')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 100)->default('México');
            $table->string('zip_code', 10)->nullable();
            $table->string('website')->nullable();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->unsignedInteger('payment_terms')->default(0);
            $table->enum('status', ['active', 'inactive', 'prospect'])->default('prospect');
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
        Schema::dropIfExists('customers');
    }
};
