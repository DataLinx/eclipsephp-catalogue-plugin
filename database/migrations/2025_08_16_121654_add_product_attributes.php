<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pim_products', function (Blueprint $table) {
            $table->string('origin_country_id', 2)->nullable();
            $table->foreign('origin_country_id')
                ->references('id')
                ->on('world_countries')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->text('meta_description')
                ->nullable();
            $table->string('meta_title')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pim_products', function (Blueprint $table) {
            $table->dropForeign(['origin_country_id']);
            $table->dropColumn([
                'origin_country_id',
                'meta_description',
                'meta_title',
            ]);
        });
    }
};
