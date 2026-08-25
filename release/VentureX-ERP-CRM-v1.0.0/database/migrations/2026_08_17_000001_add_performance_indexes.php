<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // invoices: 3-col composite for AR aging queries (company+status exists, adding due_date)
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->index(['company_id', 'status', 'due_date'], 'idx_invoices_company_status_due_date');
            });
        }

        // audit_logs: company_id + created_at for date-range queries
        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->index(['company_id', 'created_at'], 'idx_audit_logs_company_created_at');
            });
        }

        // security_events: company_id + created_at + severity for dashboard queries
        if (Schema::hasTable('security_events')) {
            Schema::table('security_events', function (Blueprint $table) {
                $table->index(['company_id', 'created_at', 'severity'], 'idx_security_events_company_created_severity');
            });
        }

        // activities: company_id + status + due_at for activity timeline
        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->index(['company_id', 'status', 'due_at'], 'idx_activities_company_status_due_at');
            });
        }

        // payments: company_id + payment_date for date-range payment queries
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index(['company_id', 'payment_date'], 'idx_payments_company_payment_date');
            });
        }

        // export_requests: company_id + status for filtered list views
        if (Schema::hasTable('export_requests')) {
            Schema::table('export_requests', function (Blueprint $table) {
                $table->index(['company_id', 'status'], 'idx_export_requests_company_status');
            });
        }

        // documents: company_id + type + documentable_type + documentable_id for polymorphic lookups
        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->index(['company_id', 'type', 'documentable_type', 'documentable_id'], 'idx_documents_company_type_morphable');
            });
        }

        // approval_requests: company_id + status + created_at for dashboard queries
        if (Schema::hasTable('approval_requests')) {
            Schema::table('approval_requests', function (Blueprint $table) {
                $table->index(['company_id', 'status', 'created_at'], 'idx_approval_requests_company_status_created');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('approval_requests')) {
            Schema::table('approval_requests', function (Blueprint $table) {
                $table->dropIndex('idx_approval_requests_company_status_created');
            });
        }

        if (Schema::hasTable('documents')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropIndex('idx_documents_company_type_morphable');
            });
        }

        if (Schema::hasTable('export_requests')) {
            Schema::table('export_requests', function (Blueprint $table) {
                $table->dropIndex('idx_export_requests_company_status');
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex('idx_payments_company_payment_date');
            });
        }

        if (Schema::hasTable('activities')) {
            Schema::table('activities', function (Blueprint $table) {
                $table->dropIndex('idx_activities_company_status_due_at');
            });
        }

        if (Schema::hasTable('security_events')) {
            Schema::table('security_events', function (Blueprint $table) {
                $table->dropIndex('idx_security_events_company_created_severity');
            });
        }

        if (Schema::hasTable('audit_logs')) {
            Schema::table('audit_logs', function (Blueprint $table) {
                $table->dropIndex('idx_audit_logs_company_created_at');
            });
        }

        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('idx_invoices_company_status_due_date');
            });
        }
    }
};
