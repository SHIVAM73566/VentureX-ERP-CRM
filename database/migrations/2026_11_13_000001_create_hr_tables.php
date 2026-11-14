<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Complete HR suite: employee directory, attendance, leave, payroll,
     * performance reviews and automated onboarding.
     *
     * String columns are used instead of native enums so the same migration
     * runs on SQLite, MySQL, MariaDB and PostgreSQL without alteration.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('employee_number', 32);
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('job_title')->nullable();
            $table->string('employment_type', 32)->default('full_time');
            $table->string('status', 32)->default('active');
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 24)->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 40)->nullable();
            $table->decimal('base_salary', 18, 4)->default(0);
            $table->string('currency_code', 8)->default('INR');
            $table->string('pay_frequency', 24)->default('monthly');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'employee_number']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'department_id']);
            $table->index(['company_id', 'last_name', 'first_name']);
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('attendance_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status', 24)->default('present');
            $table->decimal('worked_hours', 8, 2)->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['employee_id', 'attendance_date']);
            $table->index(['company_id', 'attendance_date', 'status']);
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32)->default('annual');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days', 8, 2)->default(1);
            $table->boolean('half_day')->default(false);
            $table->text('reason')->nullable();
            $table->string('status', 24)->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('decision_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'employee_id', 'start_date']);
        });

        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('reference', 40);
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 24)->default('draft');
            $table->decimal('gross_total', 18, 4)->default(0);
            $table->decimal('deductions_total', 18, 4)->default(0);
            $table->decimal('net_total', 18, 4)->default(0);
            $table->unsignedInteger('employee_count')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'reference']);
            $table->index(['company_id', 'status', 'period_start']);
        });

        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('base_salary', 18, 4)->default(0);
            $table->decimal('allowances', 18, 4)->default(0);
            $table->decimal('overtime', 18, 4)->default(0);
            $table->decimal('bonus', 18, 4)->default(0);
            $table->decimal('gross', 18, 4)->default(0);
            $table->decimal('tax', 18, 4)->default(0);
            $table->decimal('social_security', 18, 4)->default(0);
            $table->decimal('other_deductions', 18, 4)->default(0);
            $table->decimal('deductions', 18, 4)->default(0);
            $table->decimal('net', 18, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
            $table->index(['employee_id']);
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('period', 40);
            $table->date('review_date');
            $table->decimal('overall_score', 5, 2)->default(0);
            $table->decimal('goals_score', 5, 2)->default(0);
            $table->decimal('competency_score', 5, 2)->default(0);
            $table->string('status', 24)->default('draft');
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('goals')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'employee_id', 'review_date']);
            $table->index(['company_id', 'status']);
        });

        Schema::create('onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category', 40)->default('general');
            $table->date('due_date')->nullable();
            $table->string('status', 24)->default('pending');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['employee_id', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_tasks');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_runs');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('employees');
    }
};
