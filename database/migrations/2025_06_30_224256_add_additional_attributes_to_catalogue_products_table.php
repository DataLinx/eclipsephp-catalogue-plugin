<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('catalogue_products', function (Blueprint $table) {
            $table->string('sort')->nullable()->after('id');
            $table->boolean('is_active')->default(false)->after('sort');
            $table->decimal('stock_qty', 20, 5)->nullable()->after('is_active');
            $table->decimal('min_stock_qty', 20, 5)->nullable()->after('stock_qty');
            $table->date('stock_date')->nullable()->after('min_stock_qty');
            $table->date('available_from_date')->nullable()->after('stock_date');
            $table->boolean('free_delivery')->default(false)->after('available_from_date');

            $table->string('origin_country_id', 2)->nullable()->after('free_delivery');
            $table->text('meta_desc')->nullable()->after('origin_country_id');
            $table->string('meta_title')->nullable()->after('meta_desc');

            $table->foreign('origin_country_id')->references('id')->on('world_countries')->onDelete('set null');

            $table->index('is_active');
            $table->index('stock_qty');
            $table->index('available_from_date');
            $table->index('origin_country_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogue_products', function (Blueprint $table) {
            $table->dropForeign(['origin_country_id']);

            $table->dropIndex(['is_active']);
            $table->dropIndex(['stock_qty']);
            $table->dropIndex(['available_from_date']);
            $table->dropIndex(['origin_country_id']);

            $table->dropColumn([
                'sort',
                'is_active',
                'stock_qty',
                'min_stock_qty',
                'stock_date',
                'available_from_date',
                'free_delivery',
                'origin_country_id',
                'meta_desc',
                'meta_title',
            ]);
        });
    }
};
