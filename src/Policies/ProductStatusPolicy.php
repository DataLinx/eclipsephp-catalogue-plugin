<?php

namespace Eclipse\Catalogue\Policies;

use Eclipse\Catalogue\Models\ProductStatus;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class ProductStatusPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_product::status');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authorizable $user, ProductStatus $productStatus): bool
    {
        return $user->can('view_product::status');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authorizable $user): bool
    {
        return $user->can('create_product::status');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authorizable $user, ProductStatus $productStatus): bool
    {
        return $user->can('update_product::status');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authorizable $user, ProductStatus $productStatus): bool
    {
        // Prevent deletion of default status
        if ($productStatus->is_default) {
            return false;
        }

        return $user->can('delete_product::status');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_product::status');
    }
}
