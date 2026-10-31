<?php

namespace App\Providers;

use App\Http\Responses\SsoLogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (interface_exists(LogoutResponse::class)) {
            $this->app->singleton(LogoutResponse::class, SsoLogoutResponse::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (function_exists('ini_set')) {
            @ini_set('memory_limit', '512M');
        }

        // Tambalan sementara untuk environment lokal dengan SQLite
        // Agar query YEAR() spesifik MySQL tidak menyebabkan error
        try {
            if (app()->environment('local') && DB::connection()->getDriverName() === 'sqlite') {
                DB::connection()->getPdo()->sqliteCreateFunction('YEAR', function ($date) {
                    return date('Y', strtotime($date));
                });
            }
        } catch (\Exception $e) {
            // Abaikan jika koneksi gagal
        }

        // Defer until all service providers are fully booted.
        // Guard with class_exists so the app doesn't crash if socialite is missing.
        if (class_exists(Socialite::class)) {
            $this->app->booted(function () {
                try {
                    Socialite::extend('sipetra', function ($app) {
                        $config = $app['config']['services.sipetra'];

                        return Socialite::buildProvider(SipetraSocialiteProvider::class, $config);
                    });
                } catch (\Exception $e) {
                    // Ignore if contract is not bound yet
                }
            });
        }
    }
}
