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
    ]);

    expect($value)->toBeInstanceOf(PropertyValue::class);
    expect($value->getTranslation('value', 'en'))->toBe('Nike');
    expect($value->sort)->toBe(10);
    expect($value->property_id)->toBe($property->id);
});

it('belongs to a property', function () {
    $property = Property::factory()->create();
    $value = PropertyValue::factory()->create(['property_id' => $property->id]);

    expect($value->property)->toBeInstanceOf(Property::class);
    expect($value->property->id)->toBe($property->id);
});

it('can have info url', function () {
    $property = Property::factory()->create();

    $value = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Nike'],
        'info_url' => ['en' => 'https://nike.com'],
        'sort' => 10,
    ]);

    expect($value->getTranslation('info_url', 'en'))->toBe('https://nike.com');
});

it('can have image', function () {
    $property = Property::factory()->create();

    $value = PropertyValue::create([
        'property_id' => $property->id,
        'value' => ['en' => 'Nike'],
        'image' => ['en' => 'nike-logo.png'],
        'sort' => 10,
    ]);

    expect($value->getTranslation('image', 'en'))->toBe('nike-logo.png');
});

it('is sorted by sort field by default', function () {
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

    expect($sortedValues->first()->id)->toBe($value2->id);
    expect($sortedValues->get(1)->id)->toBe($value3->id);
    expect($sortedValues->last()->id)->toBe($value1->id);
});

// Translation tests
it('value attribute is translatable', function () {
    $value = PropertyValue::factory()->create([
        'value' => [
            'en' => 'English Value',
            'sl' => 'Slovenska vrednost',
        ],
    ]);

    expect($value->getTranslation('value', 'en'))->toBe('English Value');
    expect($value->getTranslation('value', 'sl'))->toBe('Slovenska vrednost');
});

it('info_url attribute is translatable', function () {
    $value = PropertyValue::factory()->create([
        'info_url' => [
            'en' => 'https://example.com/en',
            'sl' => 'https://example.com/sl',
        ],
    ]);

    expect($value->getTranslation('info_url', 'en'))->toBe('https://example.com/en');
    expect($value->getTranslation('info_url', 'sl'))->toBe('https://example.com/sl');
});

it('image attribute is translatable', function () {
    $value = PropertyValue::factory()->create([
        'image' => [
            'en' => 'image-en.png',
            'sl' => 'image-sl.png',
        ],
    ]);

    expect($value->getTranslation('image', 'en'))->toBe('image-en.png');
    expect($value->getTranslation('image', 'sl'))->toBe('image-sl.png');
});

// Factory tests
it('factory creates valid property values', function () {
    $value = PropertyValue::factory()->create();

    expect($value->getTranslation('value', 'en'))->toBeString();
    expect($value->sort)->toBeInt();
    expect($value->property_id)->toBeInt();
    expect($value->property)->toBeInstanceOf(Property::class);
});

it('factory can create value for specific property', function () {
    $property = Property::factory()->create();
    $value = PropertyValue::factory()->forProperty($property)->create();

    expect($value->property_id)->toBe($property->id);
    expect($value->property->id)->toBe($property->id);
});
