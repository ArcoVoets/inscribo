<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\FormSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $opensAt = now()->addDays(fake()->numberBetween(-7, 14));

        return [
            'title' => fake()->sentence(3),
            'capacity' => fake()->numberBetween(5, 50),
            'opens_at' => $opensAt,
            'closes_at' => fake()->boolean(80) ? $opensAt->copy()->addDays(fake()->numberBetween(1, 30)) : null,
            'show_waitlist_position' => fake()->boolean(),
            'show_capacity_data' => fake()->boolean(),
            'home_url' => fake()->boolean(70) ? fake()->url() : null,
            'registration_expiration_minutes' => fake()->randomElement([15, 30, 60, 120]),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Event $event) {
            // Create a default form for the event with one section, a text field and a select field
            $form = $event->fresh()->form;
            $form->update([
                'title' => $event->title.' Registration',
            ]);

            $section = FormSection::factory()->create([
                'form_id' => $form->id,
                'title' => 'Main',
                'sort_order' => 0,
            ]);

            $nameField = FormField::factory()->create([
                'section_id' => $section->id,
                'label' => 'Name',
                'name' => 'name',
                'type' => 'text',
                'required' => true,
                'sort_order' => 0,
            ]);

            $emailField = FormField::factory()->create([
                'section_id' => $section->id,
                'label' => 'Email',
                'name' => 'email',
                'type' => 'email',
                'required' => true,
                'sort_order' => 0,
            ]);

            $form->update([
                'name_field_id' => $nameField->id,
                'email_field_id' => $emailField->id,
            ]);

            $selectField = FormField::factory()->create([
                'section_id' => $section->id,
                'label' => 'Participant type',
                'name' => 'participant_type',
                'type' => 'select',
                'required' => true,
                'sort_order' => 1,
            ]);

            FormFieldOption::factory()->createMany([
                ['field_id' => $selectField->id, 'label' => 'Student', 'value' => 'student', 'price_cents' => 0, 'sort_order' => 0],
                ['field_id' => $selectField->id, 'label' => 'Worker', 'value' => 'worker', 'price_cents' => 1500, 'sort_order' => 1],
            ]);

            $event->save();
        });
    }
}
