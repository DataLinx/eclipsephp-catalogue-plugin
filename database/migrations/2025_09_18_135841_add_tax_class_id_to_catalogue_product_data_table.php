<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalogue_product_data', function (Blueprint $table) {
            $table->foreignId('tax_class_id')
                ->nullable()
                ->after('product_status_id')
                ->constrained('pim_tax_classes')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('catalogue_product_data', function (Blueprint $table) {
            $table->dropForeign(['tax_class_id']);
            $table->dropColumn('tax_class_id');
        });
    }
};
