<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Product;
use Eclipse\World\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $englishName = mb_ucfirst(fake()->words(3, true));
        $slovenianName = 'SI: '.$englishName;

        $englishShortDesc = fake()->sentence();
        $slovenianShortDesc = 'SI: '.$englishShortDesc;

        $englishDesc = fake()->paragraphs(3, true);
        $slovenianDesc = 'SI: '.$englishDesc;

        $englishMetaTitle = mb_ucfirst(fake()->words(5, true));
        $slovenianMetaTitle = 'SI: '.$englishMetaTitle;

        $englishMetaDesc = fake()->sentence(15);
        $slovenianMetaDesc = 'SI: '.$englishMetaDesc;

        $stockQty = fake()->randomFloat(5, 0, 1000);
        $minStockQty = $stockQty * 0.1;

        return [
            'code' => fake()->numerify('######'),
            'barcode' => fake()->ean13(),
            'manufacturers_code' => fake()->bothify('MFR-####???'),
            'suppliers_code' => fake()->bothify('SUP-####???'),
            'net_weight' => fake()->randomFloat(2, 0.1, 100),
            'gross_weight' => fake()->randomFloat(2, 0.1, 100),
            'name' => [
                'en' => $englishName,
                'sl' => $slovenianName,
            ],
            'short_description' => [
                'en' => $englishShortDesc,
                'sl' => $slovenianShortDesc,
            ],
            'description' => [
                'en' => $englishDesc,
                'sl' => $slovenianDesc,
            ],
            'sort' => fake()->bothify('SORT-###'),
            'is_active' => fake()->boolean(80),
            'stock_qty' => $stockQty,
            'min_stock_qty' => $minStockQty,
            'stock_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'available_from_date' => fake()->optional(0.3)->dateTimeBetween('-10 days', '+30 days'),
            'free_delivery' => fake()->boolean(25),
            'origin_country_id' => Country::inRandomOrder()->first()?->id ?? Country::factory()->create()->id,
            'meta_desc' => [
                'en' => $englishMetaDesc,
                'sl' => $slovenianMetaDesc,
            ],
            'meta_title' => [
                'en' => $englishMetaTitle,
                'sl' => $slovenianMetaTitle,
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
