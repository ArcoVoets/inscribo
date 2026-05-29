<?php

namespace App\Notifications;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\Invite;
use Illuminate\Support\HtmlString;
use Override;

class InvitedToRegisterNotification extends BaseNotification
{
    public function __construct(
        public readonly Invite $invite,
    ) {}

    #[Override]
    public function getEvent(): Event
    {
        return $this->invite->event;
    }

    #[Override]
    public static function templateType(): EventMailTemplateType
    {
        return EventMailTemplateType::InvitedToRegister;
    }

    #[Override]
    public static function defaultTemplateSubject(Event $event): string
    {
        return __('mail.invited_to_register.subject', [
            'event_title' => $event->title,
        ]);
    }

    protected static function eventSpecificMergeTagLabels(): array
    {
        return [
            'name' => __('admin.events.form.merge_tags.name'),
            'expires_at_sentence' => __('admin.events.form.merge_tags.expires_at_sentence'),
            'event_title' => __('admin.events.form.merge_tags.event_title'),
            'register_button' => __('admin.events.form.merge_tags.register_button'),
        ];
    }

    #[Override]
    public static function allMergeTagLabels(): array
    {
        return static::eventSpecificMergeTagLabels();
    }

    protected function allMergeTags(): array
    {
        return $this->eventSpecificMergeTags($this->invite);
    }

    protected function eventSpecificMergeTags(Invite $invite): array
    {
        $expiresAtSentence = $invite->expires_at
            ? __('mail.invited_to_register.expires_at_sentence', [
                'expires_at' => $invite->expires_at->tz(config('app.display_timezone'))->toDateTimeString(),
            ]) : '';

        return [
            'name' => $invite->name,
            'event_title' => $this->getEvent()->title,
            'register_button' => new HtmlString(view('mail.merge-tags.mail-button', [
                'url' => $invite->url(),
                'label' => __('mail.invited_to_register.register_button'),
            ])->render()),
            'expires_at_sentence' => $expiresAtSentence,
        ];
    }

    #[Override]
    public static function defaultTemplateContent(Event $event): array
    {
        $json = __('mail-templates.invited_to_register');

        return json_decode($json, true);
    }
}
