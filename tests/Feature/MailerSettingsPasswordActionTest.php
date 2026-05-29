<?php

use App\Enums\PermissionsEnum;
use App\Filament\Resources\MailerSettings\Pages\EditMailerSettings;
use App\Mail\MailerSettingsTestMail;
use App\Models\MailerSettings;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;

test('mailer settings password can be updated via action', function () {
    Permission::findOrCreate(PermissionsEnum::ACCESS_ADMIN_PANEL->value);
    Permission::findOrCreate(PermissionsEnum::MANAGE_MAILER_SETTINGS->value);

    $user = User::factory()->create();
    $user->givePermissionTo([
        PermissionsEnum::ACCESS_ADMIN_PANEL->value,
        PermissionsEnum::MANAGE_MAILER_SETTINGS->value,
    ]);

    $this->actingAs($user);

    $mailerSettings = MailerSettings::factory()->create([
        'password' => 'old-password',
    ]);

    Livewire::test(EditMailerSettings::class, ['record' => $mailerSettings->id])
        ->callAction('update_password', [
            'new_password' => 'new-secret',
        ]);

    $mailerSettings->refresh();

    expect($mailerSettings->password)->toBe('new-secret');
});

test('mailer settings test email action sends a message', function () {
    Permission::findOrCreate(PermissionsEnum::ACCESS_ADMIN_PANEL->value);
    Permission::findOrCreate(PermissionsEnum::MANAGE_MAILER_SETTINGS->value);

    $user = User::factory()->create();
    $user->givePermissionTo([
        PermissionsEnum::ACCESS_ADMIN_PANEL->value,
        PermissionsEnum::MANAGE_MAILER_SETTINGS->value,
    ]);

    $this->actingAs($user);

    Mail::fake();

    $mailerSettings = MailerSettings::factory()->create([
        'host' => 'smtp.test.example',
        'port' => 587,
        'username' => 'mailer@example.com',
        'password' => 'secret-password',
    ]);

    Livewire::test(EditMailerSettings::class, ['record' => $mailerSettings->id])
        ->callAction('send_test_email', [
            'test_email' => 'test@example.com',
        ]);

    $mailerName = "mailer_settings_{$mailerSettings->id}";

    expect(Config::get("mail.mailers.{$mailerName}"))->toMatchArray([
        'transport' => 'smtp',
        'host' => 'smtp.test.example',
        'port' => 587,
        'username' => 'mailer@example.com',
        'password' => 'secret-password',
    ]);

    Mail::assertSent(MailerSettingsTestMail::class, function (MailerSettingsTestMail $mail): bool {
        return $mail->hasTo('test@example.com');
    });
});
