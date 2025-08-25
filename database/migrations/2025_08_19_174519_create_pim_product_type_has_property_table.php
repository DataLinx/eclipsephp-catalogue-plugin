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
        Schema::create('pim_product_type_has_property', function (Blueprint $table) {
            $table->foreignId('product_type_id')->constrained('pim_product_types')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('property_id')->constrained('pim_property')->onDelete('cascade')->onUpdate('cascade');
            $table->smallInteger('sort')->nullable();
            $table->timestamps();
            $table->primary(['product_type_id', 'property_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pim_product_type_has_property');
    }
};
