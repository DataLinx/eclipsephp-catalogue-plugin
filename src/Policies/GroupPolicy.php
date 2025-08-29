<?php

namespace Eclipse\Catalogue\Policies;

use Eclipse\Catalogue\Models\Group;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Access\Authorizable;

class GroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(Authorizable $user): bool
    {
        return $user->can('view_any_group');
    }

    public function view(Authorizable $user, Group $group): bool
    {
        return $user->can('view_group');
    }

    public function create(Authorizable $user): bool
    {
        return $user->can('create_group');
    }

    public function update(Authorizable $user, Group $group): bool
    {
        return $user->can('update_group');
    }

    public function delete(Authorizable $user, Group $group): bool
    {
        return $user->can('delete_group');
    }

    public function deleteAny(Authorizable $user): bool
    {
        return $user->can('delete_any_group');
    }

    public function forceDelete(Authorizable $user, Group $group): bool
    {
        return $user->can('force_delete_group');
    }

    public function forceDeleteAny(Authorizable $user): bool
    {
        return $user->can('force_delete_any_group');
    }

    public function restore(Authorizable $user, Group $group): bool
    {
        return $user->can('restore_group');
    }

    public function restoreAny(Authorizable $user): bool
    {
        return $user->can('restore_any_group');
    }
}
