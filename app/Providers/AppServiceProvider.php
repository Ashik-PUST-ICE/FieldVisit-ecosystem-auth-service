<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Cache\RateLimiting\Limit;
use Laravel\Passport\Passport;
use App\Models\Passport\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Events\Applications\LogActivity;
use App\Services\Applications\MicroserviceClient;
use App\Services\Applications\Logging\RemoteLogClient;
use App\Listeners\Applications\SendLogToLoggingService;
use App\Services\Applications\Gateway\MachineTokenManager;
use App\Repositories\Eloquents\CustomAccessTokenRepository;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(\Laravel\Passport\Bridge\AccessTokenRepository::class, CustomAccessTokenRepository::class);
        $this->app->singleton(RemoteLogClient::class, fn() => new RemoteLogClient());
        $this->app->singleton(MachineTokenManager::class, fn() => new MachineTokenManager());
    }

    public function boot(): void
    {
        Passport::authorizationView('oauth.authorize');
        Passport::tokensExpireIn(CarbonInterval::days(10));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));
        Passport::useClientModel(Client::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('special-super-admin') ? true : null;
        });
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super-admin') ? true : null;
        });

        RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
