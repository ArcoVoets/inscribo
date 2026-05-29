<?php

use App\Enums\RegistrationStates;
use App\Filament\Resources\Events\Pages\ManageEventRegistrations;
use App\Models\Event;
use App\Models\Registration;
use App\Models\RegistrationStates\PaymentExpiredState;
use App\Models\RegistrationStates\PaymentPendingState;
use Tests\TestCase;

it('counts expired payment pending registrations as payment expired', function () {
    /** @var TestCase $this */
    $event = Event::factory()->create();

    $expiredRegistration = Registration::factory()
        ->paymentPending()
        ->create([
            'event_id' => $event->id,
        ]);

    $expiredRegistration->currentState->update([
        'expires_at' => now()->subMinute(),
    ]);

    $pendingRegistration = Registration::factory()
        ->paymentPending()
        ->create([
            'event_id' => $event->id,
        ]);

    $pendingRegistration->currentState->update([
        'expires_at' => now()->addMinute(),
    ]);

    Registration::factory()
        ->registered()
        ->create([
            'event_id' => $event->id,
        ]);

    $page = app()->make(ManageEventRegistrations::class);
    $page->record = $event;

    $tabs = $page->getTabs();

    expect($tabs[RegistrationStates::PaymentExpired->value]->getBadge())->toBe('1');
    expect($tabs[RegistrationStates::PaymentPending->value]->getBadge())->toBe('1');
});

it('returns a payment expired current state for expired payment pending', function () {
    /** @var TestCase $this */
    $registration = Registration::factory()
        ->paymentPending()
        ->create();

    $registration->currentState->update([
        'expires_at' => now()->subMinute(),
    ]);

    $registration->refresh();

    expect($registration->currentState)->toBeInstanceOf(PaymentExpiredState::class);
    expect($registration->currentState->type)->toBe(RegistrationStates::PaymentExpired);
});

it('does not persist payment expired type on save', function () {
    /** @var TestCase $this */
    $registration = Registration::factory()
        ->paymentPending()
        ->create();

    $registration->currentState->update([
        'expires_at' => now()->subMinute(),
    ]);

    $registration->refresh();

    expect($registration->currentState)->toBeInstanceOf(PaymentExpiredState::class);

    $registration->currentState->update([
        'expires_at' => now()->addMinutes(10),
    ]);

    $registration->refresh();

    expect($registration->currentState)->toBeInstanceOf(PaymentPendingState::class);
    expect($registration->currentState->type)->toBe(RegistrationStates::PaymentPending);
});

it('counts registrations by latest state id, not created_at', function () {
    /** @var TestCase $this */
    $event = Event::factory()->create();

    $registration = Registration::factory()->create([
        'event_id' => $event->id,
    ]);

    $registration->states()->create([
        'type' => RegistrationStates::Registered,
        'amount_cents' => 1500,
        'created_at' => now()->subDay(),
        'updated_at' => now()->subDay(),
    ]);

    $page = app()->make(ManageEventRegistrations::class);
    $page->record = $event;

    $tabs = $page->getTabs();

    expect($tabs[RegistrationStates::Registered->value]->getBadge())->toBe('1');
    expect($tabs[RegistrationStates::Waitlisted->value]->getBadge())->toBe('0');
});
