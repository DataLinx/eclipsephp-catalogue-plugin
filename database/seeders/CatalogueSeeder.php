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
        $this->call(CurrencySeeder::class);

        $this->call(CategorySeeder::class);

        $this->call(ProductSeeder::class);
    }
}
