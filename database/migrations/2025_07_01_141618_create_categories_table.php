<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->integer('parent_id')->default(-1);
            $table->string('image')->nullable();
            $table->integer('sort')->default(0)->index();
            $table->boolean('is_active');
            $table->string('code')->nullable();
            $table->boolean('recursive_browsing')->nullable();
            $table->string('sef_key')->nullable();
            $table->text('short_desc')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_categories');
    }
};
