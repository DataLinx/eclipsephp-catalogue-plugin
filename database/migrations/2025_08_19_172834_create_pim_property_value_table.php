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
        Schema::create('pim_property_value', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('pim_property')->onDelete('cascade')->onUpdate('cascade');
            $table->string('value');
            $table->smallInteger('sort')->default(0);
            $table->string('info_url')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pim_property_value');
    }
};
