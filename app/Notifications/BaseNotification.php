<?php

namespace App\Notifications;

use App\Enums\EventMailTemplateType;
use App\Models\Event;
use App\Models\EventMailTemplate;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /** @return string[] */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    abstract public static function templateType(): EventMailTemplateType;

    abstract public static function defaultTemplateSubject(Event $event): string;

    abstract public static function defaultTemplateContent(Event $event): array;

    /**
     * Get all merge tag labels for this notification, including both common and event,
     * registration or invite-specific tags.
     */
    abstract public static function allMergeTagLabels(): array;

    /**
     * Get all merge tags for this notification, as an associative array where the keys are the merge tag
     * keys and the values are the corresponding values for this notification instance. This should
     * include both common and event, registration or invite-specific tags. Additionally, the merge tags
     * returned by this method should be the same as the merge tags returned by the `allMergeTagLabels` method.
     */
    abstract protected function allMergeTags(): array;

    abstract public function getEvent(): Event;

    private function getMailTemplate(): EventMailTemplate
    {
        return $this->getEvent()->mailTemplates()
            ->where('type', $this->templateType())
            ->firstOrFail();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailTemplate = $this->getMailTemplate();
        $content = RichContentRenderer::make($mailTemplate->content)
            ->mergeTags($this->allMergeTags());

        $event = $this->getEvent();
        $mailerSettings = $event->mailerSettings;
        $mailerName = $event->mailerName();

        return (new MailMessage)
            ->subject($mailTemplate->subject)
            ->view('mail.custom-template', ['content' => $content])
            ->when($mailerName !== null, fn (MailMessage $mail): MailMessage => $mail->mailer($mailerName))
            ->when(
                $mailerSettings?->from_address !== null,
                fn (MailMessage $mail): MailMessage => $mail->from($mailerSettings->from_address, $mailerSettings->from_name)
            )
            ->when(
                $mailerSettings?->reply_to_address !== null,
                fn (MailMessage $mail): MailMessage => $mail->replyTo($mailerSettings->reply_to_address)
            );
    }

    public function toArray(object $notifiable): array
    {
        $mailTemplate = $this->getMailTemplate();

        return [
            'subject' => $mailTemplate->subject,
            'content' => $mailTemplate->content,
            'mergeTags' => $this->allMergeTags(),
        ];
    }
}
