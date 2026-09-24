<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AI usage log for the admin dashboard: who called what provider/model, at
     * what time, with result status, latency, tokens and estimated cost.
     * Never stores prompts, responses or API keys.
     */
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider', 50);
            $table->string('model')->nullable();
            $table->string('task', 50)->nullable();
            $table->string('request_type', 30)->nullable();
            $table->string('status', 20)->default('success');
            $table->integer('latency_ms')->nullable();
            $table->integer('prompt_tokens')->nullable();
            $table->integer('completion_tokens')->nullable();
            $table->decimal('cost', 12, 8)->nullable();
            $table->string('error_category', 50)->nullable();
            $table->string('error_message', 500)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['provider', 'status', 'created_at']);
            $table->index(['company_id', 'provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
