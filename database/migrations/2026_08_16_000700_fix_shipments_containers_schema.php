<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('container_id')->nullable()->after('purchase_order_id')->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->string('type', 16)->default('outbound')->after('supplier_id');
            $table->string('mode', 16)->nullable()->after('type');
            $table->string('origin', 255)->nullable()->after('tracking_number');
            $table->date('departure_date')->nullable()->after('ship_date');
            $table->date('arrival_date')->nullable()->after('departure_date');
            $table->timestamp('shipped_at')->nullable()->after('arrival_date');
            $table->decimal('weight', 18, 4)->nullable()->after('delivered_at');
            $table->decimal('volume', 18, 4)->nullable()->after('weight');
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->string('seal_number', 64)->nullable()->after('container_number');
            $table->decimal('capacity', 18, 4)->nullable()->change();
            $table->decimal('weight', 18, 4)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['container_id']);
            $table->dropColumn(['container_id', 'supplier_id', 'type', 'mode', 'origin', 'departure_date', 'arrival_date', 'shipped_at', 'weight', 'volume']);
        });

        Schema::table('containers', function (Blueprint $table) {
            $table->dropColumn('seal_number');
        });
    }
};
