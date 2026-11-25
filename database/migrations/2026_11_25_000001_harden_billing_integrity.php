<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Billing integrity hardening:
 * - remember when a Stripe event happened so older events cannot undo newer ones
 * - expire stale processing leases so one crashed delivery cannot drop an event
 * - enforce one entitlement row per tenant/add-on so revocation cannot be bypassed
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('billing_events')) {
            Schema::table('billing_events', function (Blueprint $table) {
                if (! Schema::hasColumn('billing_events', 'event_created_at')) {
                    $table->timestamp('event_created_at')->nullable()->after('event_type');
                }
                if (! Schema::hasColumn('billing_events', 'claimed_at')) {
                    $table->timestamp('claimed_at')->nullable()->after('status');
                }
            });

            try {
                Schema::table('billing_events', function (Blueprint $table) {
                    $table->index(['status', 'claimed_at'], 'billing_events_status_claimed_idx');
                });
            } catch (Throwable) {
                // Index may already exist from an earlier partial run.
            }
        }

        if (Schema::hasTable('layer_entitlements')) {
            Schema::table('layer_entitlements', function (Blueprint $table) {
                if (! Schema::hasColumn('layer_entitlements', 'last_event_created_at')) {
                    $table->timestamp('last_event_created_at')->nullable()->after('expires_at');
                }
            });

            $this->deduplicateEntitlements();

            foreach (['layer_entitlements_company_id_layer_key_index', 'layer_entitlements_company_id_layer_key_foreign'] as $index) {
                try {
                    Schema::table('layer_entitlements', function (Blueprint $table) use ($index) {
                        $table->dropIndex($index);
                    });
                } catch (Throwable) {
                    // Nothing to drop on this installation.
                }
            }

            try {
                Schema::table('layer_entitlements', function (Blueprint $table) {
                    $table->unique(['company_id', 'layer_key'], 'layer_ent_company_layer_uniq');
                });
            } catch (Throwable) {
                // Constraint may already exist from an earlier partial run.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('layer_entitlements')) {
            try {
                Schema::table('layer_entitlements', function (Blueprint $table) {
                    $table->dropUnique('layer_ent_company_layer_uniq');
                });
            } catch (Throwable) {
                // Nothing to drop.
            }

            try {
                Schema::table('layer_entitlements', function (Blueprint $table) {
                    $table->index(['company_id', 'layer_key'], 'layer_entitlements_company_id_layer_key_index');
                });
            } catch (Throwable) {
                // Nothing to restore.
            }

            if (Schema::hasColumn('layer_entitlements', 'last_event_created_at')) {
                Schema::table('layer_entitlements', function (Blueprint $table) {
                    $table->dropColumn('last_event_created_at');
                });
            }
        }

        if (Schema::hasTable('billing_events')) {
            try {
                Schema::table('billing_events', function (Blueprint $table) {
                    $table->dropIndex('billing_events_status_claimed_idx');
                });
            } catch (Throwable) {
                // Nothing to drop.
            }

            Schema::table('billing_events', function (Blueprint $table) {
                if (Schema::hasColumn('billing_events', 'claimed_at')) {
                    $table->dropColumn('claimed_at');
                }
                if (Schema::hasColumn('billing_events', 'event_created_at')) {
                    $table->dropColumn('event_created_at');
                }
            });
        }
    }

    protected function deduplicateEntitlements(): void
    {
        if (! Schema::hasColumn('layer_entitlements', 'company_id')
            || ! Schema::hasColumn('layer_entitlements', 'layer_key')) {
            return;
        }

        $duplicates = DB::table('layer_entitlements')
            ->select('company_id', 'layer_key', DB::raw('MAX(id) as keep_id'))
            ->groupBy('company_id', 'layer_key')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('layer_entitlements')
                ->where('company_id', $duplicate->company_id)
                ->where('layer_key', $duplicate->layer_key)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }
    }
};
