<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Encrypted API key vault — keys stored encrypted, never exposed in plaintext
        Schema::create('api_key_vaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g., 'openai', 'nvidia', 'rapidapi'
            $table->string('key_hash', 64); // HMAC hash for lookup without decrypt
            $table->text('encrypted_key'); // Encrypted actual key
            $table->string('provider', 64)->nullable(); // swift, gemini, deepseek, nvidia, claude
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'key_hash']);
        });

        // Phone verification — OTP codes sent to user's phone
        Schema::create('phone_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20);
            $table->string('otp_code', 10);
            $table->string('otp_hash', 64); // HMAC hash for verification
            $table->string('status')->default('pending'); // pending, verified, expired, failed
            $table->integer('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['phone', 'status']);
        });

        // Selfie verification — face verification for identity
        Schema::create('selfie_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('selfie_path'); // Stored encrypted path
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->string('liveness_check')->default('pending'); // pending, passed, failed
            $table->json('metadata')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // App lock — PIN-based app lock for re-authentication
        Schema::create('app_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('pin_hash', 64); // Hashed PIN (never stored plaintext)
            $table->boolean('is_active')->default(false);
            $table->integer('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamp('last_unlocked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        // Device fingerprint — track trusted devices
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_fingerprint', 64); // SHA-256 hash of device info
            $table->string('device_name')->nullable(); // e.g., "Chrome on Windows"
            $table->string('ip_address', 45)->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'device_fingerprint']);
            $table->index(['user_id', 'is_trusted']);
        });

        // Login verification log — track all verification attempts
        Schema::create('login_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('verification_type'); // email_otp, phone_otp, selfie, pin, device
            $table->string('status'); // pending, success, failed
            $table->string('ip_address', 45);
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['email', 'verification_type']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_verifications');
        Schema::dropIfExists('trusted_devices');
        Schema::dropIfExists('app_locks');
        Schema::dropIfExists('selfie_verifications');
        Schema::dropIfExists('phone_verifications');
        Schema::dropIfExists('api_key_vaults');
    }
};
