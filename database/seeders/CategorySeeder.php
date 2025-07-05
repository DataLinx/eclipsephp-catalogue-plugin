<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Core\Models\Site;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $sites = Site::all();

        foreach ($sites as $site) {
            $parents = Category::factory()
                ->parent()
                ->active()
                ->count(3)
                ->create(['site_id' => $site->id]);

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
                    ->create(['site_id' => $site->id]);
            }
        }
    }
}
