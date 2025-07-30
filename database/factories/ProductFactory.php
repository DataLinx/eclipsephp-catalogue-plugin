<?php

namespace Eclipse\Catalogue\Factories;

use Eclipse\Catalogue\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            $imageNumber = rand(1, 15);
            $imagePath = storage_path("app/public/sample-products/{$imageNumber}.jpg");

            if (file_exists($imagePath)) {
                try {
                    $product->addMedia($imagePath)
                        ->preservingOriginal()
                        ->withCustomProperties(['is_cover' => true])
                        ->toMediaCollection('images');
                } catch (Exception $e) {
                    Log::warning("Failed to attach image to product {$product->id}: ".$e->getMessage());
                }
            }
        });
    }
}
