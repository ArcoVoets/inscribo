<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Number;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureFilament();
        $this->configureLocalization();
        $this->configureRateLimiting();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );

        Model::shouldBeStrict();
        Model::automaticallyEagerLoadRelationships();
    }

    protected function configureFilament(): void
    {
        TextEntry::configureUsing(fn (TextEntry $component): TextEntry => $component->placeholder('-'));

        // Set timezone
        DateTimePicker::configureUsing(fn (DateTimePicker $component): DateTimePicker => $component->timezone(config('app.display_timezone'))->hint(config('app.display_timezone')));
        TextColumn::configureUsing(fn (TextColumn $component): TextColumn => $component->timezone(config('app.display_timezone')));
        TextEntry::configureUsing(fn (TextEntry $component): TextEntry => $component->timezone(config('app.display_timezone')));
    }

    protected function configureLocalization(): void
    {
        Number::useLocale(app()->getLocale());
        Number::useCurrency('EUR');

        Carbon::setLocale(app()->getLocale());
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for(
            'mollie-webhook',
            fn (Request $request): Limit => Limit::perDay(50)->by($request->ip() ?? 'mollie-webhook')
        );
    }
}
