<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('title');
            $table->text('description');
            $table->text('changelog')->nullable();
            $table->enum('type', ['patch', 'minor', 'major'])->default('patch');
            $table->string('min_php_version')->nullable();
            $table->string('min_laravel_version')->nullable();
            $table->string('download_url')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->timestamp('released_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_updates');
    }
};
