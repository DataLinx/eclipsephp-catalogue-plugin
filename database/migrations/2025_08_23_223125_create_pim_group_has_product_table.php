<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_group_has_product', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->constrained('catalogue_products', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('group_id')
                ->constrained('pim_group', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->integer('sort')->nullable();

            $table->primary(['product_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_group_has_product');
    }
};
