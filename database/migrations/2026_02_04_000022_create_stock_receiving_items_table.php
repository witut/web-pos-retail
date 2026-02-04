<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_receiving_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_id')->constrained('stock_receiving')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->decimal('qty', 10, 2);
            $table->string('unit_name', 20)->comment('Unit used for receiving');
            $table->decimal('cost_per_unit', 15, 2)->comment('Purchase cost per unit');
            $table->decimal('subtotal', 15, 2)->comment('qty × cost_per_unit');
            $table->timestamps();

            $table->index('receiving_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_receiving_items');
    }
};
