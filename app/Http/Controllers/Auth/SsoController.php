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

        // === STRATEGI LINKING ===
        // Cari user lokal berdasarkan sipetra_id, lalu fallback ke email
        $localUser = User::where('sipetra_id', $ssoUser->getId())->first()
                  ?? User::where('email', $ssoUser->getEmail())->first();

        $userData = [
            'sipetra_id'            => $ssoUser->getId(),
            'name'                  => $ssoUser->getName(),
            'sipetra_token'         => $accessToken,
            'sipetra_refresh_token' => $refreshToken,

            // Identity & Employee Data
            'nip'            => $rawData['nip'] ?? null,
            'jabatan'        => $rawData['employee']['jabatan'] ?? null,
            'golongan'       => $rawData['employee']['golongan'] ?? null,
            'nomor_hp'       => $rawData['phone_number'] ?? null,
        ];

        // Hanya update email jika tidak menyebabkan konflik dengan user lain
        $newEmail = $ssoUser->getEmail();
        $emailConflict = User::where('email', $newEmail)
            ->where('sipetra_id', '!=', $ssoUser->getId())
            ->exists();

        if (!$emailConflict) {
            $userData['email'] = $newEmail;
        }

        if ($localUser) {
            // Jika user ditemukan lewat email tapi belum punya sipetra_id, link sekarang
            $localUser->update($userData);
        } else {
            $userData['email'] = $newEmail; // Pastikan email diset untuk user baru
            $userData['password'] = null;
            $localUser = User::create($userData);
            $localUser->assignRole('pegawai');
        }

        Auth::login($localUser);

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
