<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('app:create-admin-user')]
#[Description('Create an admin user')]
class CreateAdminUser extends Command
{
    public function handle()
    {
        $email = $this->ask('Enter email for admin user');
        if (User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists.');

            return;
        }

        $password = $this->secret('Enter password for admin user');
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long.');

            return;
        }

        $name = str($email)->before('@')->replace('.', ' ')->title();
        $name = $this->ask('Enter name for admin user', default: $name);

        $user = User::create([
            'email' => $email,
            'name' => $name,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('admin');

        $this->info("Admin user created with email: {$email}");
        $this->info('You can now log in with the provided email and password.');
    }
}
