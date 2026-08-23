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
        // Tambalan sementara untuk environment lokal dengan SQLite
        // Agar query YEAR() spesifik MySQL tidak menyebabkan error 
        try {
            if (app()->environment('local') && \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
                \Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction('YEAR', function ($date) {
                    return date('Y', strtotime($date));
                });
            }
        } catch (\Exception $e) {
            // Abaikan jika koneksi gagal
        }

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
