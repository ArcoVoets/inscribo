<?php

namespace App\Models;

use App\Enums\ParticipantType;
use App\Enums\RegistrationStates;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Uri;

class Registration extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'participant_type' => ParticipantType::class,
            'price_cents' => 'integer',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function states(): HasMany
    {
        return $this->hasMany(RegistrationState::class)->orderByDesc('created_at');
    }

    public function currentState(): HasOne
    {
        return $this->hasOne(RegistrationState::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(RegistrationPayment::class)->orderByDesc('created_at');
    }

    public function registrationValues(): HasMany
    {
        return $this->hasMany(RegistrationValue::class);
    }

    public function currentPaymentState(): HasOne
    {
        return $this->hasOne(RegistrationPayment::class)->latestOfMany();
    }

    #[Scope]
    public function reservedForCapacity(Builder $query): Builder
    {
        return $query->whereHas('currentState', function (Builder $stateQuery): void {
            $stateQuery->whereIn('type', RegistrationStates::reservedStates());
        });
    }

    #[Scope]
    public function waitlisted(Builder $query): Builder
    {
        return $query->whereHas('currentState', fn (Builder $query) => $query
            ->where('type', RegistrationStates::Waitlisted));
    }

    #[Scope]
    public function whereState(Builder $query, RegistrationStates $state): Builder
    {
        return $query->whereHas('currentState', fn (Builder $query): Builder => $query->whereType($state));
    }

    /** @var array<RegistrationStates> $states */
    #[Scope]
    public function whereStateIn(Builder $query, array $states): Builder
    {
        return $query->where(function (Builder $query) use ($states): void {
            $isFirst = true;

            foreach ($states as $state) {
                if ($isFirst) {
                    $query->whereState($state);
                    $isFirst = false;

                    continue;
                }

                $query->orWhere(fn (Builder $query): Builder => $query->whereState($state));
            }
        });
    }

    /** @param array<string, string> $queryParams */
    public function signedStatusUrl(array $queryParams = []): string
    {
        return URL::signedRoute('events.register.status', [
            'event' => $this->event_id,
            'registration' => $this->id,
        ] + $queryParams);
    }

    /** @param array<string, string> $queryParams */
    public function publicStatusUrl(array $queryParams = [], ?bool $isIframe = null): string
    {
        $signedUrl = $this->signedStatusUrl($queryParams);
        $statusPageUrl = $this->event?->wordpress_status_page_url;

        if (! $this->event?->wordpress_enabled || ! $statusPageUrl || $isIframe === false) {
            return $signedUrl;
        }

        $signedUri = Uri::of($signedUrl);
        $statusUri = Uri::of($statusPageUrl)
            ->withQuery([
                'registration' => $this->id,
                'signature' => $signedUri->query()->get('signature'),
            ]);

        return $statusUri->toString();
    }

    public function checkoutUrl(CarbonInterface $expiration): string
    {
        return URL::temporarySignedRoute('events.register.checkout', $expiration, [
            'event' => $this->event_id,
            'registration' => $this->id,
        ]);
    }

    public function notifyEmail(): ?string
    {
        return $this->registrationValues
            ->where('field_id', $this->event->form->email_field_id)
            ->first()
            ?->value;
    }

    public function name(): string
    {
        $name = $this->registrationValues
            ->where('field_id', $this->event->form->name_field_id)
            ->first()
            ?->value;

        return $name ?? __('registration.participant');
    }
}
