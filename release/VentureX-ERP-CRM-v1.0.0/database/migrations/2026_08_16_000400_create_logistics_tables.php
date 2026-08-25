<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('shipment_number', 32)->nullable();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained()->nullOnDelete();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('draft'); // draft | packed | shipped | in_transit | delivered | returned
            $table->date('ship_date')->nullable();
            $table->date('estimated_delivery')->nullable();
            $table->date('delivered_at')->nullable();
            $table->string('destination')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('container_number', 32)->nullable();
            $table->string('type', 32)->default('standard'); // standard | high_cube | reefer | open_top | flat_rack
            $table->string('size', 16)->nullable(); // 20ft | 40ft | 40ft HC | 45ft
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('empty'); // empty | loading | loaded | in_transit | at_port | delivered
            $table->decimal('capacity', 18, 4)->nullable();
            $table->decimal('weight', 18, 4)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });

        Schema::create('landed_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('container_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('freight', 18, 4)->default(0);
            $table->decimal('insurance', 18, 4)->default(0);
            $table->decimal('customs', 18, 4)->default(0);
            $table->decimal('handling', 18, 4)->default(0);
            $table->decimal('other', 18, 4)->default(0);
            $table->decimal('total', 18, 4)->default(0);
            $table->string('status')->default('draft'); // draft | allocated
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landed_costs');
        Schema::dropIfExists('containers');
        Schema::dropIfExists('shipments');
    }
};
