<?php

namespace Database\Seeders;

use App\Console\Commands\SyncRolesAndPermissionsCommand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call(SyncRolesAndPermissionsCommand::class);

        $this->call([
            UserSeeder::class,
            EventSeeder::class,
            RegistrationSeeder::class,
            RegistrationPaymentSeeder::class,
        ]);
    }
}
