<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create purchases (master)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 50)->unique(); // PUR/YYYY/MM/XXXXX
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            
            $table->date('purchase_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('debt_amount', 15, 2)->default(0)->comment('Total - Paid');
            
            // Status Pembayaran: paid, partial, unpaid
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            
            // Status Pembelian: ordered, received, cancelled
            $table->enum('status', ['ordered', 'received', 'cancelled'])->default('ordered');
            
            $table->date('due_date')->nullable()->comment('Tanggal jatuh tempo hutang');
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('purchase_number');
            $table->index('supplier_id');
            $table->index('payment_status');
            $table->index('status');
        });

        // 2. Create purchase_items (detail)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->decimal('qty', 10, 2);
            $table->string('unit_name', 20);
            $table->decimal('cost_per_unit', 15, 2);
            $table->decimal('subtotal', 15, 2);
            
            $table->timestamps();

            $table->index('purchase_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
