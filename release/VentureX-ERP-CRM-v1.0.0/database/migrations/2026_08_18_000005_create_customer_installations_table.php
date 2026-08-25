<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_installations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('installation_id')->unique();
            $table->string('app_version');
            $table->string('php_version')->nullable();
            $table->string('laravel_version')->nullable();
            $table->string('mysql_version')->nullable();
            $table->string('server_os')->nullable();
            $table->string('server_software')->nullable();
            $table->string('domain')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'abandoned'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('app_version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_installations');
    }
};
