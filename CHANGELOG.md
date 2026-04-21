# Changelog - ManajemenSurat

## [1.2.0] - 2026-04-21
### Added
- **User Management Resource:**
    - Pembuatan `UserResource` yang komprehensif mendukung Filament v4.
    - Implementasi Form dengan sistem Tabs untuk mengelompokkan data (Akun, Identitas, Organisasi, Data Tambahan, SSO).
    - Dukungan manajemen Role langsung dari form user.
    - Tabel user dengan pratinjau avatar dan kolom yang bisa di-toggle.

### Changed
- **Konfigurasi Filament Shield:**
    - Nonaktifkan `register_role_policy` untuk memberikan kontrol manual lebih lanjut.
    - Refaktor format array metode izin pada konfigurasi shield.

---

## [1.1.0] - 2026-04-17
### Added
- **Integrasi SIPETRA SSO (Socialite):**
    - Implementasi Driver Socialite kustom untuk SIPETRA di `AppServiceProvider`.
    - Konfigurasi `config/services.php` untuk mendukung OAuth2 SIPETRA.
    - Pembuatan `SsoController` untuk menangani redirect dan callback authentication.
- **Manajemen User SSO:**
    - Penambahan field baru pada tabel `users` (sipetra_id, nip, jabatan, dll) melalui migrasi database.
    - Support Avatar SSO melalui interface `HasAvatar` di model `User`.
    - Sinkronisasi otomatis profil user (identitas, organisasi, foto) saat login.
- **UI/UX:**
    - Penambahan tombol "Login via SIPETRA" pada halaman login Filament menggunakan `Render Hook`.
    - Penambahan kolom Foto Profil pada tabel daftar user di Filament.
- **Keamanan:**
    - Penyesuaian kolom `password` menjadi nullable untuk user yang masuk via SSO.
    - Pengaturan Role otomatis (`panel_user`) bagi user baru yang terdaftar via SSO.

---

## Panduan Integrasi SSO SIPETRA
Berikut adalah langkah-langkah teknis untuk mengintegrasikan SSO SIPETRA ke aplikasi client Laravel lainnya:

### 1. Konfigurasi Environment (`.env`)
Tambahkan kredensial yang didapat dari Dashboard SIPETRA:
```env
SIPETRA_CLIENT_ID=your_client_id
SIPETRA_CLIENT_SECRET=your_client_secret
SIPETRA_REDIRECT_URI=http://your-app.test/auth/sipetra/callback
SIPETRA_BASE_URL=https://sipetra.test
```

### 2. Registrasi Socialite Driver
Pada `AppServiceProvider.php`, daftarkan driver kustom pada method `boot()`:
```php
public function boot(): void {
    $socialite = $this->app->make(\Laravel\Socialite\Contracts\Factory::class);
    $socialite->extend('sipetra', function ($app) use ($socialite) {
        $config = $app['config']['services.sipetra'];
        return $socialite->buildProvider(\App\Providers\SipetraSocialiteProvider::class, $config);
    });
}
```

### 3. Setup Model & Database
Pastikan tabel `users` memiliki kolom untuk menampung data dari SSO dan buat kolom `password` menjadi `nullable`.
Implementasikan interface `HasAvatar` pada model `User` untuk menampilkan foto:
```php
public function getFilamentAvatarUrl(): ?string {
    return $this->avatar_url;
}
```

### 4. Handler Auth (Controller)
Gunakan `SsoController` untuk memproses data dari SIPETRA. Gunakan `updateOrCreate` berbasis `email` atau `sipetra_id` untuk menghindari duplikasi user.

### 5. Routing
Daftarkan route untuk redirect dan callback:
```php
Route::get('/auth/sipetra/redirect', [SsoController::class, 'redirect'])->name('sso.redirect');
Route::get('/auth/sipetra/callback', [SsoController::class, 'callback'])->name('sso.callback');
```

---
*Dokumentasi ini dibuat oleh ManajemenSurat Integrator Team.*
