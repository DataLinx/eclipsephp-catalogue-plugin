<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\ProductStatus;
use Illuminate\Database\Seeder;

class ProductStatusSeeder extends Seeder
{
    public function run(): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenants = [];
        if ($tenantFK && ($model = config('eclipse-catalogue.tenancy.model'))) {
            $tenants = (new $model)::query()->pluck('id')->all();
        } else {
            $tenants = [null];
        }

        foreach ($tenants as $tenantId) {
            $defaults = [
                ['code' => 'in_stock', 'title' => ['en' => 'In stock', 'sl' => 'Na zalogi'], 'label_type' => 'success', 'priority' => 1, 'is_default' => true],
                ['code' => 'out_of_stock', 'title' => ['en' => 'Out of stock', 'sl' => 'Trenutno razprodan'], 'label_type' => 'danger', 'priority' => 5],
                ['code' => 'coming', 'title' => ['en' => 'Coming soon', 'sl' => 'V prihodu'], 'label_type' => 'info', 'priority' => 3],
            ];

            foreach ($defaults as $row) {
                $availabilityMap = [
                    'in_stock' => \Eclipse\Catalogue\Enums\StructuredData\ItemAvailability::IN_STOCK->value,
                    'out_of_stock' => \Eclipse\Catalogue\Enums\StructuredData\ItemAvailability::OUT_OF_STOCK->value,
                    'coming' => \Eclipse\Catalogue\Enums\StructuredData\ItemAvailability::PREORDER->value,
                ];

                $data = array_merge([
                    'description' => null,
                    'shown_in_browse' => true,
                    'allow_price_display' => true,
                    'allow_sale' => true,
                    'sd_item_availability' => $availabilityMap[$row['code']] ?? \Eclipse\Catalogue\Enums\StructuredData\ItemAvailability::IN_STOCK->value,
                    'skip_stock_qty_check' => false,
                ], $row);

                if ($tenantFK && $tenantId !== null) {
                    $data[$tenantFK] = $tenantId;
                }

                $unique = ['code' => $data['code']];
                if ($tenantFK && $tenantId !== null) {
                    $unique[$tenantFK] = $tenantId;
                }
                ProductStatus::query()->firstOrCreate($unique, $data);
            }
        }
    }
}
