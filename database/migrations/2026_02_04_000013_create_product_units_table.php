<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('unit_name', 20)->comment('Box, Karton, Lusin, etc');
            $table->decimal('conversion_rate', 10, 2)->comment('How many base units in this unit (e.g., 12 for Box)');
            $table->decimal('selling_price', 15, 2)->comment('Selling price for this unit');
            $table->boolean('is_base_unit')->default(false)->comment('TRUE for base unit (pcs/kg/liter)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
