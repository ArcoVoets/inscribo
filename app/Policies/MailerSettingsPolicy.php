<?php

namespace App\Policies;

use App\Enums\PermissionsEnum;
use App\Models\MailerSettings;
use App\Models\User;

class MailerSettingsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_MAILER_SETTINGS);
    }

    public function view(User $user, MailerSettings $mailerSettings): bool
    {
        return $user->can(PermissionsEnum::MANAGE_MAILER_SETTINGS);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_MAILER_SETTINGS);
    }

    public function update(User $user, MailerSettings $mailerSettings): bool
    {
        return $user->can(PermissionsEnum::MANAGE_MAILER_SETTINGS);
    }

    public function delete(User $user, MailerSettings $mailerSettings): bool
    {
        return $user->can(PermissionsEnum::MANAGE_MAILER_SETTINGS);
    }

    public function deleteAny(User $user): bool
    {
        return $user->can(PermissionsEnum::MANAGE_MAILER_SETTINGS);
    }
}
