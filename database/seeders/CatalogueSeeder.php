<?php

namespace Eclipse\Catalogue\Seeders;

use Illuminate\Database\Seeder;

/**
 * Default catalogue seeder
 */
class CatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MeasureUnitSeeder::class);
        $this->call(TaxClassSeeder::class);
        $this->call(PriceListSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ProductTypeSeeder::class);
        $this->call(GroupSeeder::class);
        $this->call(PropertySeeder::class);
        $this->call(ProductStatusSeeder::class);
        $this->call(ProductSeeder::class);
    }
}
