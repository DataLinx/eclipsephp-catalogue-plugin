<?php

namespace Eclipse\Catalogue\Policies;

use Eclipse\Catalogue\Models\TaxClass;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class TaxClassPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_tax::class');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authorizable $user, TaxClass $taxClass): bool
    {
        return $user->can('view_tax::class');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authorizable $user): bool
    {
        return $user->can('create_tax::class');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authorizable $user, TaxClass $taxClass): bool
    {
        return $user->can('update_tax::class');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authorizable $user, TaxClass $taxClass): bool
    {
        // Prevent deletion of default class
        if ($taxClass->is_default) {
            return false;
        }

        return $user->can('delete_tax::class');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_tax::class');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(Authorizable $user, TaxClass $taxClass): bool
    {
        // Prevent force deletion of default class
        if ($taxClass->is_default) {
            return false;
        }

        return $user->can('force_delete_tax::class');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_tax::class');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(Authorizable $user, TaxClass $taxClass): bool
    {
        return $user->can('restore_tax::class');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_tax::class');
    }
}
