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
        Schema::create('pim_product_has_custom_prop_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('pim_products')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('property_id')->constrained('pim_property')->onDelete('cascade')->onUpdate('cascade');
            $table->text('value');
            $table->timestamps();
            $table->unique(['product_id', 'property_id'], 'product_custom_property_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pim_product_has_custom_prop_value');
    }
};
