<?php

namespace App\Providers;

use App\Contracts\TournamentTopScorerResolver;
use App\Services\Achievements\AchievementAwarder;
use App\Services\TournamentTopScorer\NullTournamentTopScorerResolver;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TournamentTopScorerResolver::class,
            NullTournamentTopScorerResolver::class,
        );

        $this->app->singleton(AchievementAwarder::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        $this->configureDefaults();
        $this->configureHttpMacros();
    }

    protected function configureHttpMacros(): void
    {
        Http::macro('fifa', fn () => Http::baseUrl(config('fifa.base_url'))
            ->timeout(config('fifa.timeout'))
            ->connectTimeout(config('fifa.connect_timeout'))
            ->retry([100, 500], throw: false));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
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
            : null,
        );
    }
}
