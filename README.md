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
- **Manajemen Persediaan**:
  - **Ekstraksi Data**: Otomatisasi pengambilan data dari PDF Rincian Buku Persediaan (SEP-BP).
  - **Generasi Bon Harian**: Pembuatan Nota Permintaan / Bon Permintaan barang secara otomatis.
  - **Kartu Kendali Persediaan**: Ekspor laporan mutasi barang tahunan per item ke Excel multi-sheet, dengan saldo carry-over antar tahun.
  - **Permintaan Barang**: Modul pengajuan permintaan barang dengan repeater item dan tanda tangan digital.

## Teknologi

Aplikasi ini dibangun menggunakan teknologi modern:

- **Framework**: [Laravel 12](https://laravel.com)
- **Admin Panel**: [FilamentPHP v3](https://filamentphp.com)
- **Database**: MySQL
- **Plugins & Packages**:
  - `bezhansalleh/filament-shield`: Manajemen Role & Permission.
  - `maatwebsite/excel`: Ekspor dan impor data Excel.
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
- Modul **Permintaan Barang** baru: resource Filament dengan form nama peminta, tanggal, repeater daftar barang (nama, jumlah, satuan, keterangan), dan **Tanda Tangan Digital** berbasis HTML5 Canvas (Alpine.js, touch-enabled, disimpan sebagai Base64 PNG).
- Tabel `permintaan_barangs` dan `permintaan_barang_items` beserta models dengan relasi `hasMany` / `belongsTo`.
- **Export Kartu Kendali Tahunan**: export XLSX sekarang filter per tahun yang dipilih via modal di UI, dengan **saldo carry-over** dari akhir tahun sebelumnya tampil sebagai baris "Saldo Awal Tahun XXXX" di tabel rincian.
- Fitur **Export Kartu Kendali Persediaan** ke Excel (`.xlsx`) dengan format multi-sheet per item barang, berisi tabel ringkasan bulanan dan rincian transaksi lengkap dengan logo BPS.
- Fitur **Generasi Bon Harian** (Nota Permintaan) otomatis berdasarkan pengelompokan transaksi persediaan per tanggal.
- Integrasi `maatwebsite/excel` (Laravel Excel) untuk mendukung fitur ekspor laporan ke format spreadsheet (XLSX).
- Modul ekstraksi PDF Rincian Buku Persediaan (SEP-BP) tersendiri di `app/Scripts/parse_buku_persediaan.py` menggunakan pustaka Python `pdfplumber`.
- **UploadProgressWidget**: Fitur pemantauan Real-time progress upload buku persediaan berbentuk Terminal Log UI, polling 2 detik.

#### Changed
- **Kartu Kendali**: tombol export sekarang membuka modal pilih tahun sebelum download.
- **Print Laporan Nota Permintaan**: posisi label "Kasubbag Umum" dipindah ke bawah nama dan NIP penandatangan (setelah garis TTD), bukan di samping tulisan "SETUJU DIKELUARKAN".
- **Dockerfile**: Menambahkan post-install scripts (`package:discover`, `filament:upgrade`, `storage:link`) dan pembuatan direktori `storage/framework`.
- **docker-compose.yml**: Menambahkan shared `storage_data` volume antara container web dan queue worker agar file upload dan log dapat diakses bersama.
- Skrip Upload SEP-BP sekarang memproses ekstraksi melalui **Queue (Background Job)** (`ProcessInventoryUpload`) alih-alih dieksekusi sinkron.
- Optimalisasi penyisipan master data Barang ke **Upsert Massal** (`Item::upsert`) menjadi 1 kueri database.
- Format tampilan kolom `Filename` pada grid upload dipersingkat dengan `basename()`.

#### Fixed
- Duplikasi transaksi diatasi dengan integrasi **Sidik Jari SHA-256 (`tx_hash`)** unik sebagai penanda primer.
- Memperbaiki format "Nama Barang" pada cetak PDF Nota Permintaan menggunakan Title Case.
