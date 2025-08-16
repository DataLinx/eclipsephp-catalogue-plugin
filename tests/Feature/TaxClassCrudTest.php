<?php

use Eclipse\Catalogue\Models\TaxClass;

beforeEach(function () {
    $this->migrate();
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

    $this->assertDatabaseHas('pim_tax_classes', [
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate for most products',
        'rate' => '20.00',
        'is_default' => false,
    ]);
});

it('can update a tax class', function () {
    $taxClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => false,
    ]);

    $taxClass->update([
        'name' => 'Updated Standard Rate',
        'description' => 'Updated description',
        'rate' => 21.00,
        'is_default' => true,
    ]);

    expect($taxClass->name)->toBe('Updated Standard Rate');
    expect($taxClass->description)->toBe('Updated description');
    expect($taxClass->rate)->toBe('21.00');
    expect($taxClass->is_default)->toBeTrue();

    $this->assertDatabaseHas('pim_tax_classes', [
        'id' => $taxClass->id,
        'name' => 'Updated Standard Rate',
        'description' => 'Updated description',
        'rate' => '21.00',
        'is_default' => true,
    ]);
});

it('can soft delete a non-default tax class', function () {
    $taxClass = TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => false,
    ]);

    $taxClass->delete();

    $this->assertSoftDeleted('pim_tax_classes', [
        'id' => $taxClass->id,
    ]);
});

it('can restore a soft deleted tax class', function () {
    $taxClass = TaxClass::create([
        'name' => 'Reduced Rate',
        'description' => 'Reduced tax rate',
        'rate' => 5.00,
        'is_default' => false,
    ]);

    $taxClass->delete();
    $taxClass->restore();

    $this->assertDatabaseHas('pim_tax_classes', [
        'id' => $taxClass->id,
        'deleted_at' => null,
    ]);
});
