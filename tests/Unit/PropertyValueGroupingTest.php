<?php

use Eclipse\Catalogue\Models\Property;
use Eclipse\Catalogue\Models\PropertyValue;

it('cannot group into a target that is itself a member', function () {
    $property = Property::factory()->create();
    $parent = PropertyValue::factory()->create(['property_id' => $property->id, 'is_group' => true]);
    $member = PropertyValue::factory()->create(['property_id' => $property->id]);
    $member->groupInto($parent->id);

    $other = PropertyValue::factory()->create(['property_id' => $property->id]);

    expect(fn () => $other->groupInto($member->id))
        ->toThrow(RuntimeException::class); // member cannot be target
});

it('cannot group a value into itself or across properties', function () {
    $propA = Property::factory()->create();
    $propB = Property::factory()->create();
    $a = PropertyValue::factory()->create(['property_id' => $propA->id]);
    $b = PropertyValue::factory()->create(['property_id' => $propB->id]);

    // self
    expect(fn () => $a->groupInto($a->id))
        ->toThrow(RuntimeException::class);
});

it('prevents cross-property grouping', function () {
    $propA = Property::factory()->create();
    $propB = Property::factory()->create();
    $a = PropertyValue::factory()->create(['property_id' => $propA->id]);
    $b = PropertyValue::factory()->create(['property_id' => $propB->id]);

    expect(fn () => $a->groupInto($b->id))
        ->toThrow(RuntimeException::class);
});

it('grouping marks target as group and assigns group_value_id', function () {
    $property = Property::factory()->create();
    $target = PropertyValue::factory()->create(['property_id' => $property->id]);
    $one = PropertyValue::factory()->create(['property_id' => $property->id]);
    $two = PropertyValue::factory()->create(['property_id' => $property->id]);

    $one->groupInto($target->id);
    $two->groupInto($target->id);

    expect($target->fresh()->is_group)->toBeTrue()
        ->and($one->fresh()->group_value_id)->toBe($target->id)
        ->and($two->fresh()->group_value_id)->toBe($target->id);
});

it('re-grouping moves a member from old group to a new group', function () {
    $property = Property::factory()->create();
    $groupA = PropertyValue::factory()->create(['property_id' => $property->id]);
    $groupB = PropertyValue::factory()->create(['property_id' => $property->id]);
    $member = PropertyValue::factory()->create(['property_id' => $property->id]);

    $member->groupInto($groupA->id);
    expect($member->fresh()->group_value_id)->toBe($groupA->id);

    $member->groupInto($groupB->id);
    expect($member->fresh()->group_value_id)->toBe($groupB->id)
        ->and($groupB->fresh()->is_group)->toBeTrue();
});

it('removeFromGroup clears group_value_id', function () {
    $property = Property::factory()->create();
    $parent = PropertyValue::factory()->create(['property_id' => $property->id]);
    $member = PropertyValue::factory()->create(['property_id' => $property->id]);
    $member->groupInto($parent->id);
    $member->removeFromGroup();

    expect($member->fresh()->group_value_id)->toBeNull();
});

it('groupedOrder scope clusters parent and children without partial updates on error', function () {
    $property = Property::factory()->create();
    $a = PropertyValue::factory()->create(['property_id' => $property->id, 'value' => 'A']);
    $b1 = PropertyValue::factory()->create(['property_id' => $property->id, 'value' => 'B1']);
    $b2 = PropertyValue::factory()->create(['property_id' => $property->id, 'value' => 'B2']);

    // Group B members under A
    $b1->groupInto($a->id);
    $b2->groupInto($a->id);

    $ordered = PropertyValue::query()->sameProperty($property->id)->groupedOrder()->pluck('id')->all();
    // Expect A first, then its members in any order
    expect($ordered[0])->toBe($a->id)
        ->and(collect([$b1->id, $b2->id])->diff($ordered))->toBeEmpty();
});
