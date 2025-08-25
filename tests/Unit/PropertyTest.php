<?php

use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;

beforeEach(function () {
    $this->migrate();
});

it('can create a property', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand'],
        'code' => 'brand',
        'is_active' => true,
        'is_global' => false,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->getTranslation('name', 'en'))->toBe('Brand');
    expect($property->code)->toBe('brand');
    expect($property->is_active)->toBeTrue();
    expect($property->is_global)->toBeFalse();
});

it('converts property code to lowercase', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'code' => 'BRAND_NAME',
        'is_active' => true,
        'is_global' => false,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    expect($property->code)->toBe('brand_name');
});

it('can create property without code', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand'],
        'is_active' => true,
        'is_global' => false,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    expect($property->code)->toBeNull();
});

it('auto-assigns global property to all existing product types', function () {
    $productType1 = ProductType::factory()->create();
    $productType2 = ProductType::factory()->create();

    $property = Property::create([
        'name' => ['en' => 'Global Brand'],
        'code' => 'global_brand',
        'is_active' => true,
        'is_global' => true,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    expect($productType1->properties()->where('property_id', $property->id)->exists())->toBeTrue();
    expect($productType2->properties()->where('property_id', $property->id)->exists())->toBeTrue();
});

it('auto-assigns global property to new product types', function () {
    $globalProperty = Property::create([
        'name' => ['en' => 'Global Brand'],
        'code' => 'global_brand',
        'is_active' => true,
        'is_global' => true,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    // Create product type after global property exists
    $productType = ProductType::factory()->create();

    expect($productType->properties()->where('property_id', $globalProperty->id)->exists())->toBeTrue();
});

it('can have property values', function () {
    $property = Property::factory()->create();
    $value = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Nike'],
        'sort' => 10,
    ]);

    expect($property->values)->toHaveCount(1);
    expect($property->values->first()->getTranslation('value', 'en'))->toBe('Nike');
});

it('determines correct form field type for single value properties', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand'],
        'is_active' => true,
        'is_global' => false,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    // With less than 4 values, should be radio
    PropertyValue::factory()->count(2)->create(['property_id' => $property->id]);
    expect($property->getFormFieldType())->toBe('radio');

    // With 4+ values, should be select
    PropertyValue::factory()->count(3)->create(['property_id' => $property->id]);
    $property->refresh();
    expect($property->getFormFieldType())->toBe('select');
});

it('determines correct form field type for multiple value properties', function () {
    $property = Property::create([
        'name' => ['en' => 'Colors'],
        'is_active' => true,
        'is_global' => false,
        'max_values' => 3,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    // With less than 4 values, should be checkbox
    PropertyValue::factory()->count(2)->create(['property_id' => $property->id]);
    expect($property->getFormFieldType())->toBe('checkbox');

    // With 4+ values, should be multiselect
    PropertyValue::factory()->count(3)->create(['property_id' => $property->id]);
    $property->refresh();
    expect($property->getFormFieldType())->toBe('multiselect');
});

it('can be assigned to specific product types', function () {
    $property = Property::factory()->create(['is_global' => false]);
    $productType1 = ProductType::factory()->create();
    $productType2 = ProductType::factory()->create();

    $property->productTypes()->attach($productType1->id, ['sort' => 10]);

    expect($property->productTypes)->toHaveCount(1);
    expect($property->productTypes->first()->id)->toBe($productType1->id);
    expect($productType2->properties()->where('property_id', $property->id)->exists())->toBeFalse();
});

it('can update global status and assign to all product types', function () {
    $property = Property::factory()->create(['is_global' => false]);
    $productType1 = ProductType::factory()->create();
    $productType2 = ProductType::factory()->create();

    // Initially not assigned to any product types
    expect($property->productTypes)->toHaveCount(0);

    // Update to global
    $property->update(['is_global' => true]);

    // Should now be assigned to all product types
    expect($productType1->properties()->where('property_id', $property->id)->exists())->toBeTrue();
    expect($productType2->properties()->where('property_id', $property->id)->exists())->toBeTrue();
});

it('can soft delete property', function () {
    $property = Property::factory()->create();
    $id = $property->id;

    $property->delete();

    expect(Property::find($id))->toBeNull();
    expect(Property::withTrashed()->find($id))->not->toBeNull();
    expect(Property::withTrashed()->find($id)->trashed())->toBeTrue();
});

it('can restore soft deleted property', function () {
    $property = Property::factory()->create();
    $property->delete();

    $property->restore();

    expect($property->trashed())->toBeFalse();
    expect(Property::find($property->id))->not->toBeNull();
});

// Translation tests
it('name attribute is translatable', function () {
    $property = Property::factory()->create([
        'name' => [
            'en' => 'English Brand',
            'sl' => 'Slovenska znamka',
        ],
    ]);

    expect($property->getTranslation('name', 'en'))->toBe('English Brand');
    expect($property->getTranslation('name', 'sl'))->toBe('Slovenska znamka');
});

it('description attribute is translatable', function () {
    $property = Property::factory()->create([
        'description' => [
            'en' => 'English description',
            'sl' => 'Slovenski opis',
        ],
    ]);

    expect($property->getTranslation('description', 'en'))->toBe('English description');
    expect($property->getTranslation('description', 'sl'))->toBe('Slovenski opis');
});

// Factory tests
it('factory creates valid properties', function () {
    $property = Property::factory()->create();

    expect($property->getTranslation('name', 'en'))->toBeString();
    expect($property->is_active)->toBeBool();
    expect($property->is_global)->toBeBool();
    expect($property->max_values)->toBeInt();
    expect($property->enable_sorting)->toBeBool();
    expect($property->is_filter)->toBeBool();
});

it('factory can create global properties', function () {
    $property = Property::factory()->global()->create();

    expect($property->is_global)->toBeTrue();
});

it('factory can create single value properties', function () {
    $property = Property::factory()->singleValue()->create();

    expect($property->max_values)->toBe(1);
});

it('factory can create multiple value properties', function () {
    $property = Property::factory()->multipleValues()->create();

    expect($property->max_values)->toBeGreaterThan(1);
});

it('factory can create filter properties', function () {
    $property = Property::factory()->filter()->create();

    expect($property->is_filter)->toBeTrue();
});
