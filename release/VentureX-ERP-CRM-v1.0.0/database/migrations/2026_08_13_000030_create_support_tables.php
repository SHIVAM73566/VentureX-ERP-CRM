<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('supplier_code', 32)->nullable();
            $table->string('tax_id')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency_code', 8)->nullable();
            $table->string('status')->default('pending'); // pending | verified | approved | rejected | blocked
            $table->string('payment_terms', 128)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type', 64);
            $table->morphs('documentable'); // customer, supplier, lead, shipment, employee...
            $table->string('title');
            $table->string('original_name');
            $table->string('storage_path');
            $table->string('mime_type', 128)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('status')->default('new'); // new | processing | reviewed | verified | rejected
            $table->json('extracted_data')->nullable();
            $table->json('ai_analysis')->nullable();
            $table->date('expires_at')->nullable();
            $table->json('tags')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'type', 'status']);
            $table->index(['expires_at']);
        });

        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version');
            $table->string('original_name');
            $table->string('storage_path');
            $table->unsignedBigInteger('size')->default(0);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('change_note')->nullable();
            $table->timestamps();

            $table->unique(['document_id', 'version']);
        });

        Schema::create('ai_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('provider', 32)->default('nvidia');
            $table->string('model')->nullable();
            $table->decimal('temperature', 4, 2)->default(0.4);
            $table->decimal('top_p', 4, 2)->default(0.9);
            $table->unsignedInteger('max_tokens')->default(2048);
            $table->longText('instructions');
            $table->json('input_schema')->nullable();
            $table->json('output_schema')->nullable();
            $table->json('safety_rules')->nullable();
            $table->json('activation_rules')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'is_active']);
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('skill_slug')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'updated_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->string('role', 16); // user | assistant | system
            $table->longText('content');
            $table->json('metadata')->nullable(); // sources, fact/calculation/assumption/recommendation tags
            $table->timestamps();
        });

        Schema::create('ai_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('skill_slug')->nullable();
            $table->string('provider', 32)->nullable();
            $table->string('model')->nullable();
            $table->string('status', 32)->default('pending'); // pending | running | completed | failed | cancelled
            $table->string('input_type', 32)->nullable(); // document | email | manual | chat
            $table->json('input')->nullable();
            $table->json('output')->nullable();
            $table->json('error')->nullable();
            $table->unsignedInteger('prompt_tokens')->default(0);
            $table->unsignedInteger('completion_tokens')->default(0);
            $table->decimal('cost', 12, 6)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['skill_slug']);
        });

        Schema::create('automation_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('trigger_type', 64);
            $table->json('trigger_config')->nullable();
            $table->json('actions');
            $table->string('status')->default('active'); // active | paused | archived
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('automation_workflows')->cascadeOnDelete();
            $table->string('status', 32)->default('pending');
            $table->json('context')->nullable();
            $table->json('results')->nullable();
            $table->json('error')->nullable();
            $table->timestamp('ran_at')->nullable();
            $table->timestamps();

            $table->index(['workflow_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_workflows');
        Schema::dropIfExists('ai_runs');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('ai_skills');
        Schema::dropIfExists('document_versions');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('suppliers');
    }
};
