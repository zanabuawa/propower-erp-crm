<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->decimal('received_quantity', 12, 4)->nullable()->after('quantity');
            $table->timestamp('received_at')->nullable()->after('received_quantity');
            $table->boolean('is_late_addition')->default(false)->after('received_at');
            $table->timestamp('added_at')->nullable()->after('is_late_addition');
        });

        // Transfers need extended statuses — add requested, in_transit, partially_received
        // These are handled at model level via constants, no schema change needed
    }

    public function down(): void
    {
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->dropColumn(['received_quantity', 'received_at', 'is_late_addition', 'added_at']);
        });
    }
};
