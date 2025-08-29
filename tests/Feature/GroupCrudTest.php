<?php

use Eclipse\Catalogue\Models\Group;

beforeEach(function () {
    $this->migrate();
});

it('can create a group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
        'is_browsable' => false,
    ]);

    expect($group)->toBeInstanceOf(Group::class);
    expect($group->code)->toBe('test-group');
    expect($group->name)->toBe('Test Group');
    expect($group->is_active)->toBeTrue();
    expect($group->is_browsable)->toBeFalse();

    $this->assertDatabaseHas('pim_group', [
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
        'is_browsable' => false,
    ]);
});

it('can update a group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
        'is_browsable' => false,
    ]);

    $group->update([
        'name' => 'Updated Test Group',
        'is_active' => false,
        'is_browsable' => true,
    ]);

    expect($group->name)->toBe('Updated Test Group');
    expect($group->is_active)->toBeFalse();
    expect($group->is_browsable)->toBeTrue();

    $this->assertDatabaseHas('pim_group', [
        'id' => $group->id,
        'name' => 'Updated Test Group',
        'is_active' => false,
        'is_browsable' => true,
    ]);
});

it('can delete a group', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    $group->delete();

    $this->assertDatabaseMissing('pim_group', [
        'id' => $group->id,
    ]);
});

it('can retrieve group with site relationship', function () {
    $group = Group::create([
        'site_id' => 1,
        'code' => 'test-group',
        'name' => 'Test Group',
        'is_active' => true,
    ]);

    // Test that the site_id is set correctly
    expect($group->site_id)->toBe(1);

    // Test that the group can be retrieved from database
    $retrievedGroup = Group::find($group->id);
    expect($retrievedGroup)->not->toBeNull();
    expect($retrievedGroup->site_id)->toBe(1);
});
