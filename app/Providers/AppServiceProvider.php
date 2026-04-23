<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use App\Providers\SipetraSocialiteProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Socialite::extend('sipetra', function ($app) {
            $config = $app['config']['services.sipetra'];
            return Socialite::buildProvider(SipetraSocialiteProvider::class, $config);
        });
    }
}
