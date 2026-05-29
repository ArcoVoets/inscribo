<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Form;
use App\Models\User;

class FormPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_FORMS);
    }

    public function view(User $user, Form $form): bool
    {
        return $user->can(PermissionsEnum::MANAGE_FORMS);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_FORMS);
    }

    public function update(User $user, Form $form): bool
    {
        return $user->can(PermissionsEnum::MANAGE_FORMS);
    }

    public function delete(User $user, Form $form): bool
    {
        return $user->can(PermissionsEnum::DELETE_FORMS);
    }
}
