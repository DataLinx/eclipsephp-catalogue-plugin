<?php

use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\Property;

beforeEach(function () {
    $this->migrate();
});

it('can search products by custom property value', function () {
    // Create custom property
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Create products with custom property values
    $product1 = Product::create([
        'name' => ['en' => 'Nike Shoes'],
        'code' => 'nike-shoes',
    ]);
    $product1->setCustomPropertyValue($property, 'Nike');

    $product2 = Product::create([
        'name' => ['en' => 'Adidas Shoes'],
        'code' => 'adidas-shoes',
    ]);
    $product2->setCustomPropertyValue($property, 'Adidas');

    $product3 = Product::create([
        'name' => ['en' => 'Generic Shoes'],
        'code' => 'generic-shoes',
    ]);
    // No custom property value for product3

    // Test search functionality
    $searchResults = Product::whereHas('customPropertyValues', function ($query) use ($property) {
        $query->where('property_id', $property->id)
            ->where('value', 'Nike');
    })->get();

    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->id)->toBe($product1->id);
});

it('can filter products by custom property', function () {
    // Create custom property
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Create products with custom property values
    $product1 = Product::create([
        'name' => ['en' => 'Nike Shoes'],
        'code' => 'nike-shoes',
    ]);
    $product1->setCustomPropertyValue($property, 'Nike');

    $product2 = Product::create([
        'name' => ['en' => 'Adidas Shoes'],
        'code' => 'adidas-shoes',
    ]);
    $product2->setCustomPropertyValue($property, 'Adidas');

    $product3 = Product::create([
        'name' => ['en' => 'Generic Shoes'],
        'code' => 'generic-shoes',
    ]);
    // No custom property value for product3

    // Test filter functionality
    $filteredResults = Product::whereHas('customPropertyValues', function ($query) use ($property) {
        $query->where('property_id', $property->id);
    })->get();

    expect($filteredResults)->toHaveCount(2);
    expect($filteredResults->pluck('id')->toArray())->toContain($product1->id);
    expect($filteredResults->pluck('id')->toArray())->toContain($product2->id);
    expect($filteredResults->pluck('id')->toArray())->not->toContain($product3->id);
});

it('can search products by multiple custom properties', function () {
    // Create custom properties
    $brandProperty = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    $colorProperty = Property::create([
        'name' => ['en' => 'Color'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Create products with multiple custom property values
    $product1 = Product::create([
        'name' => ['en' => 'Nike Red Shoes'],
        'code' => 'nike-red-shoes',
    ]);
    $product1->setCustomPropertyValue($brandProperty, 'Nike');
    $product1->setCustomPropertyValue($colorProperty, 'Red');

    $product2 = Product::create([
        'name' => ['en' => 'Nike Blue Shoes'],
        'code' => 'nike-blue-shoes',
    ]);
    $product2->setCustomPropertyValue($brandProperty, 'Nike');
    $product2->setCustomPropertyValue($colorProperty, 'Blue');

    $product3 = Product::create([
        'name' => ['en' => 'Adidas Red Shoes'],
        'code' => 'adidas-red-shoes',
    ]);
    $product3->setCustomPropertyValue($brandProperty, 'Adidas');
    $product3->setCustomPropertyValue($colorProperty, 'Red');

    // Test search for Nike products
    $nikeProducts = Product::whereHas('customPropertyValues', function ($query) use ($brandProperty) {
        $query->where('property_id', $brandProperty->id)
            ->where('value', 'Nike');
    })->get();

    expect($nikeProducts)->toHaveCount(2);
    expect($nikeProducts->pluck('id')->toArray())->toContain($product1->id);
    expect($nikeProducts->pluck('id')->toArray())->toContain($product2->id);

    // Test search for Red products
    $redProducts = Product::whereHas('customPropertyValues', function ($query) use ($colorProperty) {
        $query->where('property_id', $colorProperty->id)
            ->where('value', 'Red');
    })->get();

    expect($redProducts)->toHaveCount(2);
    expect($redProducts->pluck('id')->toArray())->toContain($product1->id);
    expect($redProducts->pluck('id')->toArray())->toContain($product3->id);
});

it('can search products by multilang custom property values', function () {
    // Create custom property with multilang support
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);

    // Create products with multilang custom property values
    $product1 = Product::create([
        'name' => ['en' => 'Nike Shoes'],
        'code' => 'nike-shoes',
    ]);
    $product1->setCustomPropertyValue($property, [
        'en' => 'Nike',
        'sl' => 'Nike',
        'it' => 'Nike',
    ]);

    $product2 = Product::create([
        'name' => ['en' => 'Adidas Shoes'],
        'code' => 'adidas-shoes',
    ]);
    $product2->setCustomPropertyValue($property, [
        'en' => 'Adidas',
        'sl' => 'Adidas',
        'it' => 'Adidas',
    ]);

    // Test search functionality with multilang values
    $searchResults = Product::whereHas('customPropertyValues', function ($query) use ($property) {
        $query->where('property_id', $property->id)
            ->where('value', 'like', '%Nike%');
    })->get();

    expect($searchResults)->toHaveCount(1);
    expect($searchResults->first()->id)->toBe($product1->id);
});

it('returns correct custom property values for search', function () {
    // Create custom property
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Create product with custom property value
    $product = Product::create([
        'name' => ['en' => 'Nike Shoes'],
        'code' => 'nike-shoes',
    ]);
    $product->setCustomPropertyValue($property, 'Nike');

    // Test the search method
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toContain('Brand Name');
    expect($searchValue)->toContain('Nike');
});

it('handles products without custom properties in search', function () {
    // Create product without custom properties
    $product = Product::create([
        'name' => ['en' => 'Generic Product'],
        'code' => 'generic-product',
    ]);

    // Test the search method
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toBe('');
});
