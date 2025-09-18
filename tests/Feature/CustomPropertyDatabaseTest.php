<?php

use Eclipse\Catalogue\Models\Property;

beforeEach(function () {
    $this->migrate();
});

it('enforces type enum constraints', function () {
    // Valid types should work
    $property1 = Property::create([
        'name' => ['en' => 'Test Property'],
        'type' => 'list',
    ]);

    $property2 = Property::create([
        'name' => ['en' => 'Test Property 2'],
        'type' => 'custom',
    ]);

    expect($property1->type)->toBe('list');
    expect($property2->type)->toBe('custom');

    // Invalid type should fail
    expect(function () {
        Property::create([
            'name' => ['en' => 'Test Property 3'],
            'type' => 'invalid_type',
        ]);
    })->toThrow(Exception::class);
});

it('enforces input_type enum constraints', function () {
    // Valid input types should work
    $validInputTypes = ['string', 'text', 'integer', 'decimal', 'date', 'datetime', 'file'];

    foreach ($validInputTypes as $inputType) {
        $property = Property::create([
            'name' => ['en' => "Test Property {$inputType}"],
            'type' => 'custom',
            'input_type' => $inputType,
        ]);

        expect($property->input_type)->toBe($inputType);
    }

    // Invalid input type should fail
    expect(function () {
        Property::create([
            'name' => ['en' => 'Test Property Invalid'],
            'type' => 'custom',
            'input_type' => 'invalid_input_type',
        ]);
    })->toThrow(Exception::class);
});

it('sets default type to list', function () {
    $property = Property::create([
        'name' => ['en' => 'Test Property'],
    ]);

    $property->refresh();

    expect($property->type)->toBe('list');
});

it('allows null input_type for list properties', function () {
    $property = Property::create([
        'name' => ['en' => 'Test Property'],
        'type' => 'list',
        'input_type' => null,
    ]);

    expect($property->input_type)->toBeNull();
});

it('sets default is_multilang to false', function () {
    $property = Property::create([
        'name' => ['en' => 'Test Property'],
        'type' => 'custom',
        'input_type' => 'string',
    ]);

    $property->refresh();

    expect($property->is_multilang)->toBeFalse();
});
