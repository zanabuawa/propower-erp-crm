<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add dispatched_quantity to items (what origin actually sends)
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->decimal('dispatched_quantity', 12, 4)->nullable()->after('quantity');
        });

        // Add dispatch response fields to movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->text('dispatch_notes')->nullable()->after('notes');
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete()->after('dispatch_notes');
            $table->boolean('dispatch_is_final')->default(false)->after('dispatched_by');
        });

        // Add 'rejected' to status ENUM
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `status` ENUM(
                'draft','confirmed','cancelled',
                'requested','in_transit','completed','partially_received',
                'rejected'
            ) NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        Schema::table('stock_movement_items', function (Blueprint $table) {
            $table->dropColumn('dispatched_quantity');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['dispatched_by']);
            $table->dropColumn(['dispatch_notes', 'dispatched_by', 'dispatch_is_final']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `status` ENUM(
                'draft','confirmed','cancelled',
                'requested','in_transit','completed','partially_received'
            ) NOT NULL DEFAULT 'draft'");
        }
    }
};
