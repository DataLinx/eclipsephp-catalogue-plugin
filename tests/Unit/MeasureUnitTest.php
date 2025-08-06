<?php

use Eclipse\Catalogue\Models\MeasureUnit;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->migrate();
});

it('can create a measure unit', function () {
    $measureUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => false,
    ]);

    expect($measureUnit)->toBeInstanceOf(MeasureUnit::class);
    expect($measureUnit->name)->toBe('Kilogram');
    expect($measureUnit->is_default)->toBeFalse();
});

it('can set a measure unit as default', function () {
    $measureUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    expect($measureUnit->is_default)->toBeTrue();
    expect($measureUnit->isDefault())->toBeTrue();
});

it('ensures only one unit can be default', function () {
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

    expect($firstUnit->is_default)->toBeFalse();
    expect($secondUnit->is_default)->toBeTrue();
});

it('can get the default measure unit', function () {
    MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    $defaultUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    $retrieved = MeasureUnit::getDefault();

    expect($retrieved)->not->toBeNull();
    expect($retrieved->id)->toBe($defaultUnit->id);
    expect($retrieved->name)->toBe('Kilogram');
});

it('returns null when no default unit exists', function () {
    MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    $default = MeasureUnit::getDefault();

    expect($default)->toBeNull();
});

it('prevents deletion of default unit', function () {
    $defaultUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => true,
    ]);

    expect(fn () => $defaultUnit->delete())
        ->toThrow(ValidationException::class, 'Cannot delete the default unit of measure.');
});

it('allows deletion of non-default unit', function () {
    $unit = MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    expect($unit->delete())->toBeTrue();
});

it('can soft delete and restore units', function () {
    $unit = MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    $unit->delete();
    expect($unit->trashed())->toBeTrue();

    $unit->restore();
    expect($unit->trashed())->toBeFalse();
});

it('updates default when editing existing unit', function () {
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

    expect($firstUnit->is_default)->toBeFalse();
    expect($secondUnit->is_default)->toBeTrue();
});
