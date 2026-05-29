<?php

namespace App\Notifications;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Support\HtmlString;
use Override;

class RegistrationCompletedNotification extends RegistrationNotification
{
    use Queueable;

    public static function templateType(): EventMailTemplateType
    {
        return EventMailTemplateType::RegistrationCompleted;
    }

    #[Override]
    public static function defaultTemplateSubject(Event $event): string
    {
        return __('mail.registration_completed.subject', [
            'event_title' => $event->title,
        ]);
    }

    protected static function eventSpecificMergeTagLabels(): array
    {
        return [
            'registration_details' => __('admin.events.form.merge_tags.registration_details'),
            'pricing_details' => __('admin.events.form.merge_tags.pricing_details'),
        ];
    }

    protected function eventSpecificMergeTags(Registration $registration): array
    {
        return [
            'registration_details' => new HtmlString(view('mail.merge-tags.registration-details', [
                'eventTitle' => $this->registration->event->title,
                'fields' => $this->registration->registrationValues,
            ])->render()),
            'pricing_details' => new HtmlString(view('mail.merge-tags.pricing-details', [
                'priceRows' => $this->priceRows(),
                'totalPriceCents' => $this->registration->price_cents,
            ])->render()),
        ];
    }

    protected function priceRows(): array
    {
        $priceRows = [];
        $basePriceCents = $this->registration->event->form?->base_price_cents ?? 0;

        if ($basePriceCents !== 0) {
            $priceRows[] = [
                'label' => __('mail.registration_completed.base_price'),
                'amountCents' => $basePriceCents,
            ];
        }

        foreach ($this->registration->registrationValues as $value) {
            if ($value->option_price_cents === 0) {
                continue;
            }

            $label = $value->field->label.': '.$value->option->label;

            $priceRows[] = [
                'label' => $label,
                'amountCents' => $value->option_price_cents,
            ];
        }

        return $priceRows;
    }

    #[Override]
    public static function defaultTemplateContent(Event $event): array
    {
        $json = __('mail-templates.registration_completed');

        return json_decode($json, true);
    }
}
