<?php

namespace Database\Factories;

use App\Models\FormSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormSection>
 */
class FormSectionFactory extends Factory
{
    protected $model = FormSection::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(2),
        ];
    }
}
