<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductData;
use Eclipse\Catalogue\Models\ProductType;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->ensureSampleImagesExist();
        $this->ensureProductTypesExist();
        $this->ensureGroupsExist();

        $productTypes = ProductType::all();

        Product::factory()
            ->count(100)
            ->create([
                'product_type_id' => function () use ($productTypes) {
                    return $productTypes->random()->id;
                },
            ]);

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        $products = Product::query()->latest('id')->take(100)->get();

        foreach ($products as $index => $product) {
            if ($tenantFK && $tenantModel && class_exists($tenantModel)) {
                $tenants = $tenantModel::all();
                foreach ($tenants as $tenant) {
                    $categoryId = Category::query()
                        ->withoutGlobalScopes()
                        ->where($tenantFK, $tenant->id)
                        ->inRandomOrder()
                        ->value('id');

                    ProductData::factory()->create([
                        'product_id' => $product->id,
                        $tenantFK => $tenant->id,
                        'is_active' => true,
                        'has_free_delivery' => false,
                        'category_id' => $categoryId,
                    ]);

                    // Get groups for this specific tenant
                    $tenantGroups = Group::where($tenantFK, $tenant->id)->get();
                    $groupsToAdd = $this->determineGroupsForProduct($index, $tenantGroups);

                    foreach ($groupsToAdd as $group) {
                        $group->addProduct($product);
                    }
                }
            } else {
                $categoryId = Category::query()->inRandomOrder()->value('id');

                ProductData::factory()->create([
                    'product_id' => $product->id,
                    'is_active' => true,
                    'has_free_delivery' => false,
                    'category_id' => $categoryId,
                ]);

                // For non-tenant scenarios, use all groups
                $groups = Group::all();
                $groupsToAdd = $this->determineGroupsForProduct($index, $groups);
                foreach ($groupsToAdd as $group) {
                    $group->addProduct($product);
                }
            }
        }
    }

    private function determineGroupsForProduct(int $productIndex, $groups): array
    {
        $groupsToAdd = [];

        // Randomly assign 1-3 groups per product
        $numGroupsToAdd = rand(1, min(3, $groups->count()));

        // Get random groups for this product
        $randomGroups = $groups->random($numGroupsToAdd);

        foreach ($randomGroups as $group) {
            $groupsToAdd[] = $group;
        }

        return $groupsToAdd;
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

    private function ensureProductTypesExist(): void
    {
        $productTypes = ProductType::all();

        if ($productTypes->isEmpty()) {
            $this->call(ProductTypeSeeder::class);
        }
    }

    private function ensureGroupsExist(): void
    {
        $groups = Group::all();

        if ($groups->isEmpty()) {
            $this->call(GroupSeeder::class);
        }
    }
}
