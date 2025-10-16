<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_product_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->constrained('pim_products', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('child_id')
                ->constrained('pim_products', 'id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('type', 50);
            $table->tinyInteger('sort')->nullable();
            $table->timestamps();
            $table->unique(['parent_id', 'child_id', 'type']);
            $table->index(['parent_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_product_relations');
    }
};
