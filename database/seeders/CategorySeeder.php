<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tenantModel = config('eclipse-catalogue.tenancy.model');
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenants = $tenantModel::all();

        foreach ($tenants as $tenant) {
            $parents = Category::factory()
                ->parent()
                ->active()
                ->count(3)
                ->create([$tenantFK => $tenant->id]);

            foreach ($parents as $index => $parent) {
                $childrenCount = match ($index) {
                    0 => 3,
                    1 => 2,
                    2 => 2,
                };

                Category::factory()
                    ->child($parent)
                    ->active()
                    ->count($childrenCount)
                    ->create([$tenantFK => $tenant->id]);
            }
        }
    }
}
