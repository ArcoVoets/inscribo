<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionsEnum::VIEW_REGISTRATIONS);
    }

    public function view(User $user, Registration $registration): bool
    {
        return $user->can(PermissionsEnum::VIEW_REGISTRATIONS);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_REGISTRATIONS);
    }

    public function update(User $user, Registration $registration): bool
    {
        return $user->can(PermissionsEnum::MANAGE_REGISTRATIONS);
    }

    public function delete(User $user, Registration $registration): bool
    {
        return $user->can(PermissionsEnum::DELETE_REGISTRATIONS);
    }
}
