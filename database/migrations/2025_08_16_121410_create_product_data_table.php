<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_product_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');

            // Add foreign key for tenant if it's configured in the catalogue config
            if (config('eclipse-catalogue.tenancy.model')) {
                $tenantClass = config('eclipse-catalogue.tenancy.model');
                /** @var \Illuminate\Database\Eloquent\Model $tenant */
                $tenant = new $tenantClass;
                $table->foreignId(config('eclipse-catalogue.tenancy.foreign_key'))
                    ->constrained($tenant->getTable(), $tenant->getKeyName())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }

            $table->string('sorting_label')->nullable();
            $table->boolean('is_active')->default(false);
            $table->dateTime('available_from_date')->nullable();
            $table->boolean('has_free_delivery')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_product_data');
    }
};
