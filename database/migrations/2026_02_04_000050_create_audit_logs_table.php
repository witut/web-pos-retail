<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action_type', 50)->comment('VOID_TRANSACTION, PRICE_CHANGE, PIN_OVERRIDE, etc');
            $table->string('table_name', 50)->nullable();
            $table->string('record_id', 50)->nullable();
            $table->json('old_values')->nullable()->comment('Before state');
            $table->json('new_values')->nullable()->comment('After state');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Critical indexes for audit queries
            $table->index(['user_id', 'created_at']);
            $table->index(['table_name', 'record_id']);
            $table->index('action_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
