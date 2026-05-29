<?php

namespace App\Filament\Resources\MailerSettings\Pages;

use App\Filament\Resources\MailerSettings\MailerSettingsResource;
use App\Mail\MailerSettingsTestMail;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EditMailerSettings extends EditRecord
{
    protected static string $resource = MailerSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_password')
                ->label(__('admin.mailer_settings.actions.update_password'))
                ->schema([
                    TextInput::make('new_password')
                        ->label(__('admin.mailer_settings.form.fields.password'))
                        ->autocomplete(false)
                        ->password()
                        ->revealable()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'password' => $data['new_password'],
                    ]);

                    Notification::make()
                        ->title(__('admin.mailer_settings.actions.update_password_success'))
                        ->success()
                        ->send();
                }),
            Action::make('send_test_email')
                ->label(__('admin.mailer_settings.actions.send_test_email'))
                ->schema([
                    TextInput::make('test_email')
                        ->label(__('admin.mailer_settings.form.fields.test_email'))
                        ->email()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $mailerName = "mailer_settings_{$this->record->id}";

                    Config::set("mail.mailers.{$mailerName}", [
                        'transport' => 'smtp',
                        'host' => $this->record->host,
                        'port' => $this->record->port,
                        'username' => $this->record->username,
                        'password' => $this->record->password,
                    ]);

                    try {
                        Mail::mailer($mailerName)
                            ->to($data['test_email'])
                            ->send(new MailerSettingsTestMail($this->record));

                        Notification::make()
                            ->title(__('admin.mailer_settings.actions.send_test_email_success'))
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        report($exception);

                        Notification::make()
                            ->title(__('admin.mailer_settings.actions.send_test_email_failed'))
                            ->body($exception->getMessage())
                            ->persistent()
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
