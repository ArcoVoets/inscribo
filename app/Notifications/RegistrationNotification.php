<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Registration;
use Illuminate\Support\HtmlString;
use Override;

abstract class RegistrationNotification extends BaseNotification
{
    public function __construct(
        public readonly Registration $registration
    ) {}

    #[Override]
    public function getEvent(): Event
    {
        return $this->registration->event;
    }

    protected static function commonMergeTagLabels(): array
    {
        return [
            'name' => __('admin.events.form.merge_tags.name'),
            'event_title' => __('admin.events.form.merge_tags.event_title'),
            'status_url' => __('admin.events.form.merge_tags.status_url'),
            'status_button' => __('admin.events.form.merge_tags.status_button'),
        ];
    }

    protected static function commonMergeTags(Registration $registration): array
    {
        $statusUrl = $registration->publicStatusUrl();

        return [
            'name' => $registration->name(),
            'event_title' => $registration->event->title,
            'status_url' => $statusUrl,
            'status_button' => new HtmlString(view('mail.merge-tags.mail-button', [
                'url' => $statusUrl,
                'label' => __('mail.status.action'),
            ])->render()),
        ];
    }

    protected function allMergeTags(): array
    {
        return $this->commonMergeTags($this->registration)
             + $this->eventSpecificMergeTags($this->registration);
    }

    public static function allMergeTagLabels(): array
    {
        return static::commonMergeTagLabels() + static::eventSpecificMergeTagLabels();
    }

    protected static function eventSpecificMergeTagLabels(): array
    {
        return [];
    }

    protected function eventSpecificMergeTags(Registration $registration): array
    {
        return [];
    }
}
