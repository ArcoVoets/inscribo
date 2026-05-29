<?php

use App\Enums\PermissionsEnum;
use App\Models\MailerSettings;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;

test('mailer settings policy allows users with permission', function () {
    Permission::findOrCreate(PermissionsEnum::MANAGE_MAILER_SETTINGS->value);

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionsEnum::MANAGE_MAILER_SETTINGS->value);

    $mailerSettings = MailerSettings::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', MailerSettings::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('view', $mailerSettings))->toBeTrue()
        ->and(Gate::forUser($user)->allows('create', MailerSettings::class))->toBeTrue()
        ->and(Gate::forUser($user)->allows('update', $mailerSettings))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $mailerSettings))->toBeTrue()
        ->and(Gate::forUser($user)->allows('deleteAny', MailerSettings::class))->toBeTrue();
});

test('mailer settings policy denies users without permission', function () {
    Permission::findOrCreate(PermissionsEnum::MANAGE_MAILER_SETTINGS->value);

    $user = User::factory()->create();
    $mailerSettings = MailerSettings::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', MailerSettings::class))->toBeFalse()
        ->and(Gate::forUser($user)->allows('view', $mailerSettings))->toBeFalse()
        ->and(Gate::forUser($user)->allows('create', MailerSettings::class))->toBeFalse()
        ->and(Gate::forUser($user)->allows('update', $mailerSettings))->toBeFalse()
        ->and(Gate::forUser($user)->allows('delete', $mailerSettings))->toBeFalse()
        ->and(Gate::forUser($user)->allows('deleteAny', MailerSettings::class))->toBeFalse();
});
