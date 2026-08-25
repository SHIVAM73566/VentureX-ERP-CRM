<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landed_costs', function (Blueprint $table) {
            $table->dropColumn(['freight', 'insurance', 'customs', 'handling', 'other', 'total']);

            $table->string('cost_type')->default('freight')->after('purchase_order_id');
            $table->string('description')->nullable()->after('cost_type');
            $table->decimal('amount', 18, 4)->default(0)->after('description');
            $table->string('currency', 8)->nullable()->default('INR')->after('amount');
            $table->string('paid_to')->nullable()->after('currency');
            $table->timestamp('paid_at')->nullable()->after('paid_to');

            $table->index('cost_type');
        });
    }

    public function down(): void
    {
        Schema::table('landed_costs', function (Blueprint $table) {
            $table->dropColumn(['cost_type', 'description', 'amount', 'currency', 'paid_to', 'paid_at']);

            $table->decimal('freight', 18, 4)->default(0)->after('purchase_order_id');
            $table->decimal('insurance', 18, 4)->default(0)->after('freight');
            $table->decimal('customs', 18, 4)->default(0)->after('insurance');
            $table->decimal('handling', 18, 4)->default(0)->after('customs');
            $table->decimal('other', 18, 4)->default(0)->after('handling');
            $table->decimal('total', 18, 4)->default(0)->after('other');
        });
    }
};
