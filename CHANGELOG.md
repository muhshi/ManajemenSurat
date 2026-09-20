# Changelog - ManajemenSurat

## [2.0.7] - 2026-09-20
### Fixed
- **Chrome Unresponsive Saat Simpan Rincian Pajak:** Menghapus `->poll('5s')` dari tabel Data Rekap SP2D. Polling setiap 5 detik menyebabkan browser mengantri request refresh tabel bersamaan dengan request simpan Livewire (delete + createMany pajak), membuat Chrome tampak *wait/unresponsive*. Tombol **Segarkan Data** sudah tersedia untuk refresh manual.

## [2.0.6] - 2026-09-19
### Added
- **Export Excel Multi-Sheet & Cell Tooltips (Rekap Per Pihak):**
  - Menambahkan sheet rincian transaksi detail SP2D (`RekapPerPihakDetailSheetExport`) berdampingan dengan sheet rekapitulasi untuk setiap bulan/periode yang diekspor.
  - Menambahkan fitur **Excel Cell Comment/Note (Tooltip)** pada setiap cell nominal potongan pajak di Sheet Rekap, sehingga saat kursor diarahkan ke cell angka potongan, pengguna dapat langsung melihat daftar nomor SP2D, tanggal, dan nominal pembentuknya tanpa harus berpindah sheet.
- **Lampiran Rincian Transaksi SP2D pada Export PDF:**
  - Menambahkan halaman lampiran rincian transaksi SP2D per pihak pada berkas PDF (`rekap-per-pihak.blade.php`), lengkap dengan bookmark outline navigasi PDF untuk melompat langsung ke lampiran.
- **Opsi Export CSV Rincian SP2D:**
  - Menyediakan tombol aksi terpisah `Export CSV (Rincian SP2D)` di samping `Export CSV (Rekap)` pada halaman Rekap Per Pihak agar data granular per transaksi SP2D dapat diolah secara fleksibel pada aplikasi spreadsheet.

## [2.0.5] - 2026-09-18
### Added
- **Indikator Total Rincian Pajak di Atas Daftar:** Menambahkan placeholder status total rincian pajak saat ini di bagian atas daftar rincian pihak/penerima pada form SP2D (`Sp2dRekapResource`) sehingga status kesesuaian nominal (Sesuai, Kurang, atau Lebih) langsung terpantau tanpa harus menggulir ke bagian bawah daftar.

### Changed
- **Refactor Kalkulasi Total Rincian:** Memusatkan logika kalkulasi dan rendering status total rincian pajak ke dalam static helper method `getTotalRincianSaatIniContent` pada `Sp2dRekapResource` agar konsisten digunakan oleh indikator di bagian atas maupun bawah.
- **Penyelarasan Bab 8 Panduan Gambar Buku Pedoman:**
  - Menyusun ulang urutan 14 tangkapan layar pada Bab 8 berkas `docs/Pedoman_Penggunaan_Modul_Rekap_SP2D.docx` agar runtut sesuai kronologi alur operasional: diawali dari Import MyIntress (Bab 3.5), tabel Data Rekap & ekspor (Bab 3.1-3.4/3.7), form slide-over edit rincian (Bab 3.6 A-B), unggah Excel dan hasil ekstraksi seimbang (Bab 3.6 C & F), input manual autocomplete (Bab 3.6 D), pengelolaan jalur UP (Bab 3.6 F), notifikasi status valid (Bab 3.6 F), halaman Rekap Per Pihak (Bab 4), serta master akun pajak dan kode SPM (Bab 5).
  - Memperbaiki judul bab `BAB 8 - PANDUAN GAMBAR` dan menambahkan paragraf pengantar.
  - Memperbaiki caption seluruh gambar dengan penomoran resmi `Gambar 1` s.d. `Gambar 14` menggunakan field Word (`SEQ Gambar \* ARABIC`) dan deskripsi formal yang merujuk pada bab terkait.
  - Menambahkan tautan silang referensi gambar `(lihat Gambar X)` pada narasi penjelasan Bab 3, Bab 4, dan Bab 5.

