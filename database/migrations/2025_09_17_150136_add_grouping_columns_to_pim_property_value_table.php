<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pim_property_value', function (Blueprint $table) {
            $table->boolean('is_group')->default(false)->after('image');

            $table->foreignId('group_value_id')
                ->nullable()
                ->after('is_group')
                ->constrained('pim_property_value')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pim_property_value', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_value_id');
            $table->dropColumn('is_group');
        });
    }
};
