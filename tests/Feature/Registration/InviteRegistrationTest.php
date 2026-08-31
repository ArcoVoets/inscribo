<?php

use App\Models\Event;
use App\Models\Invite;
use App\Models\Registration;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

it('allows a valid invite to bypass opens_at (but not closes_at)', function () {
    /** @var TestCase $this */
    $event = eventWithRegistrationForm([
        'opens_at' => now()->addDay(),
        'closes_at' => now()->addDays(10),
        'capacity' => 25,
    ]);

    $invite = Invite::factory()->create([
        'event_id' => $event->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->get(route('events.register', [
        'event' => $event->id,
        'invite_id' => $invite->id,
        'invite_token' => $invite->token,
    ]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('registration/create')
        );

    $eventClosed = eventWithRegistrationForm([
        'opens_at' => now()->subDay(),
        'closes_at' => now()->subMinute(),
        'capacity' => 25,
    ]);

    $inviteForClosed = Invite::factory()->create([
        'event_id' => $eventClosed->id,
        'expires_at' => now()->addDay(),
    ]);

    $this->get(route('events.register', [
        'event' => $eventClosed->id,
        'invite_id' => $inviteForClosed->id,
        'invite_token' => $inviteForClosed->token,
    ]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('registration/closed')
            ->where('status', 'closed')
        );
});

it('consumes an invite exactly once when registration is created', function () {
    /** @var TestCase $this */
    $event = eventWithRegistrationForm([
        'opens_at' => now()->addDay(),
        'closes_at' => now()->addDays(10),
        'capacity' => 25,
    ]);

    $invite = Invite::factory()->create([
        'event_id' => $event->id,
        'expires_at' => now()->addDay(),
    ]);

    $payload = registrationPayload($event, $invite, 'invited@example.com');

    $this
        ->post(route('events.register.store', ['event' => $event->id]), $payload)
        ->assertRedirect();

    $registration = Registration::query()
        ->where('event_id', $event->id)
        ->whereHas('registrationValues', fn (Builder $query): Builder => $query
            ->where('field_id', fieldId($event, 'email'))
            ->where('value', 'invited@example.com')
        )
        ->sole();

    $invite->refresh();
    expect($invite->used_at)->not->toBeNull();
    expect($invite->used_registration_id)->toBe($registration->id);

    // Second attempt must not bypass opens_at anymore.
    $this->post(route('events.register.store', ['event' => $event->id]), $payload)
        ->assertSessionHasErrors(['event']);
});

it('does not accept expired invites to bypass opens_at', function () {
    /** @var TestCase $this */
    $event = eventWithRegistrationForm([
        'opens_at' => now()->addDay(),
        'closes_at' => now()->addDays(10),
        'capacity' => 25,
    ]);

    $invite = Invite::factory()->create([
        'event_id' => $event->id,
        'expires_at' => now()->subMinute(),
    ]);

    $payload = registrationPayload($event, $invite);

    $this->post(route('events.register.store', ['event' => $event->id]), $payload)
        ->assertSessionHasErrors(['event']);
});

it('passes registration form colors to the registration page', function () {
    /** @var TestCase $this */
    $event = eventWithRegistrationForm([
        'accent_color_title_and_button' => '#112233',
        'accent_color_required_and_hover' => '#445566',
        'accent_color_label_and_radio' => '#778899',
        'accent_color_section_title' => '#aabbcc',
    ]);

    $this->get(route('events.register', ['event' => $event->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('registration/create')
            ->where('event.accentColorTitleAndButton', '#112233')
            ->where('event.accentColorRequiredAndHover', '#445566')
            ->where('event.accentColorLabelAndRadio', '#778899')
            ->where('event.accentColorSectionTitle', '#aabbcc')
        );
});

it('uses default capacity warning copy for new events and republishes the custom values when copied', function () {
    /** @var TestCase $this */
    $event = Event::factory()->create([
        'opens_at' => now()->subDay(),
        'closes_at' => null,
    ]);

    expect($event->capacity_full_title)->toBe('Capacity is full')
        ->and($event->capacity_full_description)->toBe('You can still register, but you will be placed on the waitlist.')
        ->and($event->waitlist_active_title)->toBe('Waitlist active')
        ->and($event->waitlist_active_description)->toBe('There is currently a waitlist. New registrations will be placed on the waitlist.');

    $customEvent = Event::factory()->create([
        'opens_at' => now()->subWeek(),
        'closes_at' => null,
        'capacity_full_title' => 'Sold out',
        'capacity_full_description' => 'You may still join the queue.',
        'waitlist_active_title' => 'Queue active',
        'waitlist_active_description' => 'We will reach out if a spot opens up.',
    ]);

    $copy = $customEvent->replicate();
    $copy->save();

    expect($copy->capacity_full_title)->toBe('Sold out')
        ->and($copy->capacity_full_description)->toBe('You may still join the queue.')
        ->and($copy->waitlist_active_title)->toBe('Queue active')
        ->and($copy->waitlist_active_description)->toBe('We will reach out if a spot opens up.');

    $this->get(route('events.register', ['event' => $customEvent->id]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('registration/create')
            ->where('event.capacityFullTitle', 'Sold out')
            ->where('event.capacityFullDescription', 'You may still join the queue.')
            ->where('event.waitlistActiveTitle', 'Queue active')
            ->where('event.waitlistActiveDescription', 'We will reach out if a spot opens up.')
        );
});
