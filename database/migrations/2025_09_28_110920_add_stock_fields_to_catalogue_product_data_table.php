<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pim_product_data', function (Blueprint $table) {
            $table->decimal('stock', 20, 5)->nullable()->after('has_free_delivery');
            $table->decimal('min_stock', 20, 5)->nullable()->after('stock');
            $table->date('date_stocked')->nullable()->after('min_stock');
        });
    }

    public function down(): void
    {
        Schema::table('pim_product_data', function (Blueprint $table) {
            $table->dropColumn(['stock', 'min_stock', 'date_stocked']);
        });
    }
};
