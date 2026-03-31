<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('batch_number', 100);
            $table->date('expiry_date')->nullable();
            
            // Qty in this specific batch (base unit)
            $table->decimal('initial_quantity', 10, 2)->default(0);
            $table->decimal('current_quantity', 10, 2)->default(0);
            $table->decimal('cost_price', 15, 2)->default(0)->comment('Harga beli per unit untuk batch ini');
            
            $table->timestamps();

            // Indexes for performance
            $table->index(['product_id', 'expiry_date']);
            $table->index('batch_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
