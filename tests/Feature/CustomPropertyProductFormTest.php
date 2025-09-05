<?php

use Eclipse\Catalogue\Models\CustomPropertyValue;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\Property;

beforeEach(function () {
    $this->migrate();
});

it('can save and retrieve string custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $product->setCustomPropertyValue($property, 'Nike');

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe('Nike');
    expect($product->getCustomPropertyValueFormatted($property))->toBe('Nike');
});

it('can save and retrieve integer custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Weight'],
        'type' => 'custom',
        'input_type' => 'integer',
        'is_multilang' => false,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $product->setCustomPropertyValue($property, 500);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe(500);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('500');
});

it('can save and retrieve decimal custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Price'],
        'type' => 'custom',
        'input_type' => 'decimal',
        'is_multilang' => false,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $product->setCustomPropertyValue($property, 29.99);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe(29.99);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('29.99');
});

it('can save and retrieve date custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Release Date'],
        'type' => 'custom',
        'input_type' => 'date',
        'is_multilang' => false,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $date = '2024-01-15';
    $product->setCustomPropertyValue($property, $date);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe($date);
    expect($product->getCustomPropertyValueFormatted($property))->toBe($date);
});

it('can save and retrieve text custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Description'],
        'type' => 'custom',
        'input_type' => 'text',
        'is_multilang' => false,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $text = 'This is a detailed product description with multiple lines.';
    $product->setCustomPropertyValue($property, $text);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe($text);
    expect($product->getCustomPropertyValueFormatted($property))->toBe($text);
});

it('can update existing custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    // Set initial value
    $product->setCustomPropertyValue($property, 'Nike');
    expect($product->getCustomPropertyValueFormatted($property))->toBe('Nike');

    // Update value
    $product->setCustomPropertyValue($property, 'Adidas');
    expect($product->getCustomPropertyValueFormatted($property))->toBe('Adidas');

    // Should only have one record
    expect($product->customPropertyValues()->where('property_id', $property->id)->count())->toBe(1);
});

it('returns empty string for non-existent custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    expect($product->getCustomPropertyValue($property))->toBeNull();
    expect($product->getCustomPropertyValueFormatted($property))->toBe('');
});
