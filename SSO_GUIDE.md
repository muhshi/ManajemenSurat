# Panduan Integrasi SSO SIPETRA

Dokumen ini menjelaskan implementasi Single Sign-On (SSO) menggunakan server **SIPETRA** sebagai Identity Provider (IdP) dan aplikasi **Manajemen Surat** sebagai Client (Service Provider).

## 1. Arsitektur
Integrasi ini menggunakan protokol **OAuth2** dengan *Authorization Code Grant*. Implementasi di sisi klien dibangun di atas paket **Laravel Socialite**.

### Komponen Utama:
- **Provider**: `App\Providers\SipetraSocialiteProvider` (Custom Socialite Driver).
- **Controller**: `App\Http\Controllers\Auth\SsoController` (Handler Redirect & Callback).
- **Config**: `config/services.php` (Credential & Scopes).

---

## 2. Konfigurasi

### 2.1 Environment (.env)
Tambahkan variabel berikut pada file `.env` klien:
```env
SIPETRA_CLIENT_ID="your-client-id"
SIPETRA_CLIENT_SECRET="your-client-secret"
SIPETRA_REDIRECT_URI="https://manajemensurat.test/auth/sipetra/callback"
SIPETRA_BASE_URL="https://sipetra.test"
```

### 2.2 Services Config
Konfigurasi scope dan endpoint di `config/services.php`:
```php
'sipetra' => [
    'client_id' => env('SIPETRA_CLIENT_ID'),
    'client_secret' => env('SIPETRA_CLIENT_SECRET'),
    'redirect' => env('SIPETRA_REDIRECT_URI'),
    'base_url' => env('SIPETRA_BASE_URL'),
    'scopes' => [
        'profile:read',           // Nama, Email, Avatar
        'identity_pegawai:read',  // NIP
        'employee:read',          // Jabatan, Golongan
        'contact:read'            // Nomor HP
    ],
],
```

---

## 3. Implementasi Klien

### 3.1 Custom Socialite Provider
Provider ini bertanggung jawab untuk komunikasi tingkat rendah dengan server SSO (Authorize, Token, dan Fetch User).
- **Endpoint Profil**: `GET /api/user` (Mendukung dynamic scopes).
- **Scope Separator**: Menggunakan spasi (` `).

### 3.2 Registrasi Provider
Didaftarkan pada `AppServiceProvider.php`:
```php
public function boot(): void
{
    \Laravel\Socialite\Facades\Socialite::extend('sipetra', function ($app) {
        $config = $app['config']['services.sipetra'];
        return \Laravel\Socialite\Facades\Socialite::buildProvider(SipetraSocialiteProvider::class, $config);
    });
}
```

---

## 4. Alur Autentikasi (`SsoController`)

### 4.1 Redirect
Mengarahkan pengguna ke halaman login Sipetra.
```php
public function redirect() {
    return Socialite::driver('sipetra')->redirect();
}
```

### 4.2 Callback & User Linking
Setelah login di server berhasil, server mengirimkan `code` kembali ke klien. Klien akan:
1. Menukarkan `code` dengan `access_token`.
2. Mengambil data user dari `/api/user`.
3. **Linking Logic**: 
   - Cari user berdasarkan `sipetra_id`.
   - Jika tidak ketemu, cari berdasarkan `email`.
   - Jika tetap tidak ketemu, buat user baru.
4. **Data Sync**: Selalu memperbarui data profil lokal (NIP, Jabatan, Golongan, HP) setiap kali login SSO berhasil.

---

## 5. Mapping Data JSON
Berikut adalah cara mapping JSON dari Sipetra ke Model `User` lokal:

| JSON Key (Sipetra) | Local DB Column | Scope Required |
| :--- | :--- | :--- |
| `id` | `sipetra_id` | `profile:read` |
| `name` | `name` | `profile:read` |
| `email` | `email` | `profile:read` |
| `nip` | `nip` | `identity_pegawai:read` |
| `employee.jabatan` | `jabatan` | `employee:read` |
| `employee.golongan` | `golongan` | `employee:read` |
| `phone_number` | `nomor_hp` | `contact:read` |

---

## 6. Troubleshooting
- **Invalid Scope**: Pastikan scope di `config/services.php` terdaftar di server SSO (cek `AuthServiceProvider` di Sipetra).
- **404 pada Profile API**: Pastikan endpoint di `SipetraSocialiteProvider` diarahkan ke `/api/user` (bukan `/api/user/me`).
- **Invalid Key Supplied**: Jalankan `php artisan passport:keys` di sisi server SSO.
