<?php

use Eclipse\Catalogue\Filament\Resources\PropertyResource;
use Eclipse\Catalogue\Filament\Resources\PropertyResource\Pages\ListProperties;
use Eclipse\Catalogue\Models\Property;
use Workbench\App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->migrate();
});

it('unauthorized access can be prevented for custom properties', function () {
    // Create regular user with no permissions
    $this->setUpCommonUser();

    // Create test custom property
    $customProperty = Property::create([
        'name' => ['en' => 'Test Custom Property'],
        'code' => 'test_custom_property',
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // View table
    $this->get(PropertyResource::getUrl())
        ->assertForbidden();

    // Add direct permission to view the table, since otherwise any other action below is not available even for testing
    $this->user->givePermissionTo('view_any_property');

    // Create property
    livewire(ListProperties::class)
        ->assertActionDisabled('create');

    // Edit property
    livewire(ListProperties::class)
        ->assertCanSeeTableRecords([$customProperty])
        ->assertTableActionDisabled('edit', $customProperty);

    // Delete property
    livewire(ListProperties::class)
        ->assertTableActionDisabled('delete', $customProperty);
});

it('super admin can manage custom properties', function () {
    // Set up super admin
    $this->setUpSuperAdmin();

    // Create test custom property
    $customProperty = Property::create([
        'name' => ['en' => 'Test Custom Property'],
        'code' => 'test_custom_property',
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // View table
    $this->get(PropertyResource::getUrl())
        ->assertOk();

    // Create property
    livewire(ListProperties::class)
        ->assertActionEnabled('create');

    // Edit property
    livewire(ListProperties::class)
        ->assertCanSeeTableRecords([$customProperty])
        ->assertTableActionEnabled('edit', $customProperty);

    // Delete property
    livewire(ListProperties::class)
        ->assertTableActionEnabled('delete', $customProperty);
});

it('can create custom property with proper permissions', function () {
    // Set up super admin
    $this->setUpSuperAdmin();

    // Test creating a custom property
    $customProperty = Property::create([
        'name' => ['en' => 'Brand Name'],
        'code' => 'brand_name',
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
        'description' => ['en' => 'Product brand name'],
    ]);

    expect($customProperty)->toBeInstanceOf(Property::class);
    expect($customProperty->type)->toBe('custom');
    expect($customProperty->input_type)->toBe('string');
    expect($customProperty->is_multilang)->toBeTrue();

    $this->assertDatabaseHas('pim_property', [
        'id' => $customProperty->id,
        'code' => 'brand_name',
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => true,
    ]);
});

it('can update custom property with proper permissions', function () {
    // Set up super admin
    $this->setUpSuperAdmin();

    // Create test custom property
    $customProperty = Property::create([
        'name' => ['en' => 'Original Name'],
        'code' => 'original',
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Update the property
    $customProperty->update([
        'name' => ['en' => 'Updated Name'],
        'code' => 'updated',
        'input_type' => 'text',
        'is_multilang' => true,
    ]);

    expect($customProperty->getTranslation('name', 'en'))->toBe('Updated Name');
    expect($customProperty->code)->toBe('updated');
    expect($customProperty->input_type)->toBe('text');
    expect($customProperty->is_multilang)->toBeTrue();

    $this->assertDatabaseHas('pim_property', [
        'id' => $customProperty->id,
        'code' => 'updated',
        'input_type' => 'text',
        'is_multilang' => true,
    ]);
});

it('can delete custom property with proper permissions', function () {
    // Set up super admin
    $this->setUpSuperAdmin();

    // Create test custom property
    $customProperty = Property::create([
        'name' => ['en' => 'Test Custom Property'],
        'code' => 'test_custom_property',
        'type' => 'custom',
        'input_type' => 'string',
        'is_multilang' => false,
    ]);

    // Delete the property
    $customProperty->delete();

    $this->assertSoftDeleted('pim_property', [
        'id' => $customProperty->id,
    ]);
});
