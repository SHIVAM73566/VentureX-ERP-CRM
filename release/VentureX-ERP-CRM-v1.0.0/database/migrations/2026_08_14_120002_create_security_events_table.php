<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_events', function (Blueprint $table) {
            $table->id();
            $table->string('correlation_id', 64)->index();
            $table->string('severity', 16)->index();
            $table->string('category', 48)->index();
            $table->string('event', 64)->index();
            $table->string('title', 255);
            $table->text('details')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('status', 24)->default('open')->index();
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution')->nullable();
            $table->timestamps();

            $table->index(['severity', 'status']);
            $table->index(['category', 'event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_events');
    }
};
