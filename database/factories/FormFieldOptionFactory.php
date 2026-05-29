<?php

namespace Database\Factories;

use App\Models\FormFieldOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormFieldOption>
 */
class FormFieldOptionFactory extends Factory
{
    protected $model = FormFieldOption::class;

    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'value' => fake()->slug(),
            'price_cents' => fake()->randomElement([0, 230, 500, 560, 970, 1000]),
        ];
    }
}
