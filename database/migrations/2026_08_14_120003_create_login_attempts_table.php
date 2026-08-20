<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('email', 191)->index();
            $table->string('ip', 64)->index();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_fingerprint', 128)->nullable()->index();
            $table->boolean('success')->default(false)->index();
            $table->string('reason', 64)->nullable();
            $table->string('correlation_id', 64)->nullable();
            $table->timestamps();
            $table->index(['email', 'success', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
