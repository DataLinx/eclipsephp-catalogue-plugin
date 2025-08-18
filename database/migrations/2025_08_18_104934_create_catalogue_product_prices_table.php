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
        Schema::create('catalogue_product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('catalogue_products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('price_list_id')
                ->constrained('pim_price_lists')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->decimal('price', 20, 5);
            $table->boolean('tax_included');
            $table->timestamps();
            $table->unique(['product_id', 'price_list_id', 'valid_from'], 'uq_cpp_pid_plid_vf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogue_product_prices');
    }
};
