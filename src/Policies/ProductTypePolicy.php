<?php

namespace Eclipse\Catalogue\Policies;

use Eclipse\Catalogue\Models\ProductType;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class ProductTypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_product::type');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authorizable $user, ProductType $productType): bool
    {
        return $user->can('view_product::type');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authorizable $user): bool
    {
        return $user->can('create_product::type');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authorizable $user, ProductType $productType): bool
    {
        return $user->can('update_product::type');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authorizable $user, ProductType $productType): bool
    {
        return $user->can('delete_product::type');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_product::type');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(Authorizable $user, ProductType $productType): bool
    {
        return $user->can('force_delete_product::type');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_product::type');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(Authorizable $user, ProductType $productType): bool
    {
        return $user->can('restore_product::type');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_product::type');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(Authorizable $user, ProductType $productType): bool
    {
        return $user->can('{{ Replicate }}');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(Authorizable $user): bool
    {
        return $user->can('{{ Reorder }}');
    }
}
