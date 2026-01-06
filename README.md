# Manajemen Surat BPS Kabupaten Demak

Aplikasi Manajemen Surat untuk BPS Kabupaten Demak yang dibangun menggunakan Laravel dan Filament. Aplikasi ini berfungsi untuk mengelola arsip surat masuk, surat keluar, disposisi, dan SK, serta dilengkapi dengan fitur manajemen pengguna dan integrasi template surat.

## Fitur Utama

- **Manajemen Surat Masuk**: Pencatatan dan pengarsipan surat yang masuk.
- **Manajemen Surat Keluar**: 
  - Pembuatan surat keluar.
  - **Generasi Dokumen**: Otomatisasi pembuatan file surat (.docx) berdasarkan template.
- **Disposisi Surat**: Pengaturan alur disposisi surat kepada pegawai terkait.
- **Manajemen SK**: Pengelolaan Surat Keputusan (SK).
- **Manajemen Pengguna (User Management)**:
  - **Hak Akses (Roles)**: Menggunakan Filament Shield untuk pengaturan hak akses yang granular (misal: Super Admin, Pegawai).
  - **Profil Pengguna**: Setiap pengguna dapat mengelola profil mereka sendiri (Nama, NIP, Jabatan, dll).
  - **Import & Export**: Fitur import user dari Excel dan download template import (khusus Super Admin).
  - **WhatsApp**: Penyimpanan nomor WhatsApp untuk integrasi notifikasi (future dev).

## Teknologi

Aplikasi ini dibangun menggunakan teknologi modern:

- **Framework**: [Laravel 12](https://laravel.com)
- **Admin Panel**: [FilamentPHP v3](https://filamentphp.com)
- **Database**: MySQL
- **Plugins & Packages**:
  - `bezhansalleh/filament-shield`: Manajemen Role & Permission.
  - `phpoffice/phpword`: Generasi dokumen Word dari template.
  - `spatie/laravel-settings`: Pengaturan aplikasi.

## Persyaratan Sistem

- PHP 8.2 atau lebih baru
- Composer
- Node.js & NPM
- MySQL

## Instalasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di lingkungan lokal:

1.  **Clone Repository**
    ```bash
    git clone https://github.com/username/manajemen-surat.git
    cd manajemen-surat
    ```

2.  **Install Dependencies**
    ```bash
    composer install
    npm install
    ```

3.  **Konfigurasi Environment**
    Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database Anda.
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Setup Database**
    Buat database baru di MySQL, lalu jalankan migrasi dan seeder.
    ```bash
    php artisan migrate --seed
    ```
    *Seeder akan membuat user `super_admin` default dan role dasar.*

5.  **Setup Shield (Permissions)**
    Jika diperlukan, generate policy dan permission baru:
    ```bash
    php artisan shield:generate
    ```

6.  **Jalankan Aplikasi**
    ```bash
    npm run build
    php artisan serve
    ```
    Akses aplikasi di `http://localhost:8000/admin`.

## Panduan Penggunaan

### Login
Gunakan kredensial yang telah dibuat melalui seeder atau oleh administrator.

### Manajemen User (Khusus Super Admin)
1.  Masuk ke menu **Profil Pengguna**.
2.  Anda dapat melihat daftar pengguna dalam bentuk tabel.
3.  Gunakan tombol **Import Excel** untuk menambahkan pengguna secara massal.
4.  Gunakan **Download Template** untuk mendapatkan format CSV yang sesuai.

### Edit Profil
- **Super Admin**: Dapat mengedit seluruh data pengguna termasuk Role.
- **Pegawai/User Lain**: Saat mengklik menu Profil, akan langsung diarahkan ke halaman edit profil sendiri. Hanya dapat mengubah data diri dan password (optional).

## License

The MIT License (MIT).
