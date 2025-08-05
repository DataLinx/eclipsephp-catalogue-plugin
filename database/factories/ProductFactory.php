<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $englishName = mb_ucfirst($this->faker->words(3, true));
        $slovenianName = 'SI: '.$englishName;

        $englishShortDesc = $this->faker->sentence();
        $slovenianShortDesc = 'SI: '.$englishShortDesc;

        $englishDesc = $this->faker->paragraphs(3, true);
        $slovenianDesc = 'SI: '.$englishDesc;

        return [
            'code' => $this->faker->numerify('######'),
            'barcode' => $this->faker->ean13(),
            'manufacturers_code' => $this->faker->bothify('MFR-####???'),
            'suppliers_code' => $this->faker->bothify('SUP-####???'),
            'net_weight' => $this->faker->randomFloat(2, 0.1, 100),
            'gross_weight' => $this->faker->randomFloat(2, 0.1, 100),
            'name' => [
                'en' => $englishName,
                'sl' => $slovenianName,
            ],
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'short_description' => [
                'en' => $englishShortDesc,
                'sl' => $slovenianShortDesc,
            ],
            'description' => [
                'en' => $englishDesc,
                'sl' => $slovenianDesc,
            ],
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
