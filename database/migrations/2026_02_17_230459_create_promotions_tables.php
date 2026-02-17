<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y', 'bundle'])->default('percentage');
            $table->decimal('value', 15, 2)->default(0); // For percentage (e.g., 10 for 10%) or fixed amount
            $table->decimal('min_purchase', 15, 2)->default(0); // Minimum subtotal required
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percentage', 'fixed_amount'])->default('fixed_amount');
            $table->decimal('value', 15, 2);
            $table->integer('usage_limit')->nullable(); // Null = unlimited
            $table->integer('used_count')->default(0);
            $table->dateTime('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
