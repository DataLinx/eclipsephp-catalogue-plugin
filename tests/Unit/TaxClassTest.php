<?php

use Eclipse\Catalogue\Models\TaxClass;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->migrate();
    // Tenant is required because of the foreign key
    $this->setUpSuperAdminAndTenant();
});

it('can create a tax class', function () {
    $taxClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate for most products',
        'rate' => 20.00,
        'is_default' => false,
    ]);

    expect($taxClass)->toBeInstanceOf(TaxClass::class);
    expect($taxClass->name)->toBe('Standard Rate');
    expect($taxClass->description)->toBe('Standard tax rate for most products');
    expect($taxClass->rate)->toBe('20.00');
    expect($taxClass->is_default)->toBeFalse();
});

it('can set a tax class as default', function () {
    $taxClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    expect($taxClass->is_default)->toBeTrue();
    expect($taxClass->isDefault())->toBeTrue();
});

it('ensures only one class can be default', function () {
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

    expect($firstClass->is_default)->toBeFalse();
    expect($secondClass->is_default)->toBeTrue();
});

it('can get the default tax class', function () {
    TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => false,
    ]);

    $defaultClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    $retrieved = TaxClass::getDefault();

    expect($retrieved)->not->toBeNull();
    expect($retrieved->id)->toBe($defaultClass->id);
    expect($retrieved->name)->toBe('Standard Rate');
});

it('returns null when no default class exists', function () {
    TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => false,
    ]);

    $default = TaxClass::getDefault();

    expect($default)->toBeNull();
});

it('prevents deletion of default class', function () {
    $defaultClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    expect(fn () => $defaultClass->delete())
        ->toThrow(ValidationException::class, 'Cannot delete the default tax class.');
});

it('allows deletion of non-default class', function () {
    $class = TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => false,
    ]);

    expect($class->delete())->toBeTrue();
});

it('can soft delete and restore classes', function () {
    $class = TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => false,
    ]);

    $class->delete();
    expect($class->trashed())->toBeTrue();

    $class->restore();
    expect($class->trashed())->toBeFalse();
});

it('updates default when editing existing class', function () {
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

    expect($firstClass->is_default)->toBeFalse();
    expect($secondClass->is_default)->toBeTrue();
});
