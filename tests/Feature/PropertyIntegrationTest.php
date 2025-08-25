<?php

use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\ProductType;
use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;

beforeEach(function () {
    $this->migrate();
});

it('global property is auto-assigned to existing product types on creation', function () {
    // Create product types first
    $productType1 = ProductType::factory()->create();
    $productType2 = ProductType::factory()->create();

    // Create global property
    $property = Property::create([
        'name' => ['en' => 'Global Brand'],
        'is_active' => true,
        'is_global' => true,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    // Check that property was auto-assigned to both product types
    expect($productType1->properties()->where('property_id', $property->id)->exists())->toBeTrue();
    expect($productType2->properties()->where('property_id', $property->id)->exists())->toBeTrue();

    $this->assertDatabaseHas('pim_product_type_has_property', [
        'product_type_id' => $productType1->id,
        'property_id' => $property->id,
    ]);

    $this->assertDatabaseHas('pim_product_type_has_property', [
        'product_type_id' => $productType2->id,
        'property_id' => $property->id,
    ]);
});

it('global property is auto-assigned to new product types', function () {
    // Create global property first
    $property = Property::create([
        'name' => ['en' => 'Global Brand'],
        'is_active' => true,
        'is_global' => true,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => false,
    ]);

    // Create product type after global property exists
    $productType = ProductType::factory()->create();

    // Check that property was auto-assigned to new product type
    expect($productType->properties()->where('property_id', $property->id)->exists())->toBeTrue();

    $this->assertDatabaseHas('pim_product_type_has_property', [
        'product_type_id' => $productType->id,
        'property_id' => $property->id,
    ]);
});

it('updating property to global assigns it to all product types', function () {
    // Create product types and non-global property
    $productType1 = ProductType::factory()->create();
    $productType2 = ProductType::factory()->create();

    $property = Property::factory()->create(['is_global' => false]);

    // Initially not assigned to any product types
    expect($productType1->properties()->where('property_id', $property->id)->exists())->toBeFalse();
    expect($productType2->properties()->where('property_id', $property->id)->exists())->toBeFalse();

    // Update to global
    $property->update(['is_global' => true]);

    // Should now be assigned to all product types
    expect($productType1->properties()->where('property_id', $property->id)->exists())->toBeTrue();
    expect($productType2->properties()->where('property_id', $property->id)->exists())->toBeTrue();
});

it('can assign property values to products', function () {
    $productType = ProductType::factory()->create();
    $product = Product::factory()->create(['product_type_id' => $productType->id]);

    $property = Property::factory()->create();
    $value1 = PropertyValue::factory()->create(['property_id' => $property->id]);
    $value2 = PropertyValue::factory()->create(['property_id' => $property->id]);

    // Assign property values to product
    $product->propertyValues()->attach([$value1->id, $value2->id]);

    expect($product->propertyValues)->toHaveCount(2);
    expect($product->propertyValues->pluck('id')->toArray())->toContain($value1->id);
    expect($product->propertyValues->pluck('id')->toArray())->toContain($value2->id);

    $this->assertDatabaseHas('catalogue_product_has_property_value', [
        'product_id' => $product->id,
        'property_value_id' => $value1->id,
    ]);

    $this->assertDatabaseHas('catalogue_product_has_property_value', [
        'product_id' => $product->id,
        'property_value_id' => $value2->id,
    ]);
});

it('can get products by property value', function () {
    $property = Property::factory()->create();
    $value = PropertyValue::factory()->create(['property_id' => $property->id]);

    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    $product3 = Product::factory()->create();

    // Assign value to first two products
    $product1->propertyValues()->attach($value->id);
    $product2->propertyValues()->attach($value->id);

    $productsWithValue = $value->products;

    expect($productsWithValue)->toHaveCount(2);
    expect($productsWithValue->pluck('id')->toArray())->toContain($product1->id);
    expect($productsWithValue->pluck('id')->toArray())->toContain($product2->id);
    expect($productsWithValue->pluck('id')->toArray())->not->toContain($product3->id);
});

it('property form field type changes based on value count', function () {
    $singleProperty = Property::factory()->create(['max_values' => 1]);
    $multiProperty = Property::factory()->create(['max_values' => 3]);

    // Initially no values - should still work
    expect($singleProperty->getFormFieldType())->toBe('radio');
    expect($multiProperty->getFormFieldType())->toBe('checkbox');

    // Add 2 values - should be radio/checkbox
    PropertyValue::factory()->count(2)->create(['property_id' => $singleProperty->id]);
    PropertyValue::factory()->count(2)->create(['property_id' => $multiProperty->id]);

    $singleProperty->refresh();
    $multiProperty->refresh();

    expect($singleProperty->getFormFieldType())->toBe('radio');
    expect($multiProperty->getFormFieldType())->toBe('checkbox');

    // Add more values to reach 4+ - should be select/multiselect
    PropertyValue::factory()->count(3)->create(['property_id' => $singleProperty->id]);
    PropertyValue::factory()->count(3)->create(['property_id' => $multiProperty->id]);

    $singleProperty->refresh();
    $multiProperty->refresh();

    expect($singleProperty->getFormFieldType())->toBe('select');
    expect($multiProperty->getFormFieldType())->toBe('multiselect');
});

it('can sort properties within product type', function () {
    $productType = ProductType::factory()->create();
    $property1 = Property::factory()->create(['is_global' => false]);
    $property2 = Property::factory()->create(['is_global' => false]);
    $property3 = Property::factory()->create(['is_global' => false]);

    // Attach with specific sort orders
    $productType->properties()->attach([
        $property1->id => ['sort' => 30],
        $property2->id => ['sort' => 10],
        $property3->id => ['sort' => 20],
    ]);

    $sortedProperties = $productType->properties()->orderBy('pim_product_type_has_property.sort')->get();

    expect($sortedProperties->pluck('id')->toArray())->toBe([
        $property2->id,
        $property3->id,
        $property1->id,
    ]);
});

it('can update property sort order within product type', function () {
    $productType = ProductType::factory()->create();
    $property = Property::factory()->create(['is_global' => false]);

    $productType->properties()->attach($property->id, ['sort' => 10]);

    // Update sort order
    $productType->properties()->updateExistingPivot($property->id, ['sort' => 50]);

    $pivot = $productType->properties()->where('property_id', $property->id)->first()->pivot;
    expect($pivot->sort)->toBe(50);
});

it('deleting property removes product type assignments', function () {
    $productType = ProductType::factory()->create();
    $property = Property::factory()->create(['is_global' => false]);

    $productType->properties()->attach($property->id, ['sort' => 10]);

    // Verify assignment exists
    $this->assertDatabaseHas('pim_product_type_has_property', [
        'product_type_id' => $productType->id,
        'property_id' => $property->id,
    ]);

    // First soft delete, then force delete to test cascade
    $property->delete();
    $property->forceDelete();

    // Verify assignment is removed
    $this->assertDatabaseMissing('pim_product_type_has_property', [
        'product_type_id' => $productType->id,
        'property_id' => $property->id,
    ]);
});
