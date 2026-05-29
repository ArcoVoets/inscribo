<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ])->assignRole('admin');

        User::factory()->create([
            'name' => 'Manager',
            'email' => 'manager@example.com',
        ])->assignRole('manager');

        User::factory()->create([
            'name' => 'Guest',
            'email' => 'guest@example.com',
        ]);
    }
}
