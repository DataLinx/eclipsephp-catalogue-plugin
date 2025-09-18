<?php

namespace Eclipse\Catalogue\Seeders;

use Eclipse\Catalogue\Models\Category;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductData;
use Eclipse\Catalogue\Models\ProductStatus;
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
        $this->ensureProductStatusesExist();

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

                    $productData = ProductData::factory()->create([
                        'product_id' => $product->id,
                        $tenantFK => $tenant->id,
                        'is_active' => true,
                        'has_free_delivery' => false,
                        'category_id' => $categoryId,
                    ]);

                    // Assign random product status for this tenant
                    $this->assignRandomProductStatus($productData, $tenant->id);

                    // Get groups for this specific tenant
                    $tenantGroups = Group::where($tenantFK, $tenant->id)->get();
                    $groupsToAdd = $this->determineGroupsForProduct($index, $tenantGroups);

                    foreach ($groupsToAdd as $group) {
                        $group->addProduct($product);
                    }
                }
            } else {
                $categoryId = Category::query()->inRandomOrder()->value('id');

                $productData = ProductData::factory()->create([
                    'product_id' => $product->id,
                    'is_active' => true,
                    'has_free_delivery' => false,
                    'category_id' => $categoryId,
                ]);

                // Assign random product status for non-tenant scenario
                $this->assignRandomProductStatus($productData, null);

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

    private function ensureProductStatusesExist(): void
    {
        $productStatuses = ProductStatus::all();

        if ($productStatuses->isEmpty()) {
            $this->call(ProductStatusSeeder::class);
        }
    }

    /**
     * Assign a random product status to a product data record, respecting tenancy.
     */
    private function assignRandomProductStatus(ProductData $productData, ?int $tenantId): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        // Get available product statuses for the tenant
        $query = ProductStatus::query();

        if ($tenantFK && $tenantId !== null) {
            // Multi-tenant scenario: get statuses for this specific tenant
            $query->where($tenantFK, $tenantId);
        } elseif (! $tenantFK) {
            // Single-tenant scenario: get all statuses (site_id will be null)
            $query->whereNull('site_id');
        }

        $availableStatuses = $query->get();

        if ($availableStatuses->isNotEmpty()) {
            // Randomly select a status (with 70% chance of getting a status, 30% chance of no status)
            if (rand(1, 100) <= 70) {
                $randomStatus = $availableStatuses->random();
                $productData->update(['product_status_id' => $randomStatus->id]);
            }
        }
    }
}
