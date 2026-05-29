<?php

namespace Database\Factories;

use App\Enums\RegistrationStates;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationState;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function configure(): static
    {
        return $this->afterCreating(function (Registration $registration): void {
            if ($registration->states()->exists()) {
                return;
            }

            $registration->states()->create([
                'type' => RegistrationStates::Waitlisted,
            ]);

            // Add registration values for name, email and participant type
            $nameField = $registration->event->form->fields()->where('name', 'name')->firstOrFail();
            $emailField = $registration->event->form->fields()->where('name', 'email')->firstOrFail();
            $participantTypeField = $registration->event->form->fields()->where('name', 'participant_type')->firstOrFail();
            $participantTypeOption = $participantTypeField->options()->inRandomOrder()->limit(1)->value('id');
            $registration->registrationValues()->createMany([
                [
                    'field_id' => $nameField->id,
                    'value' => fake()->name(),
                ],
                [
                    'field_id' => $emailField->id,
                    'value' => fake()->email(),
                ],
                [
                    'field_id' => $participantTypeField->id,
                    'option_id' => $participantTypeOption,
                ],
            ]);
        });
    }

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'price_cents' => fake()->numberBetween(500, 3000),
            'manager_notes' => fake()->optional()->sentence(),
        ];
    }

    public function registered(): static
    {
        return $this->afterCreating(function (Registration $registration): void {
            $createdAt = now()->subMinutes(fake()->numberBetween(1, 500));

            $registration->states()->create([
                'type' => RegistrationStates::Registered,
                'amount_cents' => fake()->numberBetween(500, 3000),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        });
    }

    public function paymentPending($isExpired = null): static
    {
        $addHours = match ($isExpired) {
            null => fake()->numberBetween(-24, 24),
            true => fake()->numberBetween(-24, -1),
            false => fake()->numberBetween(1, 24),
        };

        return $this->afterCreating(fn (Registration $registration): RegistrationState => $registration
            ->states()->create([
                'type' => RegistrationStates::PaymentPending,
                'expires_at' => now()->addHours($addHours),
            ])
        );
    }

    public function cancelled(): static
    {
        return $this->afterCreating(function (Registration $registration): void {
            $registration->states()->create([
                'type' => RegistrationStates::Cancelled,
            ]);
        });
    }

    public function refunded(): static
    {
        return $this->afterCreating(function (Registration $registration): void {
            $amountCents = fake()->numberBetween(500, 3000);

            $registration->states()->create([
                'type' => RegistrationStates::Registered,
                'amount_cents' => $amountCents,
            ]);

            $registration->states()->create([
                'type' => RegistrationStates::Cancelled,
            ]);

            $registration->states()->create([
                'type' => RegistrationStates::Refunded,
                'amount_cents' => $amountCents,
            ]);
        });
    }

    public function waitlisted(): static
    {
        return $this->afterCreating(function (Registration $registration): void {
            $registration->states()->create([
                'type' => RegistrationStates::Waitlisted,
            ]);
        });
    }
}
