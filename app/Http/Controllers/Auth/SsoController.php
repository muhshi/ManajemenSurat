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
     * Redirect langsung ke form registrasi SIPETRA SSO.
     */
    public function register()
    {
        try {
            return Socialite::driver('sipetra')
                ->with(['screen_hint' => 'register'])
                ->redirect();
        } catch (\Exception $e) {
            logger()->error('SIPETRA Register Redirect Failed: ' . $e->getMessage());

            Notification::make()
                ->title('Gagal Membuka Pendaftaran SIPETRA')
                ->body('Terjadi kendala koneksi ke server SIPETRA: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return redirect()->route('filament.admin.auth.login');
        }
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

        $isNewUser = !$localUser;
        $identityType = $rawData['identity_type'] ?? (!empty($rawData['nip']) ? 'pegawai' : 'mitra');

        $userData = [
            'sipetra_id'            => $ssoUser->getId(),
            'name'                  => $ssoUser->getName(),
            'sipetra_token'         => $accessToken,
            'sipetra_refresh_token' => $refreshToken,
            'identity_type'         => $identityType,
            'avatar_url'            => $rawData['avatar_url'] ?? $ssoUser->getAvatar(),
            'sobat_id'              => $rawData['sobat_id'] ?? null,
            'nip_baru'              => $rawData['nip_baru'] ?? null,

            // Identity & Employee Data
            'nip'                   => $rawData['nip'] ?? null,
            'jabatan'               => $rawData['employee']['jabatan'] ?? ($rawData['jabatan'] ?? null),
            'golongan'              => $rawData['employee']['golongan'] ?? ($rawData['golongan'] ?? null),
            'nomor_hp'              => $rawData['phone_number'] ?? ($rawData['nomor_hp'] ?? null),
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
        }

        // Tetapkan role berdasarkan identity_type (mitra / pegawai)
        $roleToAssign = $identityType === 'mitra' ? 'mitra' : 'pegawai';

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $roleToAssign, 'guard_name' => 'web']);
            if (!$localUser->hasRole($roleToAssign)) {
                $localUser->assignRole($roleToAssign);
            }
        }

        Auth::login($localUser);

        if ($isNewUser) {
            Notification::make()
                ->title('Pendaftaran Akun Berhasil!')
                ->body('Selamat datang di Manajemen Surat BPS Kabupaten Demak. Akun Anda berhasil terhubung via SIPETRA.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Profil Berhasil Disinkronkan dari SIPETRA')
                ->success()
                ->send();
        }

        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
