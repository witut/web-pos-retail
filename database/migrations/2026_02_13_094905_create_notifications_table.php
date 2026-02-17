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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('type')->index(); // backup_failed, low_stock, shift_not_closed, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional context data
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            // Indexes for performance
            $table->index('created_at');
            $table->index(['user_id', 'read_at']); // For querying unread notifications per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
