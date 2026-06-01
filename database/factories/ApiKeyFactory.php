<?php

namespace Database\Factories;

use App\Models\ApiKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * @return string[]
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'key' => $this->faker->sha256(),
        ];
    }
}
