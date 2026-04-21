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
        // Defer until all service providers (including SocialiteServiceProvider)
        // are fully booted, to avoid "not instantiable" binding errors.
        // Guard with class_exists so the app doesn't crash if socialite
        // is not yet installed in vendor (e.g. before image rebuild).
        $this->app->booted(function () {
            if (!class_exists(\Laravel\Socialite\Contracts\Factory::class)) {
                return;
            }
            $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);
            $socialite->extend('sipetra', function ($app) use ($socialite) {
                $config = $app['config']['services.sipetra'];

                return $socialite->buildProvider(SipetraSocialiteProvider::class, $config);
            });
        });
    }
}
