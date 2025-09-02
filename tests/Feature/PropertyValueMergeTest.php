<?php

use Eclipse\Catalogue\Models\Product;
use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;

it('merges values by moving product references and deleting source', function () {
    $property = Property::factory()->create();
    $source = PropertyValue::factory()->create(['property_id' => $property->id, 'value' => 'Old']);
    $target = PropertyValue::factory()->create(['property_id' => $property->id, 'value' => 'New']);

    // Create products linked to source (and one already linked to target)
    $productA = Product::factory()->create();
    $productB = Product::factory()->create();
    $productC = Product::factory()->create();

    $productA->propertyValues()->attach($source->id);
    $productB->propertyValues()->attach($source->id);
    $productC->propertyValues()->attach($target->id); // should remain

    // Also add duplicate A to target to ensure duplicate cleanup works
    $productA->propertyValues()->attach($target->id);

    $result = $source->mergeInto($target->id);

    expect($result['deleted'])->toBe(1)
        ->and($result['relinked'])->toBeGreaterThanOrEqual(2)
        ->and(PropertyValue::query()->whereKey($source->id)->doesntExist())->toBeTrue();

    // All products should now reference only the target
    expect($productA->propertyValues()->pluck('property_value_id')->all())
        ->toEqual([$target->id]);
    expect($productB->propertyValues()->pluck('property_value_id')->all())
        ->toEqual([$target->id]);
    expect($productC->propertyValues()->pluck('property_value_id')->all())
        ->toEqual([$target->id]);
});

it('does not leave duplicate pivot rows after merge', function () {
    $property = Property::factory()->create();
    $source = PropertyValue::factory()->create(['property_id' => $property->id]);
    $target = PropertyValue::factory()->create(['property_id' => $property->id]);
    $product = Product::factory()->create();

    // Link product to both source and target
    $product->propertyValues()->attach($source->id);
    $product->propertyValues()->attach($target->id);

    $source->mergeInto($target->id);

    $count = DB::table('catalogue_product_has_property_value')
        ->where('product_id', $product->id)
        ->where('property_value_id', $target->id)
        ->count();

    expect($count)->toBe(1);
});

it('rolls back merge when values belong to different properties', function () {
    $prop1 = Property::factory()->create();
    $prop2 = Property::factory()->create();
    $source = PropertyValue::factory()->create(['property_id' => $prop1->id]);
    $target = PropertyValue::factory()->create(['property_id' => $prop2->id]);
    $product = Product::factory()->create();
    $product->propertyValues()->attach($source->id);

    try {
        $source->mergeInto($target->id);
        test()->fail('Expected exception not thrown');
    } catch (Throwable $e) {
        // ok
    }

    // Ensure source still exists and link remains
    expect(PropertyValue::query()->whereKey($source->id)->exists())->toBeTrue();
    $links = DB::table('catalogue_product_has_property_value')
        ->where('product_id', $product->id)
        ->where('property_value_id', $source->id)
        ->count();
    expect($links)->toBe(1);
});
