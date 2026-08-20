<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('scan_status', 32)->default('pending'); // pending | clean | infected | error
            $table->text('scan_result')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->boolean('is_quarantined')->default(false);

            $table->index(['scan_status', 'is_quarantined']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['scan_status', 'is_quarantined']);
            $table->dropColumn(['scan_status', 'scan_result', 'scanned_at', 'is_quarantined']);
        });
    }
};
