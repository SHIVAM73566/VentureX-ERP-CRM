<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('support_tickets')->nullOnDelete();
            $table->string('error_hash')->index();
            $table->string('error_type');
            $table->text('error_message');
            $table->string('module')->nullable();
            $table->string('controller')->nullable();
            $table->string('action')->nullable();
            $table->string('route')->nullable();
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->text('stack_trace')->nullable();
            $table->string('http_method')->nullable();
            $table->integer('http_status')->nullable();
            $table->string('url')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('app_version')->nullable();
            $table->string('php_version')->nullable();
            $table->string('laravel_version')->nullable();
            $table->string('server_software')->nullable();
            $table->json('request_data')->nullable();
            $table->json('session_data')->nullable();
            $table->enum('status', ['new', 'investigating', 'confirmed', 'fixed', 'wont_fix', 'duplicate'])->default('new');
            $table->text('diagnosis')->nullable();
            $table->text('fix_applied')->nullable();
            $table->timestamp('fixed_at')->nullable();
            $table->integer('occurrence_count')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index(['error_hash', 'status']);
            $table->index(['company_id', 'status']);
            $table->index(['module', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_reports');
    }
};
