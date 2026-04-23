<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Filament\Notifications\Notification;

class SsoController extends Controller
{
    /**
     * Redirect ke halaman login SIPETRA SSO.
     */
    public function redirect()
    {
        return Socialite::driver('sipetra')->redirect();
    }

    /**
     * Handle callback dari SIPETRA setelah user login.
     */
    public function callback(Request $request)
    {
        // Tangani jika user menolak izin atau ada error dari server
        if ($request->has('error')) {
            $errorDescription = $request->input('error_description', $request->input('error'));
            
            Notification::make()
                ->title('SSO SIPETRA Error')
                ->body('Terjadi kesalahan: ' . $errorDescription)
                ->danger()
                ->persistent()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }

        try {
            $ssoUser = Socialite::driver('sipetra')->user();
        } catch (\Exception $e) {
            logger()->error('SSO Login Failed: ' . $e->getMessage());
            
            Notification::make()
                ->title('SSO Login Gagal')
                ->body('Detail: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }

        $accessToken  = $ssoUser->token;
        $refreshToken = $ssoUser->refreshToken;

        // Data dari /api/user/me (sudah lengkap karena provider menggunakan endpoint ini)
        $rawData = $ssoUser->getRaw();

        // Data profil dari response
        $profile      = $rawData['profile'] ?? [];
        $organization = $rawData['organization'] ?? [];

        // === STRATEGI LINKING ===
        // Cari user lokal berdasarkan sipetra_id, lalu fallback ke email
        $localUser = User::where('sipetra_id', $ssoUser->getId())->first()
                  ?? User::where('email', $ssoUser->getEmail())->first();

        $userData = [
            'sipetra_id'            => $ssoUser->getId(),
            'name'                  => $ssoUser->getName(),
            'email'                 => $ssoUser->getEmail(),
            'sipetra_token'         => $accessToken,
            'sipetra_refresh_token' => $refreshToken,

            // Identity (dari profile)
            'nip'            => $profile['nip'] ?? null,
            'jabatan'        => $organization['jabatan'] ?? null,
            'golongan'       => $organization['golongan'] ?? null,
            'nomor_hp'       => $rawData['phone'] ?? null,
        ];

        if ($localUser) {
            $localUser->update($userData);
        } else {
            $userData['password'] = null; // SSO-only user, tidak punya password lokal
            $localUser = User::create($userData);
        }

        Auth::login($localUser);

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
