<?php

use Eclipse\Catalogue\Filament\Resources\GroupResource;
use Eclipse\Catalogue\Filament\Resources\GroupResource\Pages\ListGroups;
use Eclipse\Catalogue\Models\Group;
use Eclipse\Catalogue\Policies\GroupPolicy;
use Workbench\App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->migrate();
});

it('policy allows deletion of regular group with proper permissions', function () {
    $user = User::factory()->create();
    $user->givePermissionTo('delete_group');

    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $policy = new GroupPolicy;
    $canDelete = $policy->delete($user, $group);

    expect($canDelete)->toBeTrue();
});

it('policy prevents deletion without proper permissions', function () {
    $user = User::factory()->create();

    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $policy = new GroupPolicy;
    $canDelete = $policy->delete($user, $group);

    expect($canDelete)->toBeFalse();
});

test('unauthorized access can be prevented', function () {
    // Create regular user with no permissions
    $this->setUpCommonUser();

    // Create test group
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    // View table
    $this->get(GroupResource::getUrl())
        ->assertForbidden();

    // Add direct permission to view the table, since otherwise any other action below is not available even for testing
    $this->user->givePermissionTo('view_any_group');

    // Create group
    livewire(ListGroups::class)
        ->assertActionDisabled('create');

    // Edit group
    livewire(ListGroups::class)
        ->assertCanSeeTableRecords([$group])
        ->assertTableActionDisabled('edit', $group);

    // Delete group
    livewire(ListGroups::class)
        ->assertTableActionDisabled('delete', $group);
});
