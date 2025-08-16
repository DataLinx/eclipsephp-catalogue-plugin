<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_tax_classes', function (Blueprint $table) {
            $table->id();

            if (config('eclipse-catalogue.tenancy.model')) {
                $tenantClass = config('eclipse-catalogue.tenancy.model');
                /** @var \Illuminate\Database\Eloquent\Model $tenant */
                $tenant = new $tenantClass;
                $table->foreignId(config('eclipse-catalogue.tenancy.foreign_key'))
                    ->constrained($tenant->getTable(), $tenant->getKeyName())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }

            $table->decimal('rate', total: 5);
            $table->boolean('is_default')
                ->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_tax_classes');
    }
};
