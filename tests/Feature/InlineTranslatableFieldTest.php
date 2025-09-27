<?php

use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\Property;

beforeEach(function () {
    $this->migrate();
});

it('can save and retrieve multilang string custom property value using inline field', function () {
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
        'sl' => 'Nike Slovenia',
        'it' => 'Nike Italia',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    $customValue = $product->getCustomPropertyValue($property);
    expect($customValue->value)->toBe($multilangValue);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('Nike');
});

it('can save and retrieve multilang text custom property value using inline field', function () {
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
    expect($customValue->value)->toBe($multilangValue);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('High quality sports shoes');
});

it('can save and retrieve multilang file custom property value using inline field', function () {
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
    expect($customValue->value)->toBe($multilangValue);
    expect($product->getCustomPropertyValueFormatted($property))->toBe('manual_en.pdf');
});

it('handles empty multilang values correctly in inline field', function () {
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
        'sl' => 'Nike Slovenia',
        'it' => '',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    expect($product->getCustomPropertyValueFormatted($property))->toBe('Nike Slovenia');
});

it('handles completely empty multilang values correctly in inline field', function () {
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

it('serializes and deserializes multilang values correctly with inline field', function () {
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
        'sl' => 'Nike Slovenia',
        'it' => 'Nike Italia',
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

it('includes multilang custom property values in global search with inline field', function () {
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
        'sl' => 'Nike Slovenia',
        'it' => 'Nike Italia',
    ];

    $product->setCustomPropertyValue($property, $multilangValue);

    // Test that multilang custom property values are included in search
    $searchValue = $product->getCustomPropertyValuesForSearch();
    expect($searchValue)->toContain('Brand Name');
    expect($searchValue)->toContain('Nike');
});
