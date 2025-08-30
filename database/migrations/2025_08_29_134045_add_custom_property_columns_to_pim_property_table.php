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
        Schema::table('pim_property', function (Blueprint $table) {
            $table->enum('type', ['list', 'custom'])->default('list')->after('is_filter');
            $table->enum('input_type', ['string', 'text', 'integer', 'decimal', 'date', 'datetime', 'file'])->nullable()->after('type');
            $table->boolean('is_multilang')->default(false)->after('input_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pim_property', function (Blueprint $table) {
            $table->dropColumn(['type', 'input_type', 'is_multilang']);
        });
    }
};
