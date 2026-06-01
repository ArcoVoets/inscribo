<?php

use App\Models\Event;
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
