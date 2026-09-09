# Changelog - ManajemenSurat

## [1.5.1] - 2026-09-09
### Changed
- **Branding & UI:**
    - Mengganti favicon bawaan Laravel dengan Logo BPS pada halaman `welcome` dan `landing`.
    - Menghapus aset favicon default (`favicon.ico`, `favicon.svg`, `apple-touch-icon.png`).
    - Menyalin `logo_bps.png` ke `public/favicon.ico` untuk mengatasi masalah browser caching yang sempat menampilkan favicon Laravel saat memuat halaman awal.

### Fixed
- **Manajemen User:**
    - Memperbaiki bug (*error*) di mana foto profil SSO (berupa URL eksternal) terhapus/menjadi kosong ketika form di-simpan (seperti saat mengganti Role). Komponen `FileUpload` sekarang akan mempertahankan URL eksternal jika pengguna tidak mengunggah foto lokal baru.
- **Database & Seeder:**
    - Memperbaiki file `DatabaseSeeder` yang sebelumnya lupa memanggil seeder untuk `KodeSpm` dan `AkunPajak`. Kini data *master* tersebut otomatis ikut dimasukkan saat menjalankan `db:seed`.

## [1.5.0] - 2026-09-04
### Added
- **UI/UX Rekap SP2D & Export:**
    - Penambahan halaman `Rekap Per Pihak` untuk menampilkan rangkuman pajak berdasarkan entitas (NPWP/NIK dan Nama) secara dinamis dengan dukungan *Filter*.
    - Penambahan fitur Export multi-format (CSV, Excel, PDF) terintegrasi pada halaman Data Rekap SP2D maupun Rekap Per Pihak.
    - Format khusus pada ekspor Excel untuk memastikan NIP dan NPWP bertipe *String* sehingga mencegah terjadinya *Scientific Notation*.

### Changed
- **Pembersihan Fitur Lama:**
    - Menghapus tombol dan kelas `Sp2dCoretaxExport` karena fungsionalitas pengelompokannya telah sepenuhnya digantikan oleh halaman `Rekap Per Pihak`.

### Fixed
- **Stabilitas Widget & Konfigurasi:**
    - Perbaikan `BadMethodCallException` pada widget `Sp2dRekapStatsOverview` dan `RekapPajakOverview` dengan menggunakan implementasi trait resmi Filament untuk menginisialisasi *instance* halaman tabel.
    - Perbaikan *typo wildcard* pada `.gitignore` yang sebelumnya memblokir pelacakan file secara luas.

## [1.4.0] - 2026-08-28
### Added
- **UI/UX Rekap SP2D & Export:**
    - Pembaruan `Sp2dRekapResource` untuk menggunakan Modal Edit *inline* sehingga halaman khusus `EditSp2dRekap.php` dihapus.
    - Pembatasan panjang teks (maksimal 25 karakter) dan *tooltip* pada kolom-kolom tabel utama untuk menghindari *scroll* horizontal yang berlebihan.
    - Pembaruan halaman `RekapPerPihak.php` dengan implementasi pemisah ribuan (titik) pada angka/uang agar lebih ramah baca.
    - Penyesuaian `Sp2dCoretaxExport` di mana tipe angka dikonversi menjadi *float* agar terbaca otomatis sebagai *Number* di Microsoft Excel.

### Changed
- **Logika Import & Dev Environment:**
    - Pembaruan `Sp2dImportService` untuk membiarkan filter `Bulan` opsional (tidak wajib).
    - Status SP2D otomatis diset menjadi `perlu_rincian` (alih-alih error) ketika di-simpan tanpa baris rincian potongan.
    - Pembaruan `routes/web.php` untuk memfasilitasi `/dev/login` langsung ke kredensial admin saat `APP_ENV=local`.
    - Mengganti istilah Export Coretax menjadi Export Rekap SP2D (Penamaan lebih umum).

## [1.3.0] - 2026-08-23
### Added
- **Sub-Modul Rekapitulasi Pajak SP2D untuk Coretax:**
    - Penambahan migrasi `update_sp2d_tables_for_coretax` untuk merevisi skema `sp2d_uploads`, `sp2d_rekaps`, dan `sp2d_pajaks`.
    - Pembuatan `Sp2dImportService` untuk memparsing file Excel MyIntress (Monitoring SPP/SPM/SP2D & Monitoring Potongan SPM) menggunakan OpenSpout.
    - Implementasi `ProcessSp2dImport` job untuk pemrosesan import secara background/asynchronous.
    - Pembuatan `Sp2dCoretaxExport` untuk mengekspor data pajak yang valid ke dalam format Pivot Excel.
    - Integrasi form import multi-file dan filter periode pada `Sp2dRekapResource`.
    - Implementasi validasi ketat (balance checking) pada form edit `Sp2dRekapResource` untuk jalur transaksi banyak pihak.
    - Penambahan test unit dan integrasi `Sp2dCoretaxIntegrationTest`.

### Changed
- **UI/UX Rekap SP2D:**
    - Penambahan fitur salin otomatis (copyable) pada nomor SP2D.
    - Perbaikan tampilan tabel dengan mematikan fungsi klik baris (mencegah klik tidak sengaja saat menyeleksi teks).
    - Memindahkan posisi *scrollbar* tabel ke atas khusus pada modul SP2D untuk memudahkan navigasi horizontal.
    - Pengubahan format angka dan mata uang ke format Indonesia (titik sebagai pemisah ribuan).
    - Fitur *Toggleable* pada kolom tabel untuk memungkinkan pengguna menyembunyikan/menampilkan kolom spesifik (seperti Pajak).
    - Perbaikan fitur *sorting* pada kolom `total_pajak` yang menggunakan atribut *accessor*.

## [1.2.0] - 2026-04-21
### Added
- **User Management Resource:**
    - Pembuatan `UserResource` yang komprehensif mendukung Filament v4.
    - Implementasi Form dengan sistem Tabs untuk mengelompokkan data (Akun, Identitas, Organisasi, Data Tambahan, SSO).
    - Dukungan manajemen Role langsung dari form user.
    - Tabel user dengan pratinjau avatar dan kolom yang bisa di-toggle.
- **Security Policies:**
    - Pembuatan dan pembaruan struktur `Policy` untuk seluruh modul (User, Bmn, Pegawai, Ruangan, Surat, dll) guna mendukung integrasi Filament Shield.

### Changed
- **Konfigurasi Filament Shield:**
    - Nonaktifkan `register_role_policy` untuk memberikan kontrol manual lebih lanjut.
    - Refaktor format array metode izin pada konfigurasi shield.

### Fixed
- **Bug Navigation Group:**
    - Perbaikan `FatalError` pada `UserResource` dengan menyesuaikan type hint `$navigationGroup` agar sesuai dengan class induk (Filament v4).
- **Bug Table Actions:**
    - Perbaikan `FatalError` pada `UserResource` dengan mengubah namespace `Action` (Edit, Delete, Bulk) ke `Filament\Actions` sesuai standar Filament v4.
- **Bug Avatar Upload:**
    - Penghapusan method `circleDimensions()` yang tidak tersedia pada komponen `FileUpload` di Filament v4.
    - Penambahan preview foto saat ini pada form edit user menggunakan `Placeholder`. Hal ini memperbaiki masalah foto tidak muncul di form jika data berupa URL eksternal (SSO).
    - Perbaikan namespace `Placeholder` ke `Filament\Forms\Components`.
- **Integrasi HasAvatar:**
    - Implementasi interface `HasAvatar` pada model `User` untuk sinkronisasi foto profil (lokal & SSO) di seluruh panel Filament.

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
