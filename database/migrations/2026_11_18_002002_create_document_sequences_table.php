<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_sequences')) {
            return;
        }

        Schema::create('document_sequences', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('company_id')->default(0);
            $table->string('type', 128);
            $table->unsignedBigInteger('current_value')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'type'], 'idx_ent_document_sequences_company_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
