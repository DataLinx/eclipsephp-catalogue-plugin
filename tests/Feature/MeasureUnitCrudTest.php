<?php

use Eclipse\Catalogue\Models\MeasureUnit;

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

    $this->assertDatabaseHas('pim_measure_units', [
        'name' => 'Kilogram',
        'is_default' => false,
    ]);
});

it('can update a measure unit', function () {
    $measureUnit = MeasureUnit::create([
        'name' => 'Kilogram',
        'is_default' => false,
    ]);

    $measureUnit->update([
        'name' => 'Updated Kilogram',
        'is_default' => true,
    ]);

    expect($measureUnit->name)->toBe('Updated Kilogram');
    expect($measureUnit->is_default)->toBeTrue();

    $this->assertDatabaseHas('pim_measure_units', [
        'id' => $measureUnit->id,
        'name' => 'Updated Kilogram',
        'is_default' => true,
    ]);
});

it('can soft delete a non-default measure unit', function () {
    $measureUnit = MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    $measureUnit->delete();

    $this->assertSoftDeleted('pim_measure_units', [
        'id' => $measureUnit->id,
    ]);
});

it('can restore a soft deleted measure unit', function () {
    $measureUnit = MeasureUnit::create([
        'name' => 'Gram',
        'is_default' => false,
    ]);

    $measureUnit->delete();
    $measureUnit->restore();

    $this->assertDatabaseHas('pim_measure_units', [
        'id' => $measureUnit->id,
        'deleted_at' => null,
    ]);
});
