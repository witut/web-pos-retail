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
        Schema::table('product_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('product_returns', 'authorized_by_id')) {
                $table->foreignId('authorized_by_id')->nullable()->constrained('users')->nullOnDelete()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_returns', function (Blueprint $table) {
            $table->dropForeign(['authorized_by_id']);
            $table->dropColumn('authorized_by_id');
        });
    }
};
