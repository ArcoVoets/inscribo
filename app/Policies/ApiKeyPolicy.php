<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\ApiKey;
use App\Models\User;

class ApiKeyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_API_KEYS);
    }

    public function view(User $user, ApiKey $apiKey): bool
    {
        return $user->can(PermissionsEnum::MANAGE_API_KEYS);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_API_KEYS);
    }

    public function update(User $user, ApiKey $apiKey): bool
    {
        return $user->can(PermissionsEnum::MANAGE_API_KEYS);
    }

    public function delete(User $user, ApiKey $apiKey): bool
    {
        return $user->can(PermissionsEnum::MANAGE_API_KEYS);
    }
}
