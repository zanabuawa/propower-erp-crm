<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('purpose', 60)->default('approve_quotation');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID del objeto a autorizar');
            $table->string('code_hash', 64)->comment('SHA-256 del código de 6 dígitos');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'purpose', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_otp_codes');
    }
};
