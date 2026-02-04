<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_opname_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('opname_id')->constrained('stock_opname')->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->decimal('system_stock', 10, 2)->comment('Stock per system');
            $table->decimal('physical_stock', 10, 2)->comment('Actual counted stock');
            $table->decimal('variance', 10, 2)->comment('physical - system');
            $table->decimal('variance_value', 15, 2)->comment('variance × HPP');
            $table->text('notes')->nullable()->comment('Mandatory if variance significant');
            $table->timestamps();

            $table->index('opname_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_items');
    }
};
