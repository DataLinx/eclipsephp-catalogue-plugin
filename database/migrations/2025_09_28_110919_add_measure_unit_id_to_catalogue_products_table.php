<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pim_products', function (Blueprint $table) {
            $table->foreignId('measure_unit_id')
                ->nullable()
                ->after('product_type_id')
                ->constrained('pim_measure_units')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('pim_products', function (Blueprint $table) {
            $table->dropForeign(['measure_unit_id']);
            $table->dropColumn('measure_unit_id');
        });
    }
};
