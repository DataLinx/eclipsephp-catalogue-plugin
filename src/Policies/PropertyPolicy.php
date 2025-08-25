<?php

namespace Eclipse\Catalogue\Policies;

use Eclipse\Catalogue\Models\Property;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class PropertyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_property');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authorizable $user, Property $property): bool
    {
        return $user->can('view_property');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authorizable $user): bool
    {
        return $user->can('create_property');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authorizable $user, Property $property): bool
    {
        return $user->can('update_property');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authorizable $user, Property $property): bool
    {
        return $user->can('delete_property');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_property');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(Authorizable $user, Property $property): bool
    {
        return $user->can('force_delete_property');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_property');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(Authorizable $user, Property $property): bool
    {
        return $user->can('restore_property');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_property');
    }
}
