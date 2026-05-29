<?php

namespace App\Mail;

use App\Models\MailerSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailerSettingsTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly MailerSettings $mailerSettings
    ) {}

    public function envelope(): Envelope
    {
        $replyTo = $this->mailerSettings->reply_to_address
            ? [new Address($this->mailerSettings->reply_to_address)]
            : [];

        return new Envelope(
            subject: __('admin.mailer_settings.test_email.subject'),
            from: new Address($this->mailerSettings->from_address, $this->mailerSettings->from_name),
            replyTo: $replyTo,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.mailer-settings-test',
            with: [
                'mailerSettings' => $this->mailerSettings,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
