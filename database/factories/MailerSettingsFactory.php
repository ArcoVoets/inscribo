<?php

namespace Database\Factories;

use App\Models\MailerSettings;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MailerSettings>
 */
class MailerSettingsFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'host' => fake()->domainName(),
            'port' => fake()->randomElement([25, 465, 587]),
            'username' => fake()->safeEmail(),
            'password' => 'secret-password',
            'from_address' => fake()->safeEmail(),
            'from_name' => fake()->company(),
            'reply_to_address' => fake()->safeEmail(),
        ];
    }
}
