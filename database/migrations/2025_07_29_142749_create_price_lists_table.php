<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('currency_id', 3);
            $table->foreign('currency_id')
                ->references('id')
                ->on('world_currencies')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('tax_included');
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // More price list data, optionally in tenant-scope
        Schema::create('pim_price_list_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')
                ->constrained('pim_price_lists')
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
            $table->boolean('is_default_purchase')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_price_list_data');
        Schema::dropIfExists('pim_price_lists');
    }
};
