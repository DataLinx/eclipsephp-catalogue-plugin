<?php

use Eclipse\Catalogue\Filament\Resources\TaxClassResource\Pages\ListTaxClasses;
use Eclipse\Catalogue\Models\TaxClass;
use Eclipse\Catalogue\Policies\TaxClassPolicy;
use Livewire\Livewire;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->migrate();
    $this->setUpSuperAdminAndTenant();
});

it('policy prevents deletion of default class regardless of user permissions', function () {
    $user = User::factory()->create();

    $defaultClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    $policy = new TaxClassPolicy;
    $canDelete = $policy->delete($user, $defaultClass);

    expect($canDelete)->toBeFalse();
});

it('policy prevents force deletion of default class regardless of user permissions', function () {
    $user = User::factory()->create();

    $defaultClass = TaxClass::create([
        'name' => 'Standard Rate',
        'description' => 'Standard tax rate',
        'rate' => 20.00,
        'is_default' => true,
    ]);

    $policy = new TaxClassPolicy;
    $canForceDelete = $policy->forceDelete($user, $defaultClass);

    expect($canForceDelete)->toBeFalse();
});

test('unauthorized access can be prevented', function () {
    // Create regular user with no permissions
    $this->setUpCommonUser();

    // Create test tax class
    $site = \Workbench\App\Models\Site::first();
    $taxClass = TaxClass::create([
        'name' => 'Test Rate',
        'description' => 'Test tax rate',
        'rate' => 15.00,
        'is_default' => false,
    ]);

    // Authorization: user should not be able to view any
    expect((new TaxClassPolicy)->viewAny($this->user))->toBeFalse();

    // Add direct permission to view the table, since otherwise any other action below is not available even for testing
    $this->user->givePermissionTo('view_any_tax_class');

    // Test that user cannot view specific tax class
    $policy = new TaxClassPolicy;
    expect($policy->view($this->user, $taxClass))->toBeFalse();

    // Test that user cannot create tax classes
    expect($policy->create($this->user))->toBeFalse();

    // Test that user cannot update tax class
    expect($policy->update($this->user, $taxClass))->toBeFalse();

    // Test that user cannot delete tax class
    expect($policy->delete($this->user, $taxClass))->toBeFalse();

    // Test soft deletion
    $taxClass->delete();
    $this->assertSoftDeleted($taxClass);

    Livewire::test(ListTaxClasses::class)
        ->filterTable('trashed')
        ->assertTableActionExists('restore')
        ->assertTableActionExists('forceDelete')
        ->assertTableActionDisabled('restore', $taxClass)
        ->assertTableActionDisabled('forceDelete', $taxClass);
});
