<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionsEnum::VIEW_EVENTS);
    }

    public function view(User $user, Event $event): bool
    {
        return $user->can(PermissionsEnum::VIEW_EVENTS);
    }

    public function preview(User $user, Event $event): bool
    {
        return $user->can(PermissionsEnum::PREVIEW_FORMS);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_EVENTS);
    }

    public function update(User $user, Event $event): bool
    {
        return $user->can(PermissionsEnum::MANAGE_EVENTS);
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->can(PermissionsEnum::MANAGE_EVENTS);
    }

    public function replicate(User $user, Event $event): bool
    {
        return $user->can(PermissionsEnum::MANAGE_EVENTS);
    }
}
