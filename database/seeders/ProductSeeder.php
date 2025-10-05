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
use Illuminate\Support\Facades\DB;
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
            ->count(20)
            ->create([
                'product_type_id' => function () use ($productTypes) {
                    return $productTypes->random()->id;
                },
            ]);

        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
        $tenantModel = config('eclipse-catalogue.tenancy.model');

        $products = Product::query()->latest('id')->take(20)->get();
        $groupProductInserts = [];
        $productDataInserts = [];

        if ($tenantFK && $tenantModel && class_exists($tenantModel)) {
            $tenants = $tenantModel::all();

            $categoriesByTenant = Category::query()
                ->withoutGlobalScopes()
                ->get()
                ->groupBy($tenantFK);

            $groupsByTenant = Group::all()->groupBy($tenantFK);

            foreach ($products as $index => $product) {
                foreach ($tenants as $tenant) {
                    $categories = $categoriesByTenant->get($tenant->id);
                    $categoryId = $categories?->random()?->id;

                    $productDataInserts[] = [
                        'product_id' => $product->id,
                        $tenantFK => $tenant->id,
                        'is_active' => true,
                        'has_free_delivery' => false,
                        'category_id' => $categoryId,
                    ];

                    $tenantGroups = $groupsByTenant->get($tenant->id, collect());
                    $groupsToAdd = $this->determineGroupsForProduct($index, $tenantGroups);

                    foreach ($groupsToAdd as $groupIndex => $group) {
                        $groupProductInserts[] = [
                            'group_id' => $group->id,
                            'product_id' => $product->id,
                            'sort' => $groupIndex + 1,
                        ];
                    }
                }
            }
        } else {
            $categories = Category::all();
            $groups = Group::all();

            foreach ($products as $index => $product) {
                $productDataInserts[] = [
                    'product_id' => $product->id,
                    'is_active' => true,
                    'has_free_delivery' => false,
                    'category_id' => $categories->random()->id,
                ];

                $groupsToAdd = $this->determineGroupsForProduct($index, $groups);
                foreach ($groupsToAdd as $groupIndex => $group) {
                    $groupProductInserts[] = [
                        'group_id' => $group->id,
                        'product_id' => $product->id,
                        'sort' => $groupIndex + 1,
                    ];
                }
            }
        }

        if (! empty($productDataInserts)) {
            ProductData::insert($productDataInserts);

            $createdProductData = ProductData::whereIn('product_id', $products->pluck('id'))->get();

            if ($tenantFK && $tenantModel && class_exists($tenantModel)) {
                foreach ($createdProductData as $productData) {
                    $this->assignRandomProductStatus($productData, $productData->{$tenantFK});
                }
            } else {
                foreach ($createdProductData as $productData) {
                    $this->assignRandomProductStatus($productData, null);
                }
            }
        }

        if (! empty($groupProductInserts)) {
            DB::table('pim_group_has_product')->insert($groupProductInserts);
        }

        $this->attachImagesToProducts($products);
    }

    private function determineGroupsForProduct(int $productIndex, $groups): array
    {
        if ($groups->isEmpty()) {
            return [];
        }

        $numGroupsToAdd = rand(1, min(3, $groups->count()));

        return $groups->random($numGroupsToAdd)->all();
    }

    private function ensureSampleImagesExist(): void
    {
        Storage::disk('public')->makeDirectory('sample-products');

        if (count(Storage::disk('public')->files('sample-products')) >= 15) {
            return;
        }

        for ($i = 1; $i <= 15; $i++) {
            if (Storage::disk('public')->exists("sample-products/{$i}.jpg")) {
                continue;
            }

            $image = $this->generatePlaceholderImage($i);
            Storage::disk('public')->put("sample-products/{$i}.jpg", $image);
        }
    }

    private function generatePlaceholderImage(int $index): string
    {
        $image = imagecreatetruecolor(400, 300);
        $hue = ($index * 24) % 360;

        $this->drawBackground($image, $hue);
        $this->drawShapes($image, $hue);
        $this->drawOverlay($image, $hue);

        ob_start();
        imagejpeg($image, null, 85);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return $imageData;
    }

    private function drawBackground($image, int $hue): void
    {
        [$r, $g, $b] = $this->hslToRgb($hue / 360, 0.6, 0.5);
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);
    }

    private function drawShapes($image, int $baseHue): void
    {
        for ($i = 0; $i < 50; $i++) {
            $hue = ($baseHue + rand(-30, 30)) / 360;
            [$r, $g, $b] = $this->hslToRgb($hue, rand(40, 80) / 100, rand(30, 70) / 100);
            $color = imagecolorallocate($image, $r, $g, $b);

            match (rand(0, 2)) {
                0 => imagefilledellipse($image, rand(0, 400), rand(0, 300), rand(20, 100), rand(20, 100), $color),
                1 => imageline($image, rand(0, 400), rand(0, 300), rand(0, 400), rand(0, 300), $color),
                2 => imagefilledrectangle($image, rand(0, 400), rand(0, 300), rand(0, 400), rand(0, 300), $color),
            };
        }
    }

    private function drawOverlay($image, int $baseHue): void
    {
        for ($i = 0; $i < 3; $i++) {
            [$r, $g, $b] = $this->hslToRgb($baseHue / 360, 0.1, rand(80, 95) / 100);
            $overlayColor = imagecolorallocatealpha($image, $r, $g, $b, rand(90, 110));
            imagefilledellipse($image, rand(-50, 350), rand(-50, 250), rand(200, 400), rand(200, 400), $overlayColor);
        }
    }

    private function hslToRgb(float $h, float $s, float $l): array
    {
        if ($s == 0) {
            $rgb = $l;

            return [round($rgb * 255), round($rgb * 255), round($rgb * 255)];
        }

        $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
        $p = 2 * $l - $q;

        return [
            round($this->hueToRgb($p, $q, $h + 1 / 3) * 255),
            round($this->hueToRgb($p, $q, $h) * 255),
            round($this->hueToRgb($p, $q, $h - 1 / 3) * 255),
        ];
    }

    private function hueToRgb(float $p, float $q, float $t): float
    {
        $t = match (true) {
            $t < 0 => $t + 1,
            $t > 1 => $t - 1,
            default => $t,
        };

        return match (true) {
            $t < 1 / 6 => $p + ($q - $p) * 6 * $t,
            $t < 1 / 2 => $q,
            $t < 2 / 3 => $p + ($q - $p) * (2 / 3 - $t) * 6,
            default => $p,
        };
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

    private function assignRandomProductStatus(ProductData $productData, ?int $tenantId): void
    {
        $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');

        $query = ProductStatus::query();

        if ($tenantFK && $tenantId !== null) {
            $query->where($tenantFK, $tenantId);
        } elseif (! $tenantFK) {
            $query->whereNull('site_id');
        }

        $availableStatuses = $query->get();

        if ($availableStatuses->isNotEmpty() && rand(1, 100) <= 70) {
            $randomStatus = $availableStatuses->random();
            $productData->update(['product_status_id' => $randomStatus->id]);
        }
    }

    private function attachImagesToProducts($products): void
    {
        $productsWithImages = $products->random(10);

        foreach ($productsWithImages as $index => $product) {
            $imageNumber = ($index % 15) + 1;
            $sourceFileName = "{$imageNumber}.jpg";
            $sourcePath = storage_path("app/public/sample-products/{$sourceFileName}");

            if (file_exists($sourcePath)) {
                try {
                    $product->addMedia($sourcePath)
                        ->preservingOriginal()
                        ->withCustomProperties(['is_cover' => true])
                        ->toMediaCollection('images', 'public');
                } catch (Exception $e) {
                }
            }
        }
    }
}
