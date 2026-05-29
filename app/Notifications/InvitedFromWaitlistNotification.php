<?php

namespace App\Notifications;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\Registration;
use Carbon\CarbonInterface;
use Override;

class InvitedFromWaitlistNotification extends RegistrationNotification
{
    public function __construct(
        public readonly Registration $registration,
        public readonly CarbonInterface $paymentExpiresAt,
    ) {}

    #[Override]
    public static function templateType(): EventMailTemplateType
    {
        return EventMailTemplateType::InvitedFromWaitlist;
    }

    #[Override]
    public static function defaultTemplateSubject(Event $event): string
    {
        return __('mail.invited_from_waitlist.subject', [
            'event_title' => $event->title,
        ]);
    }

    protected static function eventSpecificMergeTagLabels(): array
    {
        return [
            'expires_at' => __('admin.events.form.merge_tags.expires_at'),
        ];
    }

    protected function eventSpecificMergeTags(Registration $registration): array
    {
        return [
            'expires_at' => $this->paymentExpiresAt?->tz(config('app.display_timezone'))->toDateTimeString(),
        ];
    }

    #[Override]
    public static function defaultTemplateContent(Event $event): array
    {
        $json = __('mail-templates.invited_from_waitlist');

        return json_decode($json, true);
    }
}
