<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_group', function (Blueprint $table) {
            $table->id();

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

            $table->string('code', 50)->nullable();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_browsable')->default(false);
            $table->timestamps();

            // Create unique index on code
            if (config('eclipse-catalogue.tenancy.foreign_key')) {
                $table->unique([config('eclipse-catalogue.tenancy.foreign_key'), 'code']);
            } else {
                $table->unique('code');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_group');
    }
};
