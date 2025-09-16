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
        // Ensure currencies exist
        if (! Currency::where('id', 'EUR')->exists()) {
            Currency::create(['id' => 'EUR', 'name' => 'Euro', 'is_active' => true]);
        }
        if (! Currency::where('id', 'USD')->exists()) {
            Currency::create(['id' => 'USD', 'name' => 'US Dollar', 'is_active' => true]);
        }

        // Desired price lists
        $definitions = [
            [
                'name' => 'Wholesale / Veleprodajni cenik',
                'code' => 'VPC',
                'currency_id' => 'EUR',
                'tax_included' => false,
                'is_default' => false,
                'is_default_purchase' => false,
            ],
            [
                'name' => 'Retail / Maloprodajni cenik',
                'code' => 'MPC',
                'currency_id' => 'EUR',
                'tax_included' => true,
                'is_default' => true, // default selling
                'is_default_purchase' => false,
            ],
            [
                'name' => 'Purchase / Nabavni cenik',
                'code' => 'NC',
                'currency_id' => 'USD',
                'tax_included' => false,
                'is_default' => false,
                'is_default_purchase' => true, // default purchase
            ],
        ];

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenants = collect();
        if ($tenantFK && $tenantModel && class_exists($tenantModel)) {
            $tenants = $tenantModel::all();
        }

        foreach ($definitions as $def) {
            $priceList = PriceList::updateOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'currency_id' => $def['currency_id'],
                    'tax_included' => $def['tax_included'],
                ]
            );

            // Ensure per-tenant data
            if ($tenants->isNotEmpty()) {
                foreach ($tenants as $tenant) {
                    PriceListData::updateOrCreate(
                        [
                            'price_list_id' => $priceList->id,
                            $tenantFK => $tenant->id,
                        ],
                        [
                            'is_active' => true,
                            'is_default' => $def['is_default'],
                            'is_default_purchase' => $def['is_default_purchase'],
                        ]
                    );
                }
            } else {
                PriceListData::updateOrCreate(
                    [
                        'price_list_id' => $priceList->id,
                    ],
                    [
                        'is_active' => true,
                        'is_default' => $def['is_default'],
                        'is_default_purchase' => $def['is_default_purchase'],
                    ]
                );
            }
        }
    }
}
