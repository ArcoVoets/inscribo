<?php

namespace Database\Factories;

use App\Models\Form;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    protected $model = Form::class;

    public function definition(): array
    {
        return [
            'base_price_cents' => fake()->randomElement([0, 500, 1000]),
            'title' => fake()->sentence(3),
            'description' => fake()->boolean(60) ? fake()->paragraph() : null,
        ];
    }
}
