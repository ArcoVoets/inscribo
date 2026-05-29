<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Invite;
use App\Models\User;

class InvitePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_INVITES);
    }

    public function view(User $user, Invite $invite): bool
    {
        return $user->can(PermissionsEnum::MANAGE_INVITES);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_INVITES);
    }

    public function update(User $user, Invite $invite): bool
    {
        return $user->can(PermissionsEnum::MANAGE_INVITES);
    }

    public function delete(User $user, Invite $invite): bool
    {
        return $user->can(PermissionsEnum::MANAGE_INVITES);
    }

    public function restore(User $user, Invite $invite): bool
    {
        return $user->can(PermissionsEnum::MANAGE_INVITES);
    }

    public function forceDelete(User $user, Invite $invite): bool
    {
        return $user->can(PermissionsEnum::MANAGE_INVITES);
    }
}
