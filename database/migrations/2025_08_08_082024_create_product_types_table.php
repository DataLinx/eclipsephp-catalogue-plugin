<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pim_product_type_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_type_id')
                ->constrained('pim_product_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

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

            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_product_type_data');
        Schema::dropIfExists('pim_product_types');
    }
};
