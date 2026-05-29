<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Invite;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invite>
 */
class InviteFactory extends Factory
{
    protected $model = Invite::class;

    public function definition(): array
    {
        $token = Invite::generateToken();

        return [
            'event_id' => Event::factory(),
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'token' => $token,
            'expires_at' => now()->addDay(),
            'revoked_at' => null,
            'used_at' => null,
            'used_registration_id' => null,
        ];
    }
}
