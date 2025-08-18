<?php

use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\ProductTypeData;
use Workbench\App\Models\Site;

/**
 * Helper: create a TypeData row including the tenant foreign key when
 * tenancy is enabled. Keeps the tests readable and future-proof.
 */
function createTypeDataForIntegration(array $attributes): ProductTypeData
{
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    if ($tenantFK) {
        $siteId = Site::first()?->id;
        // Ensure we always include the tenant FK when required
        $attributes[$tenantFK] = $attributes[$tenantFK] ?? $siteId;
    }

    return ProductTypeData::create($attributes);
}

// Product-Type Relationship Tests
test('product can have a type', function () {
    $type = ProductType::factory()->create();
    $product = Product::factory()->create([
        'product_type_id' => $type->id,
    ]);

    expect($product->type)->toBeInstanceOf(ProductType::class);
    expect($product->type->id)->toBe($type->id);
});

test('product can exist without a type', function () {
    $product = Product::factory()->create([
        'product_type_id' => null,
    ]);

    expect($product->type)->toBeNull();
    expect($product->product_type_id)->toBeNull();
});

test('product type relationship works correctly', function () {
    $type = ProductType::factory()->create([
        'name' => ['en' => 'Test Type'],
    ]);

    $product = Product::factory()->create([
        'product_type_id' => $type->id,
    ]);

    expect($product->type->getTranslation('name', 'en'))->toBe('Test Type');
});

// Product Form Integration Tests
test('product form shows active types only', function () {
    $activeType = ProductType::factory()->create();
    $inactiveType = ProductType::factory()->create();

    createTypeDataForIntegration([
        'product_type_id' => $activeType->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    createTypeDataForIntegration([
        'product_type_id' => $inactiveType->id,
        'is_active' => false,
        'is_default' => false,
    ]);

    // Simulate the query that would be used in the product form
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $currentTenant = Site::first();

    $query = ProductType::query();

    if ($tenantFK && $currentTenant) {
        $query->whereHas('productTypeData', function ($q) use ($tenantFK, $currentTenant) {
            $q->where($tenantFK, $currentTenant->id)
                ->where('is_active', true);
        });
    } else {
        $query->whereHas('productTypeData', function ($q) {
            $q->where('is_active', true);
        });
    }

    $availableTypes = $query->get();

    expect($availableTypes)->toHaveCount(1);
    expect($availableTypes->first()->id)->toBe($activeType->id);
});

// Product Table Integration Tests
test('product table shows type name', function () {
    $type = ProductType::factory()->create([
        'name' => ['en' => 'Widget Type'],
    ]);

    $product = Product::factory()->create([
        'product_type_id' => $type->id,
    ]);

    // Load the product with type relationship
    $productWithType = Product::with('type')->find($product->id);

    expect($productWithType->type)->not->toBeNull();
    expect($productWithType->type->getTranslation('name', 'en'))->toBe('Widget Type');
});

// Product Filter Integration Tests
test('product filter includes active types only', function () {
    $activeType = ProductType::factory()->create([
        'name' => ['en' => 'Active Type'],
    ]);
    $inactiveType = ProductType::factory()->create([
        'name' => ['en' => 'Inactive Type'],
    ]);

    createTypeDataForIntegration([
        'product_type_id' => $activeType->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    createTypeDataForIntegration([
        'product_type_id' => $inactiveType->id,
        'is_active' => false,
        'is_default' => false,
    ]);

    // Simulate the filter options query
    $tenantFK = config('eclipse-catalogue.tenancy.foreign_key');
    $currentTenant = Site::first();

    $query = ProductType::query();

    if ($tenantFK && $currentTenant) {
        $query->whereHas('productTypeData', function ($q) use ($tenantFK, $currentTenant) {
            $q->where($tenantFK, $currentTenant->id)
                ->where('is_active', true);
        });
    } else {
        $query->whereHas('productTypeData', function ($q) {
            $q->where('is_active', true);
        });
    }

    $filterOptions = $query->pluck('name', 'id')->toArray();

    expect($filterOptions)->toHaveCount(1);
    expect($filterOptions)->toHaveKey($activeType->id);
    expect($filterOptions)->not->toHaveKey($inactiveType->id);
});

test('product filter works with products', function () {
    $type1 = ProductType::factory()->create();
    $type2 = ProductType::factory()->create();

    createTypeDataForIntegration([
        'product_type_id' => $type1->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    createTypeDataForIntegration([
        'product_type_id' => $type2->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    $product1 = Product::factory()->create(['product_type_id' => $type1->id]);
    $product2 = Product::factory()->create(['product_type_id' => $type2->id]);
    $product3 = Product::factory()->create(['product_type_id' => null]);

    // Filter products by type1
    $filteredProducts = Product::where('product_type_id', $type1->id)->get();

    expect($filteredProducts)->toHaveCount(1);
    expect($filteredProducts->first()->id)->toBe($product1->id);
});

// Integration with Tenancy Tests
test('product type selection respects tenant context', function () {
    // Simulate tenancy being enabled
    config(['eclipse-catalogue.tenancy.foreign_key' => 'site_id']);
    config(['eclipse-catalogue.tenancy.model' => Site::class]);

    $site1 = Site::first();
    $site2 = Site::skip(1)->first() ?? Site::factory()->create();

    $type = ProductType::factory()->create();

    // Create type data for both sites, but only active for site1
    ProductTypeData::create([
        'product_type_id' => $type->id,
        'site_id' => $site1->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    ProductTypeData::create([
        'product_type_id' => $type->id,
        'site_id' => $site2->id,
        'is_active' => false,
        'is_default' => false,
    ]);

    // Query for site1 (should include the type)
    $site1Types = ProductType::whereHas('productTypeData', function ($q) use ($site1) {
        $q->where('site_id', $site1->id)->where('is_active', true);
    })->get();

    // Query for site2 (should not include the type)
    $site2Types = ProductType::whereHas('productTypeData', function ($q) use ($site2) {
        $q->where('site_id', $site2->id)->where('is_active', true);
    })->get();

    expect($site1Types)->toHaveCount(1);
    expect($site2Types)->toHaveCount(0);
});

// Default Type Tests
test('can get default type for tenant', function () {
    $defaultType = ProductType::factory()->create();
    $regularType = ProductType::factory()->create();

    createTypeDataForIntegration([
        'product_type_id' => $defaultType->id,
        'is_active' => true,
        'is_default' => true,
    ]);

    createTypeDataForIntegration([
        'product_type_id' => $regularType->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    $foundDefault = ProductType::getDefault();
    expect($foundDefault)->not->toBeNull();
    expect($foundDefault->id)->toBe($defaultType->id);
});

// Data Consistency Tests
test('product can be created with valid type', function () {
    $type = ProductType::factory()->create();

    createTypeDataForIntegration([
        'product_type_id' => $type->id,
        'is_active' => true,
        'is_default' => false,
    ]);

    $product = Product::factory()->create([
        'product_type_id' => $type->id,
        'name' => ['en' => 'Test Product'],
    ]);

    expect($product->product_type_id)->toBe($type->id);
    expect($product->type)->toBeInstanceOf(ProductType::class);
});

test('product type id is properly cast', function () {
    $type = ProductType::factory()->create();
    $product = Product::factory()->create([
        'product_type_id' => $type->id,
    ]);

    expect($product->product_type_id)->toBeInt();
    expect($product->product_type_id)->toBe($type->id);
});
