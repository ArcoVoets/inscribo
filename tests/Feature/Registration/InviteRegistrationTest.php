<?php

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
