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
        $this->call(CategorySeeder::class);
        $this->call(ProductTypeSeeder::class);
        $this->call(ProductSeeder::class);
    }
}
