<?php

namespace App\Models;

use App\Enums\RegistrationStates;
use App\Models\RegistrationStates\PaymentExpiredState;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Component;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Parental\HasChildren;

class RegistrationState extends Model
{
    use HasChildren {
        newFromBuilder as newFromBuilderFromTrait;
    }

    protected $table = 'registration_states';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'type' => RegistrationStates::class,
        'amount_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static string $filamentColor = 'gray';

    /** @var array<string, class-string<self>> */
    public function childTypes(): array
    {
        $childTypes = [];
        foreach (RegistrationStates::cases() as $state) {
            $childTypes[$state->value] = $state->getModelClass();
        }

        return $childTypes;
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Override the HasChildren newFromBuilder method to return the correct child class
     * when we create a new instance of PaymentPending but with an expired expires_at.
     */
    public function newFromBuilder($attributes = [], $connection = null): static
    {
        $attributes = (array) $attributes;
        $inheritanceColumn = $this->getInheritanceColumn();
        $type = $attributes[$inheritanceColumn] ?? null;

        if ($type instanceof \UnitEnum) {
            $type = $type->value;
        }

        if ($type === RegistrationStates::PaymentPending->value) {
            $expiresAt = $attributes['expires_at'] ?? null;

            if ($this->isExpiredValue($expiresAt)) {
                $model = new PaymentExpiredState;
                $model->exists = true;
                $model->setRawAttributes($attributes, true);
                $model->setConnection($connection ?: $this->getConnectionName());
                $model->fireModelEvent('retrieved', false);

                return $model;
            }
        }

        return $this->newFromBuilderFromTrait($attributes, $connection);
    }

    protected function isExpiredValue(mixed $expiresAt): bool
    {
        return $expiresAt !== null && Carbon::parse($expiresAt)->isPast();
    }

    #[Scope]
    public function whereType(Builder $query, RegistrationStates $type): Builder
    {
        // Split PaymentExpired and PaymentPending based on expires_at for payment pending registrations
        if ($type === RegistrationStates::PaymentExpired) {
            return $query
                ->where('type', RegistrationStates::PaymentPending)
                ->wherePast('expires_at');
        } elseif ($type === RegistrationStates::PaymentPending) {
            return $query
                ->where('type', RegistrationStates::PaymentPending)
                ->where(fn (Builder $query): Builder => $query->whereNull('expires_at')->orWhereNowOrFuture('expires_at'));
        }

        return $query->where('type', $type);
    }

    public function getLabel(): string
    {
        return $this->type->getLabel();
    }

    /** @return array<int, Action> */
    public function filamentHeaderActions(): array
    {
        return [];
    }

    protected static function cancelAction(): Action
    {
        return Action::make('cancel')
            ->label(__('admin.states.cancel_action'))
            ->icon(Heroicon::OutlinedXMark)
            ->color('danger')
            ->requiresConfirmation()
            ->action(function (Registration $record): void {
                $record->states()->create(['type' => RegistrationStates::Cancelled]);

                Notification::make()
                    ->title(__('admin.states.cancelled_notification'))
                    ->success()
                    ->send();
            });
    }

    /** @return array<Component> */
    public function stateFields(): array
    {
        return [];
    }
}
