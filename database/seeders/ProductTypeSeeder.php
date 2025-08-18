<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\ProductTypeData;
use Illuminate\Database\Seeder;

class ProductTypeSeeder extends Seeder
{
    public function run(): void
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenants = $tenantModel::all();

        $productTypes = [
            [
                'name' => $this->getTranslatedName('Common product'),
                'code' => 'COMMON',
            ],
            [
                'name' => $this->getTranslatedName('T-shirt'),
                'code' => 'TSHIRT',
            ],
            [
                'name' => $this->getTranslatedName('Shoes'),
                'code' => 'SHOES',
            ],
        ];

        foreach ($productTypes as $productTypeData) {
            $productType = ProductType::firstOrCreate(
                ['code' => $productTypeData['code']],
                $productTypeData
            );

            foreach ($tenants as $tenant) {
                ProductTypeData::firstOrCreate(
                    [
                        'product_type_id' => $productType->id,
                        $tenantFK => $tenant->id,
                    ],
                    [
                        'product_type_id' => $productType->id,
                        $tenantFK => $tenant->id,
                        'is_active' => true,
                        'is_default' => false,
                    ]
                );
            }
        }
    }

    /**
     * Get translated name for all available locales.
     */
    protected function getTranslatedName(string $baseName): array
    {
        $locales = $this->getAvailableLocales();
        $translatedNames = [];

        foreach ($locales as $locale) {
            if ($locale === 'en') {
                $translatedNames[$locale] = $baseName;
            } else {
                $translatedNames[$locale] = strtoupper($locale).': '.$baseName;
            }
        }

        return $translatedNames;
    }

    /**
     * Get available locales for the application.
     */
    protected function getAvailableLocales(): array
    {
        if (class_exists(\Eclipse\Core\Models\Locale::class)) {
            return \Eclipse\Core\Models\Locale::getAvailableLocales()
                ->pluck('id')
                ->toArray();
        }

        return ['en'];
    }
}
