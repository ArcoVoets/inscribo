<?php

namespace App\Models;

use App\Contracts\Notifiable as NotifiableContract;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Override;

class Invite extends Model implements NotifiableContract
{
    use HasFactory;
    use Notifiable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function usedRegistration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'used_registration_id');
    }

    #[Scope]
    protected function whereValid(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')
                    ->orWhereNowOrFuture('expires_at');
            })
            ->whereNull('used_at');
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isUsed(): bool
    {
        return $this->used_at !== null;
    }

    public function canBeClaimed(): bool
    {
        return (! $this->isExpired()) && (! $this->isRevoked()) && (! $this->isUsed());
    }

    public function matchesToken(string $token): bool
    {
        return hash_equals((string) $this->token, $token);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public function markUsedByRegistration(Registration $registration): void
    {
        $this->update([
            'used_at' => now(),
            'used_registration_id' => $registration->id,
        ]);
    }

    public static function findValidForEvent(Event $event, mixed $inviteId, mixed $inviteToken, bool $forUpdate = false): ?self
    {
        $inviteId = is_numeric($inviteId) ? (int) $inviteId : null;
        $inviteToken = is_string($inviteToken) ? $inviteToken : null;

        if (! $inviteId || ! $inviteToken) {
            return null;
        }

        $invite = $event->invites()
            ->where('id', $inviteId)
            ->when($forUpdate, fn (Builder $query): Builder => $query->lockForUpdate())
            ->first();

        if (! $invite || ! $invite->matchesToken($inviteToken) || ! $invite->canBeClaimed()) {
            return null;
        }

        return $invite;
    }

    public function url(): string
    {
        if ($this->event->wordpress_enabled) {
            return Uri::of($this->event->wordpress_form_page_url)
                ->withQuery([
                    'invite_id' => $this->id,
                    'invite_token' => $this->token,
                ])
                ->toString();
        }

        return route('events.register', [
            'event' => $this->event_id,
            'invite_id' => $this->id,
            'invite_token' => $this->token,
        ]);

    }

    #[Override]
    public function canBeSendToMail(): bool
    {
        return true;
    }

    public function routeNotificationForMail(Notification $notification): array|string
    {
        return [$this->email => $this->name];
    }
}
