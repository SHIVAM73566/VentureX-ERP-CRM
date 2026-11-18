<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'logo')) {
                $table->string('logo')->nullable()->after('tax_id');
            }
            if (! Schema::hasColumn('companies', 'currency_code')) {
                $table->string('currency_code', 8)->nullable()->after('country_id');
            }
            if (! Schema::hasColumn('companies', 'tax_id')) {
                $table->string('tax_id')->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('companies', 'logo')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('logo');
            });
        }
    }
};
