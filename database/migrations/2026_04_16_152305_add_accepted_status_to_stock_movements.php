<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'accepted' between requested and in_transit
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `status` ENUM(
            'draft','confirmed','cancelled',
            'requested','accepted','in_transit','completed','partially_received',
            'rejected'
        ) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN `status` ENUM(
            'draft','confirmed','cancelled',
            'requested','in_transit','completed','partially_received',
            'rejected'
        ) NOT NULL DEFAULT 'draft'");
    }
};
