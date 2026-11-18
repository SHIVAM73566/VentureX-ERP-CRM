<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('install_state')) {
            return;
        }

        Schema::create('install_state', function (Blueprint $table) {
            $table->id();
            $table->boolean('installed')->default(false);
            $table->string('mode', 16)->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        DB::table('install_state')->insert([
            'id' => 1,
            'installed' => false,
            'mode' => null,
            'installed_at' => null,
            'meta' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('install_state');
    }
};
