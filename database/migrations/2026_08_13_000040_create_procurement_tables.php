<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_email')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('material_category', 128)->nullable();
            $table->string('material_description', 512)->nullable();
            $table->string('grade', 64)->nullable();
            $table->string('isri_grade', 32)->nullable();
            $table->decimal('quantity_mt', 18, 4)->nullable();
            $table->decimal('price_per_mt', 18, 4)->nullable();
            $table->string('currency_code', 8)->nullable();
            $table->string('delivery_location', 255)->nullable();
            $table->string('payment_terms', 255)->nullable();
            $table->string('loading_terms', 255)->nullable();
            $table->decimal('cu_percent', 10, 4)->nullable();
            $table->decimal('fe_percent', 10, 4)->nullable();
            $table->decimal('ni_percent', 10, 4)->nullable();
            $table->decimal('cr_percent', 10, 4)->nullable();
            $table->decimal('pb_percent', 10, 4)->nullable();
            $table->decimal('zn_percent', 10, 4)->nullable();
            $table->decimal('al_percent', 10, 4)->nullable();
            $table->decimal('mn_percent', 10, 4)->nullable();
            $table->decimal('mo_percent', 10, 4)->nullable();
            $table->string('other_elements', 255)->nullable();
            $table->string('coa_number', 128)->nullable();
            $table->string('spectro_report_number', 128)->nullable();
            $table->boolean('coa_available')->default(false);
            $table->date('offer_date')->nullable();
            $table->date('validity_date')->nullable();
            $table->string('grade_match', 32)->nullable(); // match | partial | mismatch
            $table->string('quality_status', 16)->nullable(); // GREEN | YELLOW | RED
            $table->string('risk_level', 16)->nullable(); // low | medium | high
            $table->decimal('estimated_metal_value', 18, 4)->nullable();
            $table->string('buyer_action', 64)->nullable(); // review | hold | reject | contact
            $table->json('ai_analysis')->nullable();
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'quality_status']);
            $table->index(['company_id', 'material_category']);
            $table->index(['supplier_id']);
            $table->index(['offer_date']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('company_id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->string('first_name', 64)->nullable()->after('name');
            $table->string('last_name', 64)->nullable()->after('first_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('job_title', 128)->nullable()->after('phone');
            $table->string('timezone', 64)->default('UTC')->after('job_title');
            $table->string('locale', 8)->default('en')->after('timezone');
            $table->boolean('is_active')->default(true)->after('locale');
            $table->string('theme', 16)->default('light')->after('is_active');
            $table->date('last_login_at')->nullable()->after('theme');
            $table->softDeletes()->after('updated_at');

            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('branch_id');
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn([
                'first_name', 'last_name', 'phone', 'job_title',
                'timezone', 'locale', 'is_active', 'theme', 'last_login_at', 'deleted_at',
            ]);
        });
        Schema::dropIfExists('supplier_offers');
    }
};
