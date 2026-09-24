<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Company-scoped AI provider credentials.
     *
     * API keys are stored ENCRYPTED (never plaintext). The encrypted value is
     * only decrypted server-side at request time by AiProviderManager and is
     * never sent to the browser, JavaScript, HTML, API responses or logs.
     */
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('provider', 50);
            $table->text('encrypted_key')->nullable();
            $table->string('key_hash', 64)->nullable();
            $table->string('model')->nullable();
            $table->string('base_url')->nullable();
            $table->string('path')->nullable();
            $table->string('auth_mode', 20)->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('fallback_enabled')->default(true);
            $table->integer('per_user_daily_limit')->nullable();
            $table->integer('per_user_monthly_limit')->nullable();
            $table->integer('org_daily_limit')->nullable();
            $table->integer('org_monthly_limit')->nullable();
            $table->integer('request_count')->default(0);
            $table->integer('error_count')->default(0);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->string('last_error', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'provider']);
            $table->index(['company_id', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};