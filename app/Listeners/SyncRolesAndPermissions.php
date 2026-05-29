<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\Events\NoPendingMigrations;

class SyncRolesAndPermissions
{
    public function handle(NoPendingMigrations|MigrationsEnded $event): void
    {
        \App\SyncRolesAndPermissions::sync();
        if (! app()->environment('testing')) {
            echo "Roles and permissions synced successfully.\n";
        }
    }
}
