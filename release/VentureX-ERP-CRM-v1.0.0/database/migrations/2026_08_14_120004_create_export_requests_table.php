<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_requests', function (Blueprint $table) {
            $table->id();
            $table->string('export_id', 32)->unique();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('department', 64)->nullable();
            $table->string('data_type', 64)->index();
            $table->json('record_ids')->nullable();
            $table->integer('record_count')->default(0);
            $table->string('reason', 500)->nullable();
            $table->string('format', 16)->default('csv');
            $table->string('sensitivity', 24)->default('internal');
            $table->string('status', 24)->default('pending')->index();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_downloads')->default(1);
            $table->integer('download_count')->default(0);
            $table->string('storage_path', 500)->nullable();
            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_requests');
    }
};
