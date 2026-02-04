<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 20)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('brand', 100)->nullable();
            $table->string('base_unit', 20)->comment('pcs, kg, liter, box, dozen, etc');

            // CRITICAL: Use decimal(15,2) for money, NOT float/double
            $table->decimal('selling_price', 15, 2)->comment('Default selling price');
            $table->decimal('cost_price', 15, 2)->default(0)->comment('HPP - Auto-calculated weighted average');

            // CRITICAL: Use decimal(10,2) for qty to support kg/liter
            $table->decimal('stock_on_hand', 10, 2)->default(0)->comment('Current stock in base unit');
            $table->decimal('min_stock_alert', 10, 2)->default(10)->comment('Low stock threshold');

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('image_path')->nullable();
            $table->decimal('tax_rate', 5, 2)->nullable()->comment('Product-specific tax rate, overrides global');

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Indexes for performance
            $table->index('sku');
            $table->index('status');
            $table->index('category_id');
            $table->index('stock_on_hand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
