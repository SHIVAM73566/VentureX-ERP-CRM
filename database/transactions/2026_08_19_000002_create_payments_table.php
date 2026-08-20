<?php

namespace Database\Transactions;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('payment_id')->unique()->comment('PayPal order/payment ID');
            $table->string('payer_id')->nullable()->comment('PayPal payer ID');
            $table->string('plan_key')->comment('Plan key from config');
            $table->string('description')->nullable();
            $table->unsignedinteger('amount_cents')->comment('Amount in cents');
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('pending')->comment('pending, completed, failed, refunded, cancelled');
            $table->string('payment_method')->default('paypal');
            $table->json('metadata')->nullable()->comment('Additional payment data');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index('payment_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
