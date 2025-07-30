<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\Product;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureSampleImagesExist();

        Product::factory()
            ->count(100)
            ->create();
    }

    private function ensureSampleImagesExist(): void
    {
        Storage::disk('public')->makeDirectory('sample-products');

        $existingImages = Storage::disk('public')->files('sample-products');

        if (count($existingImages) >= 15) {
            $this->command->info('Sample images already exist.');

            return;
        }

        $this->command->info('Downloading sample product images...');

        for ($i = 1; $i <= 15; $i++) {
            $imagePath = "sample-products/{$i}.jpg";

            if (Storage::disk('public')->exists($imagePath)) {
                continue;
            }

            try {
                $imageUrl = "https://picsum.photos/400/300?random={$i}";
                $response = Http::timeout(10)->get($imageUrl);

                if ($response->successful()) {
                    Storage::disk('public')->put($imagePath, $response->body());
                    $this->command->info("Downloaded image {$i}/15");
                }
            } catch (Exception $e) {
                $this->command->warn("Failed to download image {$i}: ".$e->getMessage());
            }
        }

        $this->command->info('Sample images ready!');
    }
}
