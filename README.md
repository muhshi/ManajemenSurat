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
- **Manajemen BMN (Barang Milik Negara)**:
  - **Data Aset BMN**: Pencatatan aset dengan 4 tab (Identitas, Lokasi & PJ, Nilai Aset, Status).
  - **Data Ruangan**: Master ruangan per lantai dengan sinkronisasi data DBR/DBL SIMAN.
  - **Data Pegawai**: Master pegawai sebagai penanggung jawab aset (polymorphic).
  - **Import dari SIMAN**: Upload file Excel ekspor SIMAN, mapping otomatis ke database dengan summary hasil import.
  - **Dashboard BMN**: Widget statistik total aset, kondisi, henti guna, nilai buku, chart distribusi jenis BMN, dan tabel top ruangan.

## Teknologi

Aplikasi ini dibangun menggunakan teknologi modern:

- **Framework**: [Laravel 12](https://laravel.com)
- **Admin Panel**: [FilamentPHP v4](https://filamentphp.com)
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

### [2026-04-22]
#### Fixed
- **Ekstraksi PDF Python (SEP-BP)**: 
  - Memperbaiki kompatibilitas path Python Virtual Environment agar mendukung sistem Windows (`Scripts/python.exe`) dan Linux.
  - Memperbaiki metode pemanggilan *background process* menggunakan array untuk menghindari masalah *escaping* tanda petik pada Windows.
  - Meningkatkan robustnes Regex pada parser PDF (`parse_buku_persediaan.py`) agar mendukung format tanggal bulan singkatan (misal: `01-MAR-26`) dan membersihkan karakter whitespace yang tidak perlu pada Kode/Nama Barang.
  - Mengubah konfigurasi `FileUpload` agar secara eksplisit menggunakan disk `public` untuk menghindari *File Not Found error* pada antrian job.
- **Tampilan Nota Permintaan**: Menghapus sisa-sisa elemen border/span dan mengatur ulang margin pada kolom tanda tangan (`inventory-print.blade.php`) untuk memastikan teks "Yang Menyerahkan" (yang berasal dari cache server) tergantikan dengan tata letak yang bersih.

### [2026-04-21]
#### Fixed
- **Kompatibilitas Windows**: Memperbaiki perintah `composer dev` agar dapat berjalan di sistem operasi Windows.
  - Menjalankan `npm install` untuk menginstal Vite dan dependensi lainnya.
  - Menghapus perintah `php artisan pail` dari skrip `dev` dan `dev:ssr` di `composer.json` karena ekstensi `pcntl` tidak tersedia di Windows.
  - Menyesuaikan konfigurasi `concurrently` untuk menghapus panel logs yang bergantung pada Pail.

### [Unreleased]
#### Added
- **Modul Manajemen BMN**: Implementasi lengkap modul Barang Milik Negara berbasis data SIMAN.
  - Migration & Model `ruangans`, `pegawais`, `bmns` dengan relasi polymorphic `penanggung_jawab`.
  - **RuanganResource**: CRUD ruangan dengan badge kode, filter lantai, dan count BMN per ruangan.
  - **PegawaiResource**: CRUD pegawai dengan badge jumlah BMN ditanggung dan filter status aktif.
  - **BmnResource**: Form 4 tab (Identitas, Lokasi & PJ, Nilai Aset, Status & Flags), filter multi-kriteria, badge warna per jenis dan kondisi, auto-hitung nilai buku.
  - **Import dari SIMAN**: Action upload file `.xlsx` ekspor SIMAN dengan mapping otomatis, upsert berdasarkan `kode_register`, resolve ruangan & pegawai, summary hasil import.
  - **BmnStatsWidget**: 6 metrik statistik (total, kondisi 3 level, henti guna, nilai buku total).
  - **BmnPerJenisChart**: Donut chart distribusi aset per jenis BMN.
  - **BmnPerRuanganTable**: Tabel top ruangan berdasarkan jumlah aset.
  - Seeder `RuanganSeeder` (11 ruangan dari DBR/DBL SIMAN) dan `PegawaiSeeder` (2 pegawai).
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
- **Dockerfile**: Menambahkan build stage Node.js (Vite) untuk kompilasi assets secara otomatis, post-install scripts (`package:discover`, `filament:upgrade`, `storage:link`), dan pembuatan direktori `storage/framework`.
- **docker-compose.yml**: Menambahkan shared `storage_data` volume antara container web dan queue worker agar file upload dan log dapat diakses bersama.
- Skrip Upload SEP-BP sekarang memproses ekstraksi melalui **Queue (Background Job)** (`ProcessInventoryUpload`) alih-alih dieksekusi sinkron.
- Optimalisasi penyisipan master data Barang ke **Upsert Massal** (`Item::upsert`) menjadi 1 kueri database.
- Format tampilan kolom `Filename` pada grid upload dipersingkat dengan `basename()`.

#### Fixed
- Duplikasi transaksi diatasi dengan integrasi **Sidik Jari SHA-256 (`tx_hash`)** unik sebagai penanda primer.
- Memperbaiki format "Nama Barang" pada cetak PDF Nota Permintaan menggunakan Title Case.

### [2026-04-14]
#### Changed
- **Upgrade Filament v3 → v4** (`v3.3.45` → `v4.10.0`): Upgrade mayor framework admin panel menggunakan official automated upgrade script.
- **Upgrade filament-shield v3 → v4** (`v3.9.10` → `v4.2.0`): Kompatibel dengan Filament v4, config diperbarui ke format ShieldConfig baru.
- **Migrasi struktur direktori ke v4**: Semua resource dipindahkan ke subfolder per model (`SuratKeluars/`, `SuratMasuks/`, `Disposisis/`, dst.) menggunakan artisan command `filament:upgrade-directory-structure-to-v4`.
- Namespace semua Resource dan Pages diperbarui otomatis oleh upgrade script.
- `config/filament-shield.php` diperbarui ke format v4 (ShieldConfig object).

### [2026-04-15]
#### Added
- **Otomatisasi Build Assets di Docker**: Menambahkan multi-stage build pada `Dockerfile` untuk menginstal Node.js dan menjalankan `npm run build` secara otomatis saat pembuatan image. Ini memastikan file CSS/JS (Vite) selalu terupdate di production.
- **Upgrade PHP di Docker**: Menaikkan versi PHP dari 8.3 ke 8.4 di base image `frankenphp` untuk mendukung dependency terbaru (Symfony 8.0).
- **Panduan Update di Server**: Menambahkan bagian "CARA UPDATE" di `deploy-docker.md` untuk memudahkan sinkronisasi code, rebuild image, dan pembersihan cache Laravel/Filament.
- **Migration Data Cleanup**: Migrasi untuk membersihkan karakter *newline* (`\n`) pada kolom `no_dok` di tabel `transactions` yang disebabkan oleh limitasi parser PDF sebelumnya.

#### Changed
- **Layout Signature Nota Permintaan**: Menyesuaikan posisi jabatan "Kasubbag Umum" agar berada di bawah "SETUJU DIKELUARKAN" dan di atas garis tanda tangan, sesuai standar format baku.
- **Grouping Nota Permintaan**: Logika pengelompokan transaksi pada cetakan Nota Permintaan kini menggunakan kombinasi Tanggal dan Nomor Dokumen agar transaksi dengan nomor berbeda di hari yang sama terpisah tabelnya.
- **Lebar Kolom Export**: Menyesuaikan lebar kolom "No Dok" di ekspor Excel agar teks panjang tidak terpotong atau terbungkus ke baris baru.

#### Fixed
- **Deployment Staleness**: Memperbaiki isu di mana `git pull` di server tidak mengubah tampilan aplikasi karena container masih menggunakan image lama dan assets belum di-build.
- **Multiline No Dok Parser**: Memperbaiki skrip Python `parse_buku_persediaan.py` agar mampu menangkap sambungan nomor dokumen yang terpotong ke baris baru pada file PDF sumber (SEP-BP).
- **No Dok Row Split**: Memperbaiki isu di mana nomor dokumen terbelah menjadi 3 baris pada PDF laporan dengan menggunakan CSS `white-space: nowrap` dan pembersihan data saat import.
