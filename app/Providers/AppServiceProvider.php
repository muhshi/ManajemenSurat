<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        \Laravel\Socialite\Facades\Socialite::extend('sipetra', function ($app) {
            $config = $app['config']['services.sipetra'];

            return \Laravel\Socialite\Facades\Socialite::buildProvider(SipetraSocialiteProvider::class, $config);
        });
    }
}
