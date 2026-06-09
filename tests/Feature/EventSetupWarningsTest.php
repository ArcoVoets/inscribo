<?php

use App\Filament\Resources\Events\Pages\EditEvent;
use App\Models\ApiKey;
use App\Models\Event;
use App\Models\MailerSettings;
use App\Models\User;
use Spatie\Permission\Models\Permission;

it('calculates event setup warnings and tab counts', function () {
    $event = Event::factory()->create()->fresh();

    $form = $event->form;
    $form->update([
        'email_field_id' => null,
        'name_field_id' => null,
    ]);

    $form->fields->each(fn ($field) => $field->options()->update(['price_cents' => 0]));

    $page = app(EditEvent::class);
    $page->record = $event->fresh();

    $warnings = $page->getEventSetupWarnings();

    expect($warnings['items'])->toContain(
        __('admin.events.form.warnings.items.no_email_field'),
        __('admin.events.form.warnings.items.no_name_field'),
        __('admin.events.form.warnings.items.no_pricing_options'),
    );

    expect($warnings['total'])->toBe(5)
        ->and($warnings['tabCounts']['general'])->toBe(1)
        ->and($warnings['tabCounts']['form'])->toBe(3)
        ->and($warnings['tabCounts']['emails'])->toBe(1)
        ->and($page->getEventSetupWarningCountForTab('general'))->toBe(1)
        ->and($page->getEventSetupWarningCountForTab('form'))->toBe(3)
        ->and($page->getEventSetupWarningCountForTab('emails'))->toBe(1)
        ->and($page->hasEventSetupWarnings())->toBeTrue();
});

it('warns when the event form has sections but no fields', function () {
    $event = Event::factory()->create()->fresh();

    $event->form->fields()->delete();

    $page = app(EditEvent::class);
    $page->record = $event->fresh();

    $warnings = $page->getEventSetupWarnings();

    expect($warnings['items'])->toContain(
        __('admin.events.form.warnings.items.no_email_field'),
        __('admin.events.form.warnings.items.no_name_field'),
        __('admin.events.form.warnings.items.no_fields'),
        __('admin.events.form.warnings.items.no_pricing_options'),
    );

    expect($warnings['total'])->toBe(6)
        ->and($warnings['tabCounts']['general'])->toBe(1)
        ->and($warnings['tabCounts']['form'])->toBe(4)
        ->and($warnings['tabCounts']['emails'])->toBe(1)
        ->and($page->getEventSetupWarningCountForTab('form'))->toBe(4)
        ->and($page->hasEventSetupWarnings())->toBeTrue();
});

it('returns no event setup warnings when event is fully configured', function () {
    $event = Event::factory()->create()->fresh();
    $apiKey = ApiKey::factory()->create();
    $mailer = MailerSettings::factory()->create();
    $event->update(['api_key_id' => $apiKey->id, 'mailer_settings_id' => $mailer->id]);

    $page = app(EditEvent::class);
    $page->record = $event->fresh();

    $warnings = $page->getEventSetupWarnings();

    expect($warnings['items'])->toBe([])
        ->and($warnings['total'])->toBe(0)
        ->and($page->hasEventSetupWarnings())->toBeFalse();
});

it('renders the event edit page without repeater closure errors', function () {
    Permission::findOrCreate('access_admin_panel');
    Permission::findOrCreate('view_events');
    Permission::findOrCreate('manage_events');
    Permission::findOrCreate('preview_forms');

    $user = User::factory()->create();
    $user->givePermissionTo([
        'access_admin_panel',
        'view_events',
        'manage_events',
        'preview_forms',
    ]);

    $this->actingAs($user);

    $event = Event::factory()->create();

    $this->get(route('filament.admin.resources.events.edit', ['record' => $event->id]))
        ->assertOk();
});
