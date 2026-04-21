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
        $this->app->booted(function () {
            $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);
            $socialite->extend('sipetra', function ($app) use ($socialite) {
                $config = $app['config']['services.sipetra'];

                return $socialite->buildProvider(SipetraSocialiteProvider::class, $config);
            });
        });
    }
}
