<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique()->comment('Format: INV/YYYY/MM/XXXXX');
            $table->timestamp('transaction_date');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('restrict');

            // Financial fields - STRICT decimal typing
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);

            // Payment details
            $table->enum('payment_method', ['cash', 'debit_card', 'credit_card', 'qris', 'transfer']);
            $table->decimal('amount_paid', 15, 2);
            $table->decimal('change_amount', 15, 2)->default(0);

            // Status & Void tracking (PRD Section 7.2.2)
            $table->enum('status', ['completed', 'void'])->default('completed');
            $table->string('void_reason')->nullable()->comment('Customer Return, Payment Failed, Input Error, etc');
            $table->text('void_notes')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->timestamp('voided_at')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('invoice_number');
            $table->index('transaction_date');
            $table->index('cashier_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
