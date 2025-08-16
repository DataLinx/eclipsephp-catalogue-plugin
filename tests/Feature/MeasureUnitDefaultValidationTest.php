<?php

use Eclipse\Catalogue\Models\MeasureUnit;

beforeEach(function () {
    $this->migrate();
});

it('ensures only one unit can be set as default when creating', function () {
    // Create first default unit
    $firstUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    // Create second unit as default
    $secondUnit = MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => true,
    ]);

    // Refresh first unit from database
    $firstUnit->refresh();

    // Check that only the new unit is default
    expect($firstUnit->is_default)->toBeFalse();
    expect($secondUnit->is_default)->toBeTrue();
});

it('ensures only one unit can be set as default when updating', function () {
    $firstUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    $secondUnit = MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    // Update second unit to be default
    $secondUnit->update(['is_default' => true]);

    // Refresh first unit
    $firstUnit->refresh();

    // Check that only the updated unit is default
    expect($firstUnit->is_default)->toBeFalse();
    expect($secondUnit->is_default)->toBeTrue();
});

it('allows multiple units to be non-default', function () {
    $firstUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => false,
    ]);

    $secondUnit = MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    // Check that both units are non-default
    expect($firstUnit->is_default)->toBeFalse();
    expect($secondUnit->is_default)->toBeFalse();
});

it('can unset default by updating to false', function () {
    $unit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    $unit->update(['is_default' => false]);

    expect($unit->is_default)->toBeFalse();
});
