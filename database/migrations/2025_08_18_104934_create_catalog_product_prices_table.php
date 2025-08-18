<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_product_prices', function (Blueprint $table) {
            /*
             * @todo: remove these comments before PR is made
             */
            $table->id();
            $table->foreignId('product_id')
                ->constrained('catalog_products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('price_list_id')
                ->constrained('pim_price_lists')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            // Dates are inclusive, valid_to is optional
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            // Price should be decimal(20,5)
            $table->decimal('price');
            // Automatically copied from the price list but can be overridden by user when editing the price
            $table->boolean('tax_included');
            $table->timestamps();

            // This should also be enforced by form validation
            $table->unique(['product_id', 'price_list_id', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_prices');
    }
};
