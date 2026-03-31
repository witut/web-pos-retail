<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Tracking Type: default, batch, serial
            $table->enum('tracking_type', ['default', 'batch', 'serial'])->default('default')->after('base_unit');
            
            // Batas Pajang (untuk bakery): hari sebelum ED harus ditarik
            $table->smallInteger('display_limit_days')->default(0)->after('tracking_type');
            
            $table->index('tracking_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['tracking_type', 'display_limit_days']);
        });
    }
};
