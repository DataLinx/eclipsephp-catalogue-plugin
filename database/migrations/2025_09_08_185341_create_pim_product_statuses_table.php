<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_product_statuses', function (Blueprint $table) {
            $table->id();

            $tenantModel = config('eclipse-catalogue.tenancy.model');
            $tenantFk = config('eclipse-catalogue.tenancy.foreign_key');
            if ($tenantModel && $tenantFk) {
                /** @var \Illuminate\Database\Eloquent\Model $tenant */
                $tenant = new $tenantModel;
                $table->foreignId($tenantFk)
                    ->constrained($tenant->getTable(), $tenant->getKeyName())
                    ->cascadeOnUpdate()
                    ->cascadeOnDelete();
            }

            $table->string('code', 20)->nullable();
            $table->json('title');
            $table->json('description')->nullable();
            $table->string('label_type', 50)->default('gray');
            $table->boolean('shown_in_browse')->default(true);
            $table->boolean('allow_price_display')->default(true);
            $table->boolean('allow_sale')->default(true);
            $table->boolean('is_default')->default(false);
            $table->tinyInteger('priority');
            $table->string('sd_item_availability', 50);
            $table->boolean('skip_stock_qty_check')->default(false);
            $table->timestamps();

            if ($tenantModel && $tenantFk) {
                $table->unique([$tenantFk, 'code']);
            } else {
                $table->unique('code');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_product_statuses');
    }
};
