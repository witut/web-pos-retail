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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->after('cashier_id')->constrained()->onDelete('set null');
            $table->integer('points_earned')->default(0)->after('customer_id');
            $table->integer('points_redeemed')->default(0)->after('points_earned');
            $table->decimal('points_discount_amount', 15, 2)->default(0)->after('points_redeemed');

            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'points_earned', 'points_redeemed', 'points_discount_amount']);
        });
    }
};
