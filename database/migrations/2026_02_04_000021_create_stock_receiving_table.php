<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_receiving', function (Blueprint $table) {
            $table->id();
            $table->string('receiving_number', 30)->unique()->comment('Format: RCV/YYYY/MM/XXXXX');
            $table->foreignId('supplier_id')->constrained()->onDelete('restrict');
            $table->string('invoice_number', 50)->nullable()->comment('Supplier invoice/DO number');
            $table->date('receiving_date');
            $table->decimal('total_cost', 15, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('receiving_number');
            $table->index('receiving_date');
            $table->index('supplier_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_receiving');
    }
};
