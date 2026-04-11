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

4.  **Setup Ekstraktor PDF (SEP-BP)**
    Untuk modul Buku Persediaan, dibutuhkan environment Python.
    ```bash
    mkdir -p app/Scripts
    python3 -m venv app/Scripts/venv
    source app/Scripts/venv/bin/activate
    pip install pdfplumber
    ```

5.  **Setup Database**
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

## Changelog

Semua perubahan yang mencolok pada project ini akan didokumentasikan di bawah. Menggunakan format [Keep a Changelog](https://keepachangelog.com/id/1.0.0/).

### [Unreleased]
#### Added
- Modul ekstraksi PDF Rincian Buku Persediaan (SEP-BP) tersendiri di `app/Scripts/parse_buku_persediaan.py` menggunakan pustaka Python `pdfplumber`.
- Output parsing PDF tervalidasi menggunakan format JSON dari script ekstraktor data.
- Setup environment virtual khusus Python (`venv`) untuk mengisolir *dependencies* parser (pdfplumber) di dalam folder `app/Scripts` agar proses migrasi tidak terhambat.
- **UploadProgressWidget**: Fitur pemantauan Real-time progress upload buku persediaan berbentuk Terminal Log UI, polling 2 detik.

#### Changed
- Skrip Upload SEP-BP sekarang memproses ekstraksi melalui **Queue (Background Job)** (`ProcessInventoryUpload`) alih-alih dieksekusi sinkron untuk menghindari batas waktu Time-out PHP (30 detik).
- Optimalisasi penyisipan master data Barang dari Eloquent Insert loop ke **Upsert Massal** (`Item::upsert`) menjadi 1 kueri database (mengurangi waktu penambahan item dari hitungan menit menjadi milidetik).
- Format tampilan kolom `Filename` pada grid upload dipersingkat membuang absolute path dengan `basename()`.
- Waktu `timeout` pada Worker ditingkatkan menjadi 600 detik baik pada properti Job `timeout` maupun dev script `php artisan queue:listen`.

#### Fixed
- Duplikasi transaksi laporan persediaan yang sama berulang kali diatasi dengan integrasi **Sidik Jari SHA-256 (`tx_hash`)** unik sebagai penanda primer. Transaksi kembar hanya akan tersinkronisasi / memicu UPSERT menimpa data bukannya menduplikat.
