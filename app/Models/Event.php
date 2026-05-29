<?php

namespace App\Models;

use App\Enums\EventMailTemplateType;
use App\Enums\ParticipantType;
use App\Enums\RegistrationStates;
use App\Observers\EventObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Uri;

/**
 * @property ?string $home_url
 * @property ?string $wordpress_status_page_url
 * @property ?string $wordpress_form_page_url
 * @property bool $wordpress_enabled
 **/
#[ObservedBy(EventObserver::class)]
class Event extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $attributes = [
        'show_waitlist_position' => false,
        'wordpress_enabled' => false,
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'capcity' => 'integer',
            'show_waitlist_position' => 'boolean',
            'show_capacity_data' => 'boolean',
            'wordpress_enabled' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class);
    }

    public function mailTemplates(): HasMany
    {
        // Order by same order as in enum
        $orderQuery = '(CASE '.str_repeat('WHEN `type` = ? THEN ? ', count(EventMailTemplateType::cases())).'END)';
        $order = collect(EventMailTemplateType::cases())->pluck('value')->map(fn (string $value, int $key): array => [$value, $key])->flatten()->all();

        return $this
            ->hasMany(EventMailTemplate::class)
            ->orderByRaw($orderQuery, $order);
    }

    public function mailerSettings(): BelongsTo
    {
        return $this->belongsTo(MailerSettings::class);
    }

    public function form(): HasOne
    {
        return $this->hasOne(Form::class);
    }

    public function reservedForCapacityCount(): int
    {
        return $this->registrations()
            ->reservedForCapacity()
            ->count();
    }

    public function availableCapacity(): int
    {
        return max(0, (int) $this->capacity - $this->reservedForCapacityCount());
    }

    public function waitlistCount(): int
    {
        return $this->registrations()
            ->waitlisted()
            ->count();
    }

    /**
     * @return array{reservedCount:int, availableCapacity:int, waitlistCount:int, waitlistIsEmpty:bool, isCapacityFull:bool}
     */
    public function capacitySnapshot(): array
    {
        $reservedCount = $this->reservedForCapacityCount();
        $availableCapacity = max(0, (int) $this->capacity - $reservedCount);
        $waitlistCount = $this->waitlistCount();

        return [
            'reservedCount' => $reservedCount,
            'availableCapacity' => $availableCapacity,
            'waitlistCount' => $waitlistCount,
            'waitlistIsEmpty' => $waitlistCount === 0,
            'isCapacityFull' => $availableCapacity === 0,
        ];
    }

    public function directPaymentAllowed(): bool
    {
        $waitlistExists = $this->registrations()
            ->waitlisted()
            ->exists();

        return $this->availableCapacity() > 0 && (! $waitlistExists);
    }

    public function priceForParticipantType(ParticipantType|string $participantType): int
    {
        if (is_string($participantType)) {
            $participantType = ParticipantType::from($participantType);
        }

        return match ($participantType) {
            ParticipantType::Student => $this->student_price_cents,
            ParticipantType::Worker => $this->worker_price_cents,
        };
    }

    public function registrationIsClosed(): bool
    {
        return $this->opens_at->isFuture() || $this->closes_at?->isNowOrPast();
    }

    public function isOpenForRegistration(): bool
    {
        return ! $this->registrationIsClosed();
    }

    /** @return array{key:string, label:string, priceCents:int}[] */
    public function participantTypes(): array
    {
        return array_map(
            fn (ParticipantType $type): array => [
                'key' => $type->value,
                'label' => $type->getLabel(),
                'priceCents' => $this->priceForParticipantType($type),
            ],
            ParticipantType::cases(),
        );
    }

    public function cleanTitleForFilename(): string
    {
        return str_replace([' ', '/'], ['-', '_'], $this->title);
    }

    /**
     * @return array{
     *      total_capacity:int,
     *      registrations_count:int,
     *      pending_payments_count:int,
     *      pending_invites_count:int,
     *      total_pending:int,
     *      available_capacity:int,
     *      status:string,
     *      has_capacity:bool
     * }
     */
    public function inviteCapacityInfo(): array
    {
        $registrationsCount = $this->registrations()->whereState(RegistrationStates::Registered)->count();
        $pendingPaymentsCount = $this->registrations()->whereState(RegistrationStates::PaymentPending)->count();
        $pendingInvitesCount = $this->invites()->whereValid()->count();

        $totalCapacity = $this->capacity;
        $totalPending = $registrationsCount + $pendingPaymentsCount + $pendingInvitesCount;
        $availableCapacity = $totalCapacity - $totalPending;

        if ($availableCapacity > 0) {
            $status = trans_choice('admin.capacity.places_available', $availableCapacity, ['count' => $availableCapacity]);
            $hasCapacity = true;
        } else {
            $status = __('admin.capacity.no_capacity');
            $hasCapacity = false;
        }

        return [
            'total_capacity' => $totalCapacity,
            'registrations_count' => $registrationsCount,
            'pending_payments_count' => $pendingPaymentsCount,
            'pending_invites_count' => $pendingInvitesCount,
            'total_pending' => $totalPending,
            'available_capacity' => $availableCapacity,
            'status' => $status,
            'has_capacity' => $hasCapacity,
        ];
    }

    public function wordpressFormShortcode(): string
    {
        $origin = rtrim(Config::string('app.url'), '/');
        $formPath = (string) parse_url(route('events.register', $this), PHP_URL_PATH);

        return '[inscribo-embed-form origin="'.$origin.'" form_path="'.$formPath.'"]';
    }

    public function wordpressStatusShortcode(): string
    {
        $origin = rtrim(Config::string('app.url'), '/');

        $statusPathTemplate = route('events.register.status', ['event' => $this->id, 'registration' => ':registration'], false);

        return '[inscribo-embed-status origin="'.$origin.'" status_path_template="'.$statusPathTemplate.'"]';
    }

    public function registrationUrl(): string
    {
        if ($this->wordpress_enabled) {
            return $this->wordpress_form_page_url;
        }

        return route('events.register', ['event' => $this->id]);
    }

    protected function uriToOrigin(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $uri = Uri::of($url);

        return $uri
            ->withPath('')
            ->replaceQuery([])
            ->withoutFragment()
            ->toStringAble()
            ->rtrim('/')
            ->toString();
    }

    public function wordpressFormBaseUrl(): ?string
    {
        if (! $this->wordpress_enabled) {
            return null;
        }

        return $this->uriToOrigin($this->wordpress_form_page_url);
    }

    public function wordPressStatusBaseUrl(): ?string
    {
        if (! $this->wordpress_enabled) {
            return null;
        }

        return $this->uriToOrigin($this->wordpress_status_page_url);
    }

    // Use to cache the result of registrations()->exists() since we need it in a lot of place in the FormForm
    public function registrationsExist(): bool
    {
        return once(fn (): bool => $this->registrations()->exists());
    }

    public function invitesExist(): bool
    {
        return once(fn (): bool => $this->invites()->exists());
    }

    public function replicateMailTemplatesFrom(Event $sourceEvent): void
    {
        foreach ($sourceEvent->mailTemplates as $template) {
            $replica = $template->replicate(['event_id']);
            $replica->event_id = $this->id;
            $replica->save();
        }
    }

    public function mailerName(): ?string
    {
        if ($this->mailer_settings_id === null) {
            return null;
        }

        return "event_{$this->id}";
    }
}
