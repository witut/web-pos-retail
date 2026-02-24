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
        Schema::table('promotions', function (Blueprint $table) {
            $table->integer('buy_qty')->nullable()->after('value')->comment('Quantity to buy for buy_x_get_y');
            $table->integer('get_qty')->nullable()->after('buy_qty')->comment('Quantity received free for buy_x_get_y');
            $table->foreignId('reward_product_id')->nullable()->after('get_qty')->constrained('products')->nullOnDelete()->comment('Optional different reward product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropForeign(['reward_product_id']);
            $table->dropColumn(['buy_qty', 'get_qty', 'reward_product_id']);
        });
    }
};
