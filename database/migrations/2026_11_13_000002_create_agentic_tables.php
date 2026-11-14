<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AERO X Agentic Engine: executable business rules and an immutable run
     * ledger. Every run is transactional; failures are rolled back and kept
     * for self-training telemetry.
     */
    public function up(): void
    {
        Schema::create('agent_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key', 80);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category', 40)->default('operations');
            $table->string('trigger', 60)->default('scheduled');
            $table->boolean('is_enabled')->default(true);
            $table->boolean('requires_approval')->default(false);
            $table->unsignedInteger('cooldown_minutes')->default(60);
            $table->json('config')->nullable();
            $table->decimal('confidence', 5, 4)->default(0.8000);
            $table->unsignedInteger('run_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->text('last_result')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'key']);
            $table->index(['company_id', 'is_enabled']);
        });

        Schema::create('agent_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('agent_action_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action_key', 80);
            $table->string('trigger', 60)->default('scheduled');
            $table->string('status', 24)->default('running');
            $table->boolean('dry_run')->default(false);
            $table->text('summary')->nullable();
            $table->json('details')->nullable();
            $table->decimal('duration_ms', 12, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status', 'created_at']);
            $table->index(['action_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_runs');
        Schema::dropIfExists('agent_actions');
    }
};
