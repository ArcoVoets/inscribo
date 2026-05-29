<?php

use App\Listeners\SetNotificationMailer;
use App\Models\Event;
use App\Models\MailerSettings;
use App\Models\Registration;
use App\Notifications\RegistrationSubmittedPaymentPending;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\Config;

test('mailer settings configure per-event mailer and sender', function () {
    $mailerSettings = MailerSettings::factory()->create([
        'host' => 'smtp.test.example',
        'port' => 587,
        'username' => 'sender@example.com',
        'password' => 'secret',
        'from_address' => 'sender@example.com',
        'from_name' => 'Event Sender',
        'reply_to_address' => 'reply@example.com',
    ]);

    $event = Event::factory()->create([
        'mailer_settings_id' => $mailerSettings->id,
    ]);

    $registration = Registration::factory()->create([
        'event_id' => $event->id,
    ]);

    $notification = new RegistrationSubmittedPaymentPending($registration, now()->addHour());

    $listener = new SetNotificationMailer;
    $listener->handle(new NotificationSending($registration, $notification, 'mail'));

    $mailerName = $event->mailerName();

    expect(Config::get("mail.mailers.{$mailerName}"))->toMatchArray([
        'transport' => 'smtp',
        'host' => 'smtp.test.example',
        'port' => 587,
        'username' => 'sender@example.com',
        'password' => 'secret',
    ]);

    $message = $notification->toMail($registration);

    expect($message->mailer)->toBe($mailerName);
    expect($message->from)->toBe(['sender@example.com', 'Event Sender']);
    expect($message->replyTo)->toBe([['reply@example.com', null]]);
});

test('mailer settings are optional for notifications', function () {
    $event = Event::factory()->create();

    $registration = Registration::factory()->create([
        'event_id' => $event->id,
    ]);

    $notification = new RegistrationSubmittedPaymentPending($registration, now()->addHour());

    $message = $notification->toMail($registration);

    expect($message->mailer)->toBeNull();
    expect($message->from)->toBe([]);
    expect($message->replyTo)->toBe([]);
});
