<?php

use Eclipse\Catalogue\Filament\Resources\PropertyResource;
use Eclipse\Catalogue\Filament\Resources\PropertyResource\Pages\ListProperties;
use Eclipse\Catalogue\Models\Property;
use Workbench\App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->migrate();
});

test('unauthorized access can be prevented', function () {
    // Create regular user with no permissions
    $this->setUpCommonUser();

    // Create test property
    $property = Property::factory()->create([
        'name' => ['en' => 'Test Property'],
        'is_active' => true,
        'is_global' => false,
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
        ->assertCanSeeTableRecords([$property])
        ->assertTableActionDisabled('edit', $property);

    // Delete property
    livewire(ListProperties::class)
        ->assertTableActionDisabled('delete', $property);

    // Test delete action
    livewire(ListProperties::class)
        ->assertTableActionDisabled('delete', $property);
});
