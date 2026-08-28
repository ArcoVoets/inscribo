<?php

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\EventMailTemplate;
use App\Models\Registration;
use App\Notifications\RegistrationCompletedNotification;
use App\Notifications\RegistrationSubmittedPaymentPendingNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

test('a registration sends a confirmation email with signed status link', function () {
    /** @var TestCase $this */
    Notification::fake();

    $event = Event::factory()->create([
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addDay(),
        'capacity' => 25,
    ]);

    $payload = registrationPayload($event);

    $this
        ->post(route('events.register.store', ['event' => $event->id]), $payload)
        ->assertRedirect();

    $registration = $event->registrations()->firstOrFail();

    Notification::assertSentTo($registration, RegistrationSubmittedPaymentPendingNotification::class, function (RegistrationSubmittedPaymentPendingNotification $notification, array $channels, $notifiable) {
        expect($channels)->toContain('mail');
        expect($notifiable->routeNotificationFor('mail'))->toBe(['registrant@example.com' => 'Invited Registrant']);

        $url = $notification->registration->signedStatusUrl();
        expect($url)->toContain('signature=');

        return true;
    });
});

test('a completed registration notification sends all configured confirmation addresses as bcc', function () {
    $event = eventWithRegistrationForm([
        'confirmation_mail_addresses' => [
            'first@example.com',
            'second@example.com',
        ],
    ]);
    EventMailTemplate::factory()->create([
        'event_id' => $event->id,
        'type' => EventMailTemplateType::RegistrationCompleted,
    ]);
    /** @var Registration $registration */
    $registration = Registration::factory()->for($event)->create();

    $mail = (new RegistrationCompletedNotification($registration))->toMail($registration);

    expect($mail->bcc)->toBe([
        ['first@example.com', null],
        ['second@example.com', null],
    ]);
});
