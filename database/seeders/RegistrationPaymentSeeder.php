<?php

namespace Database\Seeders;

use App\Enums\RegistrationStates;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use Illuminate\Database\Seeder;
use Mollie\Api\Types\PaymentStatus;

class RegistrationPaymentSeeder extends Seeder
{
    public function run(): void
    {
        if (! Registration::query()->exists()) {
            $this->call(RegistrationSeeder::class);
        }

        $registrations = Registration::query()->with('currentState')->get();

        foreach ($registrations as $registration) {
            $currentType = $registration->currentState?->type;

            if ($currentType === RegistrationStates::Waitlisted->value) {
                continue;
            }

            if (in_array($currentType, [RegistrationStates::Registered->value, RegistrationStates::Cancelled->value, RegistrationStates::Refunded->value], true)) {
                RegistrationPayment::factory()
                    ->for($registration)
                    ->state([
                        'status' => PaymentStatus::EXPIRED,
                    ])
                    ->create();
            }

            $latestStatus = match ($currentType) {
                RegistrationStates::PaymentPending->value => PaymentStatus::OPEN,
                RegistrationStates::Registered->value,
                RegistrationStates::Cancelled->value,
                RegistrationStates::Refunded->value => PaymentStatus::PAID,
                default => PaymentStatus::OPEN,
            };

            RegistrationPayment::factory()
                ->for($registration)
                ->state([
                    'status' => $latestStatus,
                    'occured_at' => now()->subHours(fake()->numberBetween(0, 48)),
                ])
                ->create();
        }
    }
}
