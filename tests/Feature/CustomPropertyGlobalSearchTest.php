<?php

use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\Property;

beforeEach(function () {
    $this->migrate();
});

it('includes custom property values in global search', function () {
    // Create custom property
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Create product with custom property value
    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);
    $product->setCustomPropertyValue($property, 'Nike');

    // Test that custom property values are included in search
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toContain('Brand Name');
    expect($searchValue)->toContain('Nike');
});

it('includes multilang custom property values in global search', function () {
    // Create custom property with multilang support
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);

    // Create product with multilang custom property value
    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);
    $product->setCustomPropertyValue($property, [
        'en' => 'Nike',
        'sl' => 'Nike',
        'it' => 'Nike',
    ]);

    // Test that multilang custom property values are included in search
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toContain('Brand Name');
    expect($searchValue)->toContain('Nike');
});

it('handles multiple custom properties in global search', function () {
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

    // Create product with multiple custom property values
    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);
    $product->setCustomPropertyValue($brandProperty, 'Nike');
    $product->setCustomPropertyValue($colorProperty, 'Red');

    // Test that multiple custom property values are included in search
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toContain('Brand Name');
    expect($searchValue)->toContain('Nike');
    expect($searchValue)->toContain('Color');
    expect($searchValue)->toContain('Red');
});

it('returns empty string for products without custom properties', function () {
    // Create product without custom properties
    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    // Test that empty string is returned for products without custom properties
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toBe('');
});

it('handles empty custom property values in global search', function () {
    // Create custom property
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Create product with empty custom property value
    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);
    $product->setCustomPropertyValue($property, '');

    // Test that empty values are handled correctly
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toBe('');
});

it('handles multilang custom property with empty values in global search', function () {
    // Create custom property with multilang support
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);

    // Create product with empty multilang custom property values
    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);
    $product->setCustomPropertyValue($property, [
        'en' => '',
        'sl' => '',
        'it' => '',
    ]);

    // Test that empty multilang values are handled correctly
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toBe('');
});

it('includes custom property values in search with proper formatting', function () {
    // Create custom property
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Create product with custom property value
    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);
    $product->setCustomPropertyValue($property, 'Nike');

    // Test that search value includes both property name and value
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toContain('Brand Name Nike');
});