## [2.0.4] - 2026-09-15
### Changed
- **Refactor Jalur Transaksi (GUP -> UP):**
  - Merefaktor istilah dan nilai jalur transaksi dari `gup` menjadi `up` pada database, model, dan seluruh antarmuka Filament.
  - Alasan refactor: Kategori jalur ini mencakup seluruh keluarga Uang Persediaan / Revolving Funds (UP [311], TUP [321], GUP [312], GUP-KKP [317], dan PTUP [322]), bukan hanya GUP.
  - Membuat migrasi database `2026_09_15_221000_refactor_jalur_gup_to_up.php` yang memperbarui enum dan memigrasikan data riwayat `gup` menjadi `up` pada tabel `sp2d_rekaps` dan `kode_spms` (mendukung driver MySQL/MariaDB dan SQLite).
  - Memperbarui seeder `KodeSpmSeeder` untuk kode 311, 321, 312, 317, dan 322 menjadi jalur `up`.
  - Memperbarui deteksi otomatis jalur pada `Sp2dImportService` dengan regex pencocokan keluarga UP (`up|gup|tup|ptup`) serta fallback kode SPM.
  - Memperbarui form dan tabel `KodeSpmResource`, dropdown filter dan badge tabel `Sp2dRekapResource`, serta teks indikator `(UP: Tidak Terikat Target)`.
  - Memperbarui format tampilan jalur pada sheet export Excel (`Sp2dRekapPerBulanSheet`) dan template PDF (`sp2d-rekap.blade.php`).

## [2.0.3] - 2026-09-15
### Added
- **Landing Page Modern v2.0:** Memperbarui antarmuka beranda publik (`landing.blade.php`) dengan desain modern bertema BPS, glassmorphism, dan tipografi Plus Jakarta Sans. Seluruh fitur dan modul terbaru kini ditampilkan secara komprehensif: Rekapitulasi SP2D & Pajak CoreTax, Surat Keluar & SK Otomatis, Surat Masuk & Disposisi Digital, Persediaan ATK/ARK, Aset BMN, Agenda & Notulensi Rapat, serta integrasi tombol login SSO SIPETRA.
- **Export PDF via mPDF:** Menginstal paket `mpdf/mpdf` dan memigrasikan proses export PDF (pada *Rekap Per Pihak* dan *Data Rekap SP2D*) dari DomPDF ke mPDF. mPDF mendukung fitur PDF outline/bookmark secara native melalui tag `<bookmark content="..." level="0" />`, sehingga panel bookmark navigasi di PDF viewer (Chrome, Edge, Adobe Reader) kini berfungsi dan dapat diklik untuk melompat antar periode/bulan.


### Fixed
- **Hak Akses & Otorisasi Menu (Filament Shield):** Perbaikan menu *Kode SPM*, *Akun Pajak*, dan *Rekap Per Pihak* yang masih terlihat oleh role `pegawai`. Penyebab: `AkunPajak` dan `KodeSpm` belum memiliki file Policy Laravel, dan halaman kustom `RekapPerPihak` belum menggunakan trait `HasPageShield`. Akibatnya, Filament mengizinkan semua user secara default meskipun permission sudah di-uncheck pada menu Peran. Solusi: Membuat `AkunPajakPolicy`, `KodeSpmPolicy`, memasang `HasPageShield` pada `RekapPerPihak`, serta menambahkan migrasi permission Shield untuk ketiga entitas tersebut.
- **Deploy Script — Volume Vendor Synchronization:** Menambahkan perintah sinkronisasi `composer install` ke dalam container saat build di `deploy.sh`. Hal ini untuk mencegah anonymous volume `/app/vendor` menutupi paket-paket baru (seperti `mpdf/mpdf`) yang baru diinstal.
- **Export Excel — Invalid Cell Coordinate:** Perbaikan crash `PhpSpreadsheet\Exception: Invalid cell coordinate [1` saat Export Excel di halaman *Rekap Per Pihak*. Penyebab: `columnFormats()` menggunakan aritmatika `chr(65 + col)` untuk menghasilkan huruf kolom Excel. Ketika jumlah akun pajak melebihi ~23 kolom, `chr()` menghasilkan karakter non-huruf seperti `[` (ASCII 91) yang ditolak PhpSpreadsheet. Solusi: Ganti dengan `Coordinate::stringFromColumnIndex()` yang menghasilkan kolom multi-huruf (`AA`, `AB`, ...) secara benar.




## [2.0.2] - 2026-09-14

### Fixed
- **Export Excel — Format Rupiah:** Kolom nominal (Bruto, Total Potongan, Netto) pada sheet Excel *Data Rekap SP2D* dan *Rekap Per Pihak* kini tampil sebagai angka dengan format `#,##0` (pemisah ribuan otomatis sesuai locale Excel). Sebelumnya nilai dikirim sebagai string hasil `number_format` sehingga Excel membaca "499" bukan "499.000". Solusi: kirim `float` mentah ke Excel, format ditangani via `WithColumnFormatting`.
- **PDF Bookmark — Rekap Per Pihak:** `bookmark-label` pada `h2` dan `h3` di `rekap-per-pihak.blade.php` kini dibuat via variabel `@php` (bukan `{{ }}`), identik dengan perbaikan sebelumnya di `sp2d-rekap.blade.php`. Akibatnya panel navigasi PDF kini menampilkan nama bulan yang benar sebagai nav pane.

