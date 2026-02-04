<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->enum('movement_type', ['IN', 'OUT', 'ADJUSTMENT', 'RETURN'])->comment('Stock movement direction');
            $table->enum('reference_type', ['SALE', 'RECEIVING', 'OPNAME', 'VOID'])->comment('Source of movement');
            $table->string('reference_id', 50)->comment('Transaction ID or Receiving ID');
            $table->decimal('qty', 10, 2)->comment('Can be positive or negative');
            $table->string('unit_name', 20);
            $table->decimal('cost_price', 15, 2)->comment('HPP at time of movement');
            $table->decimal('stock_before', 10, 2)->comment('Stock before this movement');
            $table->decimal('stock_after', 10, 2)->comment('Stock after this movement');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            // Critical indexes for kartu stok queries
            $table->index(['product_id', 'created_at']);
            $table->index('reference_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
