<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Update transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')->constrained('product_batches');
            $table->foreignId('product_serial_id')->nullable()->after('batch_id')->constrained('product_serials');
        });

        // 2. Update stock_movements
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')->constrained('product_batches');
            $table->foreignId('product_serial_id')->nullable()->after('batch_id')->constrained('product_serials');
        });

        // Add 'PURCHASE' to reference_type enum
        DB::statement("ALTER TABLE stock_movements MODIFY COLUMN reference_type ENUM('SALE', 'RECEIVING', 'OPNAME', 'VOID', 'RETURN', 'PURCHASE') COMMENT 'Source of movement'");
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['product_serial_id']);
            $table->dropColumn(['batch_id', 'product_serial_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['product_serial_id']);
            $table->dropColumn(['batch_id', 'product_serial_id']);
        });
    }
};
