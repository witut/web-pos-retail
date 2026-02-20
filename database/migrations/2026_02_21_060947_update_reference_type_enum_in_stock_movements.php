<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to include 'RETURN'
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN reference_type ENUM('SALE', 'RECEIVING', 'OPNAME', 'VOID', 'RETURN') COMMENT 'Source of movement'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN reference_type ENUM('SALE', 'RECEIVING', 'OPNAME', 'VOID') COMMENT 'Source of movement'");
    }
};
