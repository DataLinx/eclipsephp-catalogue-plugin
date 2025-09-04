<?php

use Eclipse\Catalogue\Models\Property;

beforeEach(function () {
    $this->migrate();
});

it('can create custom property with string input type', function () {
    $property = Property::create([
        'name' => ['en' => 'Brand Name'],
        'code' => 'brand_name',
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
        'description' => ['en' => 'Product brand name'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('string');
    expect($property->is_multilang)->toBeTrue();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeTrue();
});

it('can create custom property with text input type', function () {
    $property = Property::create([
        'name' => ['en' => 'Product Description'],
        'code' => 'product_description',
        'type' => 'custom',
        'input_type' => 'text',
        'is_multilang' => true,
        'description' => ['en' => 'Detailed product description'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('text');
    expect($property->is_multilang)->toBeTrue();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeTrue();
});

it('can create custom property with integer input type', function () {
    $property = Property::create([
        'name' => ['en' => 'Product Weight'],
        'code' => 'product_weight',
        'type' => 'custom',
        'input_type' => 'integer',
        'is_multilang' => false,
        'description' => ['en' => 'Product weight in grams'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('integer');
    expect($property->is_multilang)->toBeFalse();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeFalse();
});

it('can create custom property with decimal input type', function () {
    $property = Property::create([
        'name' => ['en' => 'Product Price'],
        'code' => 'product_price',
        'type' => 'custom',
        'input_type' => 'decimal',
        'is_multilang' => false,
        'description' => ['en' => 'Product price in EUR'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('decimal');
    expect($property->is_multilang)->toBeFalse();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeFalse();
});

it('can create custom property with date input type', function () {
    $property = Property::create([
        'name' => ['en' => 'Release Date'],
        'code' => 'release_date',
        'type' => 'custom',
        'input_type' => 'date',
        'is_multilang' => false,
        'description' => ['en' => 'Product release date'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('date');
    expect($property->is_multilang)->toBeFalse();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeFalse();
});

it('can create custom property with datetime input type', function () {
    $property = Property::create([
        'name' => ['en' => 'Last Updated'],
        'code' => 'last_updated',
        'type' => 'custom',
        'input_type' => 'datetime',
        'is_multilang' => false,
        'description' => ['en' => 'Last update timestamp'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('datetime');
    expect($property->is_multilang)->toBeFalse();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeFalse();
});

it('can create custom property with file input type', function () {
    $property = Property::create([
        'name' => ['en' => 'Product Manual'],
        'code' => 'product_manual',
        'type' => 'custom',
        'input_type' => 'file',
        'max_values' => 1,
        'is_multilang' => true,
        'description' => ['en' => 'Product manual PDF'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('file');
    expect($property->max_values)->toBe(1);
    expect($property->is_multilang)->toBeTrue();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeTrue();
});

it('can create custom property with multiple files', function () {
    $property = Property::create([
        'name' => ['en' => 'Product Images'],
        'code' => 'product_images',
        'type' => 'custom',
        'input_type' => 'file',
        'max_values' => 5,
        'is_multilang' => false,
        'description' => ['en' => 'Product image gallery'],
    ]);

    expect($property)->toBeInstanceOf(Property::class);
    expect($property->type)->toBe('custom');
    expect($property->input_type)->toBe('file');
    expect($property->max_values)->toBe(5);
    expect($property->is_multilang)->toBeFalse();
    expect($property->isCustomType())->toBeTrue();
    expect($property->supportsMultilang())->toBeFalse();
});
