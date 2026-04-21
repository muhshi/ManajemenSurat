<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SsoController extends Controller
{
    /**
     * Redirect the user to the SIPETRA authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('sipetra')->redirect();
    }

    /**
     * Obtain the user information from SIPETRA.
     */
    public function callback(Request $request)
    {
        // If SSO Server rejected the authorization request
        if ($request->has('error')) {
            $message = match ($request->input('error')) {
                'access_denied' => 'Login dibatalkan. Anda harus memberikan izin untuk dapat masuk ke aplikasi.',
                default => 'Terjadi kesalahan pada server SSO: ' . $request->input('error_description', 'Unknown error'),
            };

            return redirect()->route('filament.admin.auth.login')->withErrors(['email' => $message]);
        }

        try {
            /** @var \Laravel\Socialite\Two\User $sipetraUser */
            $sipetraUser = Socialite::driver('sipetra')->user();
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $responseBody = $e->getResponse()->getBody()->getContents();
            logger()->error('SSO Client Error: ' . $responseBody);
            $errorData = json_decode($responseBody, true);
            $hint = $errorData['hint'] ?? '';
            return redirect()->route('filament.admin.auth.login')->withErrors(['email' => 'Gagal melakukan login via SSO SIPETRA. Info: ' . $hint]);
        } catch (\Exception $e) {
            logger()->error('SSO Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('filament.admin.auth.login')->withErrors(['email' => 'Gagal melakukan login via SSO SIPETRA. ' . $e->getMessage()]);
        }

        logger()->info('SSO User obtained successfully', ['email' => $sipetraUser->getEmail()]);

        // Token dari Socialite payload
        $accessToken = $sipetraUser->token;
        $refreshToken = $sipetraUser->refreshToken;

        // Semua data sudah tersedia di raw payload (/api/user/me)
        $rawData      = $sipetraUser->getRaw();
        $profile      = $rawData['profile'] ?? [];
        $organization = $rawData['organization'] ?? [];

        logger()->debug('SSO User Profile Data:', [
            'id'    => $sipetraUser->getId(),
            'name'  => $sipetraUser->getName(),
            'email' => $sipetraUser->getEmail(),
        ]);

        // Match the user locally (sipetra_id first, then email fallback)
        $localUser = User::where('sipetra_id', $sipetraUser->getId())->first()
            ?? User::where('email', $sipetraUser->getEmail())->first();

        $userData = [
            'sipetra_id'            => $sipetraUser->getId(),
            'name'                  => $sipetraUser->getName(),
            'email'                 => $sipetraUser->getEmail(),
            'sipetra_token'         => $accessToken,
            'sipetra_refresh_token' => $refreshToken,
            'avatar_url'            => $sipetraUser->getAvatar(),

            // From profile (nested in /api/user/me)
            'identity_type' => $profile['identity_type'] ?? null,
            'nip'           => $profile['nip'] ?? null,
            'nip_baru'      => $profile['nip_baru'] ?? null,
            'sobat_id'      => $profile['sobat_id'] ?? null,
            'jenis_kelamin' => $profile['jenis_kelamin'] ?? null,
            'tempat_lahir'  => $profile['tempat_lahir'] ?? null,
            'tanggal_lahir' => $profile['tanggal_lahir'] ?? null,
            'pendidikan'    => $profile['pendidikan'] ?? null,

            // From organization (nested in /api/user/me)
            'kd_satker'  => $organization['kd_satker'] ?? null,
            'jabatan'    => $organization['jabatan'] ?? null,
            'unit_kerja' => $organization['unit_kerja'] ?? null,
            'golongan'   => $organization['golongan'] ?? null,
        ];

        if ($localUser) {
            $localUser->update($userData);
        } else {
            $userData['password'] = null; // No password for SSO users
            $localUser = User::create($userData);

            // // Assign default role to access the panel (Filament Shield)
            // if (method_exists($localUser, 'assignRole')) {
            //     $localUser->assignRole('panel_user');
            // }
        }

        Auth::login($localUser);

        logger()->info('User logged in via SSO', ['user_id' => $localUser->id]);

        return redirect()->intended(route('filament.admin.pages.dashboard', absolute: false));
    }
}
