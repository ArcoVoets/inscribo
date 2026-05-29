<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_USERS);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can(PermissionsEnum::MANAGE_USERS);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_USERS);
    }

    public function update(User $user, User $model): bool
    {
        return $user->can(PermissionsEnum::MANAGE_USERS);
    }
}
