<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('request_type', 64); // user, role, permission, export, purchase, payment, inventory, ai_quota, ai_advanced, security
            $table->string('resource_type', 64)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('risk_level', 16)->default('low'); // low, medium, high, critical
            $table->string('status', 24)->default('pending'); // pending, approved, rejected, expired, cancelled
            $table->integer('required_approvals')->default(1);
            $table->integer('current_approvals')->default(0);
            $table->foreignId('final_approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['requester_id', 'status']);
            $table->index(['request_type', 'status']);
        });

        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 16); // approve, reject
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['approval_request_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
    }
};
