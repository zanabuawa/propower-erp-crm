<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movement_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_movement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // action slugs: created | accepted_complete | accepted_partial | rejected
            //               sent | received_partial | received_complete | cancelled
            $table->string('action', 50);
            $table->text('notes')->nullable();
            $table->json('data')->nullable(); // items summary, quantities, etc.
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movement_events');
    }
};
