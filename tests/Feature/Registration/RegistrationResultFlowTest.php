<?php

use App\Actions\SyncMolliePaymentStatus;
use App\Enums\RegistrationStates;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationPayment;
use Inertia\Testing\AssertableInertia as Assert;
use Mollie\Api\Resources\Payment;
use Tests\TestCase;

it('syncs payment status once on checkout return and redirects to canonical status URL when payment settles', function () {
    /** @var TestCase $this */
    $event = Event::factory()->create([
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addDay(),
        'capacity' => 25,
    ]);

    $registration = Registration::factory()
        ->paymentPending(isExpired: false)
        ->create([
            'event_id' => $event->id,
        ]);

    $registrationPayment = RegistrationPayment::factory()->create([
        'registration_id' => $registration->id,
    ]);

    $syncPaymentStatus = Mockery::mock(SyncMolliePaymentStatus::class);
    $syncPaymentStatus
        ->shouldReceive('execute')
        ->once()
        ->withArgs(fn (RegistrationPayment $payment): bool => $payment->is($registrationPayment))
        ->andReturnUsing(function () use ($registration): Payment {
            $registration->states()->create([
                'type' => RegistrationStates::Registered,
            ]);

            return Mockery::mock(Payment::class);
        });

    $this->app->instance(SyncMolliePaymentStatus::class, $syncPaymentStatus);

    $this->get($registration->signedStatusUrl(['from_checkout' => '1']))
        ->assertRedirect($registration->signedStatusUrl());
});

it('shows the checkout link for non-expired payment pending registrations', function () {
    /** @var TestCase $this */
    $event = Event::factory()->create([
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addDay(),
        'capacity' => 25,
    ]);

    $registration = Registration::factory()
        ->paymentPending(isExpired: false)
        ->create([
            'event_id' => $event->id,
        ]);

    $this->get($registration->signedStatusUrl())
        ->assertInertia(fn (Assert $page) => $page
            ->component('registration/status')
            ->where('status.state', RegistrationStates::PaymentPending->value)
            ->where('status.checkoutUrl', $registration->checkoutUrl($registration->currentState->expires_at))
        );
});

it('hides the checkout link for expired payment pending registrations', function () {
    /** @var TestCase $this */
    $event = Event::factory()->create([
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addDay(),
        'capacity' => 25,
    ]);

    $registration = Registration::factory()
        ->paymentPending(isExpired: true)
        ->create([
            'event_id' => $event->id,
        ]);

    $this->get($registration->signedStatusUrl())
        ->assertInertia(fn (Assert $page) => $page
            ->component('registration/status')
            ->where('status.checkoutUrl', null)
        );
});
