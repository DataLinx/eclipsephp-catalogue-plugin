<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\PriceList;
use Eclipse\Catalogue\Models\PriceListData;
use Eclipse\World\Models\Currency;
use Illuminate\Database\Seeder;

class PriceListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have at least one currency
        if (Currency::count() === 0) {
            Currency::create(['id' => 'EUR', 'name' => 'Euro', 'is_active' => true]);
        }

        // Create price lists using factory
        $priceLists = PriceList::factory()
            ->count(3)
            ->create();

        // Create price list data for each price list
        foreach ($priceLists as $index => $priceList) {
            // Create data for all tenants if tenancy is enabled
            $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
            $tenantModel = config('eclipse-catalogue.tenancy.model');

            if ($tenantFK && $tenantModel && class_exists($tenantModel)) {
                $tenants = $tenantModel::all();

                foreach ($tenants as $tenant) {
                    PriceListData::factory()->create([
                        'price_list_id' => $priceList->id,
                        $tenantFK => $tenant->id,
                        'is_active' => true,
                        'is_default' => $index === 0, // First price list is default selling
                        'is_default_purchase' => false,
                    ]);
                }
            } else {
                // No tenancy - create single record
                PriceListData::factory()->create([
                    'price_list_id' => $priceList->id,
                    'is_active' => true,
                    'is_default' => $index === 0, // First price list is default selling
                    'is_default_purchase' => false,
                ]);
            }
        }
    }
}
