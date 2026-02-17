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
        Schema::create('customer_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('points'); // Positive for earn, negative for redeem
            $table->enum('type', ['earn', 'redeem', 'adjustment', 'expire'])->default('earn');
            $table->string('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at');

            // Indexes
            $table->index('customer_id');
            $table->index('type');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_points');
    }
};
