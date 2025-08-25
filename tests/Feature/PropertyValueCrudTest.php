<?php

use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;

beforeEach(function () {
    $this->migrate();
});

it('can create a property value', function () {
    $property = Property::factory()->create();

    $value = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Nike'],
        'sort' => 10,
        'info_url' => ['en' => 'https://nike.com'],
        'image' => ['en' => 'nike-logo.png'],
    ]);

    expect($value)->toBeInstanceOf(PropertyValue::class);
    expect($value->getTranslation('value', 'en'))->toBe('Nike');

    $this->assertDatabaseHas('pim_property_value', [
        'id' => $value->id,
        'property_id' => $property->id,
        'sort' => 10,
    ]);
});

it('can update a property value', function () {
    $value = PropertyValue::factory()->create([
        'value' => ['en' => 'Original Value'],
        'sort' => 10,
    ]);

    $value->update([
        'value' => ['en' => 'Updated Value'],
        'sort' => 20,
        'info_url' => ['en' => 'https://updated.com'],
    ]);

    expect($value->getTranslation('value', 'en'))->toBe('Updated Value');
    expect($value->sort)->toBe(20);
    expect($value->getTranslation('info_url', 'en'))->toBe('https://updated.com');

    $this->assertDatabaseHas('pim_property_value', [
        'id' => $value->id,
        'sort' => 20,
    ]);
});

it('maintains sort order when creating multiple values', function () {
    $property = Property::factory()->create();

    $value1 = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Third'],
        'sort' => 30,
    ]);

    $value2 = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'First'],
        'sort' => 10,
    ]);

    $value3 = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Second'],
        'sort' => 20,
    ]);

    $sortedValues = PropertyValue::where('property_id', $property->id)->get();

    expect($sortedValues->pluck('sort')->toArray())->toBe([10, 20, 30]);
    expect($sortedValues->pluck('id')->toArray())->toBe([$value2->id, $value3->id, $value1->id]);
});

it('can update sort order', function () {
    $property = Property::factory()->create();

    $value1 = PropertyValue::factory()->create([
        'property_id' => $property->id,
        'sort' => 10,
    ]);

    $value2 = PropertyValue::factory()->create([
        'property_id' => $property->id,
        'sort' => 20,
    ]);

    // Swap sort orders
    $value1->update(['sort' => 25]);
    $value2->update(['sort' => 5]);

    $sortedValues = PropertyValue::where('property_id', $property->id)->get();

    expect($sortedValues->first()->id)->toBe($value2->id);
    expect($sortedValues->last()->id)->toBe($value1->id);
});

it('can create value with all translatable fields', function () {
    $property = Property::factory()->create();

    $value = PropertyValue::create([
        'property_id' => $property->id,
        'value' => [
            'en' => 'English Value',
            'sl' => 'Slovenska vrednost',
        ],
        'info_url' => [
            'en' => 'https://example.com/en',
            'sl' => 'https://example.com/sl',
        ],
        'image' => [
            'en' => 'image-en.png',
            'sl' => 'image-sl.png',
        ],
        'sort' => 10,
    ]);

    expect($value->getTranslation('value', 'en'))->toBe('English Value');
    expect($value->getTranslation('value', 'sl'))->toBe('Slovenska vrednost');
    expect($value->getTranslation('info_url', 'en'))->toBe('https://example.com/en');
    expect($value->getTranslation('info_url', 'sl'))->toBe('https://example.com/sl');
    expect($value->getTranslation('image', 'en'))->toBe('image-en.png');
    expect($value->getTranslation('image', 'sl'))->toBe('image-sl.png');
});
