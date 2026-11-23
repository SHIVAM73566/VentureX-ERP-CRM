<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('layer_entitlements', function (Blueprint $table) {
            $table->string('stripe_subscription_id', 128)->nullable()->after('layer_key');
            $table->string('stripe_customer_id', 128)->nullable()->after('stripe_subscription_id');
            $table->index(['stripe_subscription_id', 'status'], 'layer_ent_stripe_sub_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('layer_entitlements', function (Blueprint $table) {
            $table->dropIndex('layer_ent_stripe_sub_status_idx');
            $table->dropColumn(['stripe_subscription_id', 'stripe_customer_id']);
        });
    }
};
