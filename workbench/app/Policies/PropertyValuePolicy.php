<?php

declare(strict_types=1);

namespace Workbench\App\Policies;

use Eclipse\Catalogue\Models\PropertyValue;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PropertyValuePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_property_value');
    }

    public function view(AuthUser $authUser, PropertyValue $propertyValue): bool
    {
        return $authUser->can('view_property_value');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_property_value');
    }

    public function update(AuthUser $authUser, PropertyValue $propertyValue): bool
    {
        return $authUser->can('update_property_value');
    }

    public function restore(AuthUser $authUser, PropertyValue $propertyValue): bool
    {
        return $authUser->can('restore_property_value');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_property_value');
    }

    public function replicate(AuthUser $authUser, PropertyValue $propertyValue): bool
    {
        return $authUser->can('replicate_property_value');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_property_value');
    }

    public function delete(AuthUser $authUser, PropertyValue $propertyValue): bool
    {
        return $authUser->can('delete_property_value');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any_property_value');
    }

    public function forceDelete(AuthUser $authUser, PropertyValue $propertyValue): bool
    {
        return $authUser->can('force_delete_property_value');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_property_value');
    }
}
