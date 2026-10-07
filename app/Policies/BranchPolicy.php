<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

/**
 * Every signed-in user can browse branchs; only managers maintain the catalog.
 */
class BranchPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Branch $branch): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isManager();
    }

    public function update(User $user, Branch $branch): bool
    {
        return $user->isManager();
    }

    public function delete(User $user, Branch $branch): bool
    {
        return $user->isManager();
    }
}
