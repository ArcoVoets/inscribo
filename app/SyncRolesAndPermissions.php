<?php

declare(strict_types=1);

namespace App;

use App\Enums\PermissionsEnum;
use App\Enums\RolesEnum;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncRolesAndPermissions
{
    public static function sync(): void
    {
        $managerPermissions = [
            PermissionsEnum::ACCESS_ADMIN_PANEL,
            PermissionsEnum::VIEW_REGISTRATIONS,
            PermissionsEnum::MANAGE_REGISTRATIONS,
            PermissionsEnum::MANAGE_INVITES,
            PermissionsEnum::VIEW_EVENTS,
            PermissionsEnum::PREVIEW_FORMS,
        ];

        $adminPermissions = array_merge($managerPermissions, [
            PermissionsEnum::MANAGE_USERS,
            PermissionsEnum::DELETE_FORMS,
            PermissionsEnum::MANAGE_EVENTS,
            PermissionsEnum::MANAGE_FORMS,
            PermissionsEnum::MANAGE_MAILER_SETTINGS,
            PermissionsEnum::MANAGE_API_KEYS,
            PermissionsEnum::DELETE_REGISTRATIONS,
        ]);

        $rolesAndPermissions = [
            RolesEnum::MANAGER->value => $managerPermissions,
            RolesEnum::ADMIN->value => $adminPermissions,
        ];

        foreach ($rolesAndPermissions as $role => $rolePermissions) {
            $role = Role::findOrCreate($role);

            $permissions = collect($rolePermissions)->map(fn ($permission): PermissionContract => Permission::findOrCreate($permission->value))->all();

            $role->syncPermissions($permissions);
        }

    }
}
