<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\RegistrationPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Mollie\Api\Types\PaymentStatus;

/**
 * @extends Factory<RegistrationPayment>
 */
class RegistrationPaymentFactory extends Factory
{
    protected $model = RegistrationPayment::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'mollie_payment_id' => 'tr_'.strtoupper(fake()->bothify('??##??##??##')),
            'status' => fake()->randomElement([
                PaymentStatus::OPEN,
                PaymentStatus::PENDING,
                PaymentStatus::PAID,
                PaymentStatus::FAILED,
                PaymentStatus::CANCELED,
                PaymentStatus::EXPIRED,
            ]),
            'occured_at' => fake()->optional(0.9)->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
