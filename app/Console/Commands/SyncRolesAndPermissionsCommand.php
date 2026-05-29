<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\SyncRolesAndPermissions;
use Illuminate\Console\Command;

class SyncRolesAndPermissionsCommand extends Command
{
    protected $signature = 'app:sync-roles-and-permissions';

    protected $description = 'Sync the roles and permissions, meant to be ran on deployments';

    public function handle(): void
    {
        SyncRolesAndPermissions::sync();
        $this->info('Roles and permissions synced successfully.');
    }
}
