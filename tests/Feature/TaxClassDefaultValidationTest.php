<?php

use Eclipse\Catalogue\Models\TaxClass;

beforeEach(function () {
    $this->migrate();
    $this->setUpSuperAdminAndTenant();
});

it('ensures only one class can be set as default when creating', function () {
    // Create first default class
    $firstClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    // Create second class as default
    $secondClass = TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => true,
    ]);

    // Refresh first class from database
    $firstClass->refresh();

    // Check that only the new class is default
    expect($firstClass->is_default)->toBeFalse();
    expect($secondClass->is_default)->toBeTrue();
});

it('ensures only one class can be set as default when updating', function () {
    $firstClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    $secondClass = TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => false,
    ]);

    // Update second class to be default
    $secondClass->update(['is_default' => true]);

    // Refresh first class
    $firstClass->refresh();

    // Check that only the updated class is default
    expect($firstClass->is_default)->toBeFalse();
    expect($secondClass->is_default)->toBeTrue();
});

it('allows multiple classes to be non-default', function () {
    $firstClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => false,
    ]);

    $secondClass = TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => false,
    ]);

    // Check that both classes are non-default
    expect($firstClass->is_default)->toBeFalse();
    expect($secondClass->is_default)->toBeFalse();
});

it('can unset default by updating to false', function () {
    $class = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    $class->update(['is_default' => false]);

    expect($class->is_default)->toBeFalse();
});
