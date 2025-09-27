<?php

use Eclipse\Catalogue\Models\CustomPropertyValue;
use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\Property;

beforeEach(function () {
    $this->migrate();
});

it('can save and retrieve multilang string custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $multilangValue = [
        'en' => 'Nike',
        'sl' => 'Nike',
        'it' => 'Nike',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe($multilangValue);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('Nike');
});

it('can save and retrieve multilang text custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Description'],
        'type' => 'custom',
        'input_type' => 'text',
        'is_multilang' => true,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $multilangValue = [
        'en' => 'High quality sports shoes',
        'sl' => 'Visokokakovostne športne copate',
        'it' => 'Scarpe sportive di alta qualità',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe($multilangValue);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('High quality sports shoes');
});

it('can save and retrieve multilang file custom property value', function () {
    $property = Property::create([
        'name' => ['en' => 'Manual'],
        'type' => 'custom',
        'input_type' => 'file',
        'max_values' => 1,
        'is_multilang' => true,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $multilangValue = [
        'en' => 'manual_en.pdf',
        'sl' => 'navodila_sl.pdf',
        'it' => 'manuale_it.pdf',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue)->toBeInstanceOf(CustomPropertyValue::class);
    expect($customValue->value)->toBe($multilangValue);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('manual_en.pdf');
});

it('returns first non-empty value for multilang property', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $multilangValue = [
        'en' => '',
        'sl' => 'Nike',
        'it' => '',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    expect($product->getCustomPropertyValueFormatted($property))->toBe('Nike');
});

it('returns empty string when all multilang values are empty', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $multilangValue = [
        'en' => '',
        'sl' => '',
        'it' => '',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    expect($product->getCustomPropertyValueFormatted($property))->toBe('');
});

it('handles non-multilang properties correctly', function () {
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

it('serializes and deserializes multilang values correctly', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);

    $product = Product::create([
        'name' => ['en' => 'Test Product'],
        'code' => 'test-product',
    ]);

    $multilangValue = [
        'en' => 'Nike',
        'sl' => 'Nike',
        'it' => 'Nike',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    // Verify the value is stored as JSON in the database
    $this->assertDatabaseHas('pim_product_has_custom_prop_value', [
        'product_id' => $product->id,
        'property_id' => $property->id,
        'value' => json_encode($multilangValue),
    ]);

    // Verify it's retrieved correctly
    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue->value)->toBe($multilangValue);
});
