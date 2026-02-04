<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->id();
            $table->string('opname_number', 30)->unique()->comment('Format: OPN/YYYY/MM/XXXXX');
            $table->date('opname_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('opname_number');
            $table->index('opname_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname');
    }
};
