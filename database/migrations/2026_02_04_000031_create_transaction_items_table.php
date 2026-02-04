<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->string('product_name', 200)->comment('Snapshot for history - in case product deleted');
            $table->string('unit_name', 20)->comment('Unit used at time of sale');
            $table->decimal('qty', 10, 2);
            $table->decimal('unit_price', 15, 2)->comment('Selling price at time of sale');
            $table->decimal('subtotal', 15, 2)->comment('qty × unit_price');
            $table->decimal('cost_price', 15, 2)->comment('HPP at time of sale - for profit calculation');
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
