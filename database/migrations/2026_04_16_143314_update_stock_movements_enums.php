<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `type` ENUM('entry','exit','adjustment','transfer_in','transfer_out','transfer') NOT NULL");
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `status` ENUM('draft','confirmed','cancelled','requested','in_transit','completed','partially_received') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `type` ENUM('entry','exit','adjustment','transfer_in','transfer_out') NOT NULL");
            DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `status` ENUM('draft','confirmed','cancelled') NOT NULL DEFAULT 'draft'");
        }
    }
};
