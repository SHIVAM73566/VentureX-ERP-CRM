<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enterprise RAG pipeline registry, document/vector store and query log.
     *
     * Embeddings are stored as portable JSON arrays so the same schema works
     * on SQLite, MySQL, PostgreSQL and MariaDB without a vector extension.
     */
    public function up(): void
    {
        Schema::create('rag_pipelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->string('key', 100)->unique();
            $table->string('name');
            $table->string('domain', 50);
            $table->text('description')->nullable();
            $table->json('source_models')->nullable();
            $table->string('retrieval_strategy', 50)->default('hybrid');
            $table->decimal('weight', 5, 4)->default(1);
            $table->decimal('confidence', 5, 4)->default(0.5);
            $table->boolean('is_active')->default(true);
            $table->json('config')->nullable();
            $table->unsignedInteger('document_count')->default(0);
            $table->timestamp('last_indexed_at')->nullable();
            $table->timestamp('last_trained_at')->nullable();
            $table->timestamps();

            $table->index(['domain', 'is_active']);
            $table->index('is_active');
        });

        Schema::create('rag_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('pipeline_id')->constrained('rag_pipelines')->cascadeOnDelete();
            $table->string('source_type', 100);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('title')->nullable();
            $table->longText('content');
            $table->json('embedding')->nullable();
            $table->unsignedInteger('embedding_dim')->nullable();
            $table->json('metadata')->nullable();
            $table->decimal('confidence', 5, 4)->default(0.5);
            $table->unsignedInteger('tokens')->default(0);
            $table->timestamps();

            $table->index(['pipeline_id', 'company_id']);
            $table->index(['source_type', 'source_id']);
            $table->index('company_id');
        });

        Schema::create('rag_query_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pipeline_id')->constrained('rag_pipelines')->cascadeOnDelete();
            $table->text('query');
            $table->string('mode', 20)->default('local');
            $table->decimal('top_score', 8, 6)->default(0);
            $table->unsignedInteger('result_count')->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->timestamps();

            $table->index(['pipeline_id', 'created_at']);
            $table->index(['company_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rag_query_logs');
        Schema::dropIfExists('rag_documents');
        Schema::dropIfExists('rag_pipelines');
    }
};
