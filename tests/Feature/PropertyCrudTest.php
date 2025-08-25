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
        'description' => ['en' => 'Product brand'],
        'internal_name' => 'Brand/Manufacturer',
        'is_active' => true,
        'is_global' => false,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => true,
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->getTranslation('name', 'en'))->toBe('Brand');
    expect($property->code)->toBe('brand');

    $this->assertDatabaseHas('pim_property', [
        'id' => $property->id,
        'code' => 'brand',
        'is_active' => true,
        'is_global' => false,
        'max_values' => 1,
        'enable_sorting' => false,
        'is_filter' => true,
    ]);
});

it('can update a property', function () {
    $property = Property::factory()->create([
        'name' => ['en' => 'Original Name'],
        'code' => 'original',
        'is_global' => false,
    ]);

    $property->update([
        'name' => ['en' => 'Updated Name'],
        'code' => 'updated',
        'is_global' => true,
    ]);

    expect($property->getTranslation('name', 'en'))->toBe('Updated Name');
    expect($property->code)->toBe('updated');
    expect($property->is_global)->toBeTrue();

    $this->assertDatabaseHas('pim_property', [
        'id' => $property->id,
        'code' => 'updated',
        'is_global' => true,
    ]);
});

it('can soft delete a property', function () {
    $property = Property::factory()->create();

    $property->delete();

    $this->assertSoftDeleted('pim_property', [
        'id' => $property->id,
    ]);
});

it('can restore a soft deleted property', function () {
    $property = Property::factory()->create();

    $property->delete();
    $property->restore();

    $this->assertDatabaseHas('pim_property', [
        'id' => $property->id,
        'deleted_at' => null,
    ]);
});

it('can create property with values', function () {
    $property = Property::factory()->create();

    $value1 = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Nike'],
        'sort' => 10,
    ]);

    $value2 = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Adidas'],
        'sort' => 20,
    ]);

    expect($property->values)->toHaveCount(2);

    $this->assertDatabaseHas('pim_property_value', [
        'property_id' => $property->id,
        'sort' => 10,
    ]);

    $this->assertDatabaseHas('pim_property_value', [
        'property_id' => $property->id,
        'sort' => 20,
    ]);
});

it('can assign property to product types', function () {
    $property = Property::factory()->create(['is_global' => false]);
    $productType1 = ProductType::factory()->create();
    $productType2 = ProductType::factory()->create();

    $property->productTypes()->attach([
        $productType1->id => ['sort' => 10],
        $productType2->id => ['sort' => 20],
    ]);

    expect($property->productTypes)->toHaveCount(2);

    $this->assertDatabaseHas('pim_product_type_has_property', [
        'property_id' => $property->id,
        'product_type_id' => $productType1->id,
        'sort' => 10,
    ]);

    $this->assertDatabaseHas('pim_product_type_has_property', [
        'property_id' => $property->id,
        'product_type_id' => $productType2->id,
        'sort' => 20,
    ]);
});

it('can detach property from product types', function () {
    $property = Property::factory()->create(['is_global' => false]);
    $productType = ProductType::factory()->create();

    $property->productTypes()->attach($productType->id, ['sort' => 10]);
    expect($property->productTypes)->toHaveCount(1);

    $property->productTypes()->detach($productType->id);
    $property->refresh();
    expect($property->productTypes)->toHaveCount(0);

    $this->assertDatabaseMissing('pim_product_type_has_property', [
        'property_id' => $property->id,
        'product_type_id' => $productType->id,
    ]);
});

it('cascades delete to product type assignments', function () {
    $property = Property::factory()->create(['is_global' => false]);
    $productType = ProductType::factory()->create();

    $property->productTypes()->attach($productType->id, ['sort' => 10]);

    // First soft delete, then force delete to test cascade
    $property->delete();
    $property->forceDelete();

    $this->assertDatabaseMissing('pim_property', [
        'id' => $property->id,
    ]);

    $this->assertDatabaseMissing('pim_product_type_has_property', [
        'property_id' => $property->id,
        'product_type_id' => $productType->id,
    ]);
});
