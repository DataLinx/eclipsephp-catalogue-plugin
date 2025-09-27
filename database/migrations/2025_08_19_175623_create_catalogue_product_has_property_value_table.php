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
        Schema::create('pim_product_has_property_value', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('pim_products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('property_value_id')->constrained('pim_property_value')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
            $table->unique(['product_id', 'property_value_id'], 'product_property_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pim_product_has_property_value');
    }
};
