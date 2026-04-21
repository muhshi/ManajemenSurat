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
        // Defer until all service providers are fully booted.
        // Guard with class_exists so the app doesn't crash if socialite is missing.
        if (class_exists(\Laravel\Socialite\Facades\Socialite::class)) {
            $this->app->booted(function () {
                try {
                    \Laravel\Socialite\Facades\Socialite::extend('sipetra', function ($app) {
                        $config = $app['config']['services.sipetra'];
                        return \Laravel\Socialite\Facades\Socialite::buildProvider(SipetraSocialiteProvider::class, $config);
                    });
                } catch (\Exception $e) {
                    // Ignore if contract is not bound yet
                }
            });
        }
    }
}
