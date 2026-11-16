<?php

use App\Enums\PermissionsEnum;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Filament\Tables\Columns\Column;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;

test('event table shows registration counts with successful and waitlisted counts visible', function () {
    Permission::findOrCreate(PermissionsEnum::ACCESS_ADMIN_PANEL->value);

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionsEnum::ACCESS_ADMIN_PANEL->value);

    $this->actingAs($user);

    $event = Event::factory()->create();
    Registration::factory()->registered()->create(['event_id' => $event->id]);
    Registration::factory()->waitlisted()->create(['event_id' => $event->id]);

    $table = EventsTable::configure(Table::make(new ListEvents)->query(Event::query()));
    $columns = collect($table->getColumns())->keyBy(fn (Column $column): string => $column->getName());
    $record = $table->getQuery()->findOrFail($event->id);

    expect($record->registered_registrations_count)->toBe(1)
        ->and($record->waitlisted_registrations_count)->toBe(1)
        ->and($columns)->toHaveKeys([
            'registered_registrations_count',
            'waitlisted_registrations_count',
            'payment_pending_registrations_count',
            'cancelled_registrations_count',
            'refunded_registrations_count',
            'payment_expired_registrations_count',
        ])
        ->and($columns['registered_registrations_count']->isToggledHiddenByDefault())->toBeFalse()
        ->and($columns['waitlisted_registrations_count']->isToggledHiddenByDefault())->toBeFalse()
        ->and($columns->except([
            'registered_registrations_count',
            'waitlisted_registrations_count',
            'title',
            'capacity',
            'opens_at',
            'closes_at',
            'show_waitlist_position',
        ])->every(fn (Column $column): bool => $column->isToggledHiddenByDefault()))->toBeTrue();
});