## [2.0.1] - 2026-09-14
### Changed
- **Buku Pedoman Penggunaan:** Sinkronisasi pembaruan tata letak dan judul berkas Word `docs/Pedoman_Penggunaan_Modul_Rekap_SP2D.docx` (Versi 2.0).

### Fixed
- **PDF Export (Data Rekap SP2D):** Perbaikan bookmark/outline navigasi per bulan pada file PDF yang dihasilkan. Sebelumnya properti CSS `bookmark-label` pada `h1` dirender di-dalam `style=""` Blade yang menyebabkan quote ter-HTML-encode sehingga DomPDF tidak bisa membaca nilai bookmark. Sekarang label dibuat via variabel PHP (`@php`) sebelum di-render ke inline style.

### Added
- **Upload Rincian via Excel — Uang Lembur:** Menambahkan opsi "Uang Lembur" pada dropdown Jenis File Excel di form edit SP2D. Sistem membaca kolom `pajak` sebagai nominal PPh Pasal 21 (kode akun 411121), kolom `nmpeg`/`nmrek` sebagai nama penerima, dan kolom `npwp`/`nip` sebagai identitas pajak. Format file yang didukung adalah `Hasil_Excel_Uang_Lembur_export_*.xlsx` dari sistem penggajian.
- **Config DomPDF:** Publish file `config/dompdf.php` untuk memungkinkan kustomisasi opsi PDF renderer di masa mendatang.

## [2.0.0] - 2026-09-14
### Added
- **Dokumentasi & Buku Pedoman:**
    - Membuat dan memperbarui berkas Word `docs/Pedoman_Penggunaan_Modul_Rekap_SP2D.docx` (Versi 2.0) yang mencakup panduan operasional lengkap Modul Rekap SP2D & Pajak BPS Kabupaten Demak.
    - Menambahkan petunjuk penggunaan fitur-fitur terbaru: layout filter di atas tabel (*above content*), posisi tombol edit paling kiri, scrollbar horizontal ganda, import MyIntress, upload rincian pajak via Excel (Gaji, Tukin, Uang Makan), fitur hapus terpilih/semua, indikator keseimbangan pajak (termasuk GUP), ekspor multi-format (CSV, Excel multi-sheet, PDF landscape dengan outline/bookmark), serta SOP bulanan Bendahara & Troubleshooting.

## [1.5.2] - 2026-09-12
### Changed
- **Filter Data Rekap SP2D:** Mengubah tampilan filter dari dropdown panel tertutup (SelectFilter) menjadi dropdown Bulan, Tahun, Jenis SPM, dan Jalur Transaksi yang tampil langsung di atas tabel (`AboveContent`), konsisten dengan halaman Rekap Per Pihak.
- **Nama Sheet Excel SP2D:** Format nama sheet Excel diubah dari `PERIODE_01` menjadi `{tahun}_{bulan}_{namaBulan}` (contoh: `2026_01_Januari`).
- **Export Excel Rekap Per Pihak:** Export Excel kini menghasilkan file multi-sheet, di mana setiap bulan memiliki sheet tersendiri dengan nama format `{tahun}_{bulan}_{namaBulan}`.
- **Export PDF Rekap Per Pihak:** Setiap bulan kini tampil di halaman baru (page break) pada file PDF.
- **PDF Bookmark Navigasi:** Kedua PDF (Data Rekap SP2D & Rekap Per Pihak) kini memiliki bookmark panel navigasi per bulan/periode menggunakan DomPDF `bookmark-level` dan `bookmark-label`, sehingga dapat di-navigate langsung dari panel bookmark PDF reader.
- **Mekanisme Download Export:** Seluruh export (CSV, Excel, PDF) kini diunduh melalui route server (`response()->download()`) dengan `window.open()`, menggantikan `window.location.href` ke URL statis. Solusi ini memastikan file dapat diunduh di Chrome yang dikelola organisasi (managed Chrome).

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
- **Validasi Import SP2D:**
    - Menambahkan kustomisasi pesan validasi pada field `file_monitoring_sp2d` agar error menjadi lebih jelas ("Input 1 wajib diisi") dibanding string standar sistem.
    - Menambahkan tampilan `description` dan `tooltip` pada status *failed* di tabel Riwayat Import agar detail log error (contoh: "Header salah") dapat dibaca langsung oleh pengguna.

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
