<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('serial_number', 100)->unique();
            
            // Status SN: available, sold, returned, service, reserved
            $table->enum('status', ['available', 'sold', 'returned', 'service', 'reserved'])->default('available');
            
            // Opsional: link ke transaction_item_id jika sudah terjual
            $table->foreignId('transaction_item_id')->nullable()->constrained('transaction_items');
            
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index('serial_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};
