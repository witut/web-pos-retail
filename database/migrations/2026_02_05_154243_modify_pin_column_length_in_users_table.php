<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * 
     * Increase PIN column length to store bcrypt hashes (60 chars)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Change pin column from 6 chars to 60 chars for bcrypt hash
            $table->string('pin', 60)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 6)->nullable()->change();
        });
    }
};
