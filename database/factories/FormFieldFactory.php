<?php

namespace Database\Factories;

use App\Enums\FormFieldType;
use App\Models\FormField;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    protected $model = FormField::class;

    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'name' => fake()->slug(),
            'type' => fake()->randomElement(FormFieldType::cases()),
            'placeholder' => fake()->boolean(50) ? fake()->sentence() : null,
            'required' => fake()->boolean(60),
        ];
    }
}
