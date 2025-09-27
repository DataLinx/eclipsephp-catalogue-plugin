<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pim_product_data', function (Blueprint $table) {
            $table->foreignId('product_status_id')
                ->nullable()
                ->after('product_id')
                ->constrained('pim_product_statuses')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('pim_product_data', function (Blueprint $table) {
            $table->dropForeign(['product_status_id']);
            $table->dropColumn('product_status_id');
        });
    }
};
