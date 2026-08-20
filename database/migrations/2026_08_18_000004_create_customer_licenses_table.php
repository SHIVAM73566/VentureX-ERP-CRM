<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('license_key')->unique();
            $table->enum('tier', ['starter', 'professional', 'enterprise'])->default('starter');
            $table->enum('status', ['active', 'expired', 'suspended', 'revoked'])->default('active');
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('domain')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('app_version')->nullable();
            $table->integer('max_users')->default(5);
            $table->integer('max_companies')->default(1);
            $table->json('features')->nullable();
            $table->timestamp('last_check_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('license_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_licenses');
    }
};
