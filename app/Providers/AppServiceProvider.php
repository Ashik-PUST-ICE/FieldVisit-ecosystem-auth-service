<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Laravel\Passport\Passport;
use App\Models\Passport\Client;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Events\Applications\LogActivity;
use App\Repositories\Eloquents\AuthRepository;
use App\Repositories\Eloquents\ServiceRepository;
use App\Services\Applications\MicroserviceClient;
use App\Services\Applications\Logging\RemoteLogClient;
use App\Listeners\Applications\SendLogToLoggingService;
use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Services\Applications\Gateway\MachineTokenManager;
use App\Repositories\Eloquents\CustomAccessTokenRepository;
use App\Repositories\Interfaces\ServiceRepositoryInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerInterfaces();
    }

    /**
     * Register the application's interfaces.
     */
    protected function registerInterfaces(): void
    {
        $this->app->bind(\Laravel\Passport\Bridge\AccessTokenRepository::class, \App\Repositories\Eloquents\CustomAccessTokenRepository::class);

        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);

        $this->app->singleton(RemoteLogClient::class, fn() => new RemoteLogClient());
        $this->app->singleton(MachineTokenManager::class, fn() => new MachineTokenManager());
    }

    /**
     * Bootstrap any application services.
     */
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
    }
}
