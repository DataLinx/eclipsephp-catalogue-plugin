<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pim_products', function (Blueprint $table) {
            $table->foreignId('tariff_code_id')->nullable()->after('origin_country_id')->constrained('world_tariff_codes')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pim_products', function (Blueprint $table) {
            $table->dropForeign(['tariff_code_id']);
            $table->dropColumn('tariff_code_id');
        });
    }
};
