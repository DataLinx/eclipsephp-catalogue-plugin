<?php

namespace Eclipse\Catalogue\Policies;

use Eclipse\Catalogue\Models\PriceList;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class PriceListPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_price::list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authorizable $user, PriceList $priceList): bool
    {
        return $user->can('view_price::list');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authorizable $user): bool
    {
        return $user->can('create_price::list');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authorizable $user, PriceList $priceList): bool
    {
        return $user->can('update_price::list');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authorizable $user, PriceList $priceList): bool
    {
        // Prevent deletion of default price lists
        if ($priceList->is_default || $priceList->is_default_purchase) {
            return false;
        }

        return $user->can('delete_price::list');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_price::list');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Authorizable $user, PriceList $priceList): bool
    {
        return $user->can('restore_price::list');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_price::list');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Authorizable $user, PriceList $priceList): bool
    {
        // Prevent force deletion of default price lists
        if ($priceList->is_default || $priceList->is_default_purchase) {
            return false;
        }

        return $user->can('force_delete_price::list');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_price::list');
    }
}
