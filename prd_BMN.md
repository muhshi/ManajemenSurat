# PRD: Fitur Manajemen BMN (Barang Milik Negara)
**Stack:** Laravel 11 + Filament 3  
**Konteks:** Modul manajemen aset internal berbasis data SIMAN (Sistem Informasi Manajemen Aset Negara)

---

## 1. Ringkasan Fitur

Fitur ini memungkinkan pengelolaan BMN secara internal: mencatat daftar aset, memetakan lokasi ruangan, menetapkan penanggung jawab (pegawai atau tim/ruangan), dan melacak status serta kondisi tiap barang.

---

## 2. Entitas & Database

### 2.1 `ruangans`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode_ruang | string | Contoh: `1001`, `2001` |
| nama_tipe_ruang | string | Contoh: `Ruang Kerja`, `Ruang Toilet/WC` |
| nama_ruang | string | Contoh: `RUANG KEPALA` |
| lantai | tinyint | Nomor lantai |
| luas_ruang | decimal | m² |
| gedung | string nullable | Nama gedung jika ada lebih dari satu |
| timestamps | | |

### 2.2 `pegawais`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| nama | string | |
| nip | string nullable | |
| jabatan | string nullable | |
| no_hp | string nullable | |
| aktif | boolean | default true |
| timestamps | | |

### 2.3 `bmns`
> Sesuai kolom data SIMAN dari file `daftar-aset-1.xlsx`

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| kode_barang | string | Kode barang dari SIMAN |
| nup | integer | Nomor Urut Pendaftaran |
| nama_barang | string | |
| jenis_bmn | string | TANAH / ALAT BESAR / ALAT ANGKUTAN / dll |
| merk | string nullable | |
| tipe | string nullable | |
| kondisi | enum | `Baik`, `Rusak Ringan`, `Rusak Berat` |
| umur_aset | integer | Tahun |
| henti_guna | boolean | |
| nilai_perolehan | decimal | |
| nilai_buku | decimal | |
| nilai_penyusutan | decimal | |
| tanggal_perolehan | date | |
| no_polisi | string nullable | Untuk kendaraan |
| no_dokumen | string nullable | |
| status_penggunaan | string nullable | |
| intra_extra | enum | `Intra`, `Extra` |
| usul_hapus | boolean | |
| alamat | string nullable | |
| kode_register | string nullable | UUID dari SIMAN |
| ruangan_id | bigint FK nullable | Lokasi ruangan barang |
| penanggung_jawab_type | string nullable | Polymorphic: `pegawai` atau `ruangan` |
| penanggung_jawab_id | bigint nullable | Polymorphic FK |
| catatan | text nullable | |
| foto | json nullable | Array path foto |
| timestamps | | |

**Catatan polymorphic:** Kolom `penanggung_jawab_type` + `penanggung_jawab_id` menggunakan `morphTo()` agar satu BMN bisa ditanggung oleh pegawai atau ruangan (tim).

---

## 3. Relasi Antar Model

```
Ruangan  ──< BMN (lokasi barang)
Ruangan  ──< BMN (sebagai penanggung jawab, via morphTo)
Pegawai  ──< BMN (sebagai penanggung jawab, via morphTo)
```

---

## 4. Filament Resources

### 4.1 `RuanganResource`
**List columns:**
- Kode Ruang
- Nama Tipe Ruang
- Nama Ruang
- Lantai
- Luas Ruang (m²)
- Jumlah BMN (count relation)

**Form fields:**
- Kode Ruang (TextInput)
- Nama Tipe Ruang (Select: Ruang Kerja / Ruang Pelayanan / Ruang Istirahat / Ruang Toilet WC / Ruang Gudang / dll)
- Nama Ruang (TextInput)
- Lantai (Select: 1, 2, 3...)
- Luas Ruang (TextInput decimal)
- Gedung (TextInput nullable)

**Relation Manager:**
- `BmnsRelationManager` — daftar BMN yang berlokasi di ruangan ini

---

### 4.2 `PegawaiResource`
**List columns:**
- Nama
- NIP
- Jabatan
- Jumlah BMN yang ditanggung
- Status Aktif (badge)

**Form fields:**
- Nama (TextInput)
- NIP (TextInput)
- Jabatan (TextInput)
- No HP (TextInput)
- Aktif (Toggle)

**Relation Manager:**
- `BmnsRelationManager` — daftar BMN yang menjadi tanggung jawab pegawai ini

---

### 4.3 `BmnResource`
**List columns:**
- Kode Barang + NUP
- Nama Barang
- Jenis BMN (badge warna per kategori)
- Kondisi (badge: Baik=green, Rusak Ringan=yellow, Rusak Berat=red)
- Ruangan (nama ruang)
- Penanggung Jawab (nama pegawai atau nama ruangan)
- Nilai Buku (formatted Rupiah)
- Henti Guna (icon)

**Filter sidebar:**
- Jenis BMN
- Kondisi
- Ruangan
- Henti Guna
- Usul Hapus

**Form fields (tabs):**

*Tab: Identitas Barang*
- Kode Barang (TextInput)
- NUP (TextInput numeric)
- Nama Barang (TextInput)
- Jenis BMN (Select)
- Merk (TextInput)
- Tipe (TextInput)
- Kondisi (Select)
- Umur Aset (TextInput)
- No Polisi (TextInput, visible jika jenis = Alat Angkutan)
- No Dokumen (TextInput)
- Kode Register (TextInput, readonly, dari SIMAN)

*Tab: Lokasi & Penanggung Jawab*
- Ruangan (Select → `ruangans`)
- Tipe Penanggung Jawab (Radio: `Pegawai` / `Tim/Ruangan`)
- Penanggung Jawab Pegawai (Select → `pegawais`, visible jika tipe = Pegawai)
- Penanggung Jawab Ruangan (Select → `ruangans`, visible jika tipe = Tim/Ruangan)

*Tab: Nilai Aset*
- Nilai Perolehan (TextInput decimal, formatted)
- Nilai Penyusutan (TextInput decimal)
- Nilai Buku (TextInput decimal, readonly, auto-hitung)
- Tanggal Perolehan (DatePicker)
- Status Penggunaan (Select)
- Intra/Extra (Select)

*Tab: Status & Flags*
- Henti Guna (Toggle)
- Usul Hapus (Toggle)
- Catatan (Textarea)
- Foto (FileUpload multiple)

**Actions:**
- Edit
- Delete (dengan konfirmasi)
- `ImportAction` (custom) — import dari file Excel SIMAN
- Export ke Excel

---

## 5. Import dari Excel SIMAN

### Trigger
Tombol **"Import dari SIMAN"** di halaman list `BmnResource`.

### Proses
1. User upload file `.xlsx` (format SIMAN)
2. Gunakan `maatwebsite/excel` atau `spatie/simple-excel`
3. Mapping kolom Excel → kolom `bmns`:

| Kolom Excel | Kolom DB |
|---|---|
| Jenis BMN | jenis_bmn |
| Kode Barang | kode_barang |
| NUP | nup |
| Nama Barang | nama_barang |
| Merk | merk |
| Tipe | tipe |
| Kondisi | kondisi |
| Umur Aset | umur_aset |
| Henti Guna | henti_guna |
| No Polisi | no_polisi |
| Tanggal Perolehan | tanggal_perolehan |
| Nilai Perolehan Pertama | nilai_perolehan |
| Nilai Penyusutan | nilai_penyusutan |
| Nilai Buku | nilai_buku |
| Status Penggunaan | status_penggunaan |
| Penghuni | penanggung_jawab (resolve ke pegawai) |
| Kode Register | kode_register |

4. Kolom `Penghuni` di-resolve ke `pegawai_id` via nama (fuzzy match atau exact)
5. Baris yang sudah ada (by `kode_register`) di-skip atau di-update (pilihan user)
6. Tampilkan summary: berhasil / skip / gagal

---

## 6. Dashboard Widget (Filament Widgets)

| Widget | Tipe | Keterangan |
|---|---|---|
| Total BMN | `StatsOverviewWidget` | Total semua aset |
| Per Kondisi | `StatsOverviewWidget` | Baik / Rusak Ringan / Rusak Berat |
| BMN Henti Guna | `StatsOverviewWidget` | Yang sudah tidak digunakan |
| BMN per Jenis | `ChartWidget` (Pie/Donut) | Distribusi jenis BMN |
| BMN per Ruangan | `TableWidget` | Top ruangan by jumlah BMN |
| Nilai Total Aset | `StatsOverviewWidget` | Total nilai buku semua BMN |

---

## 7. Alur Utama (User Journey)

```
1. Setup awal
   └─ Tambah data Ruangan (dari data DBR/DBL SIMAN)
   └─ Tambah data Pegawai

2. Import BMN
   └─ Upload file Excel dari SIMAN
   └─ Review hasil import

3. Assign lokasi & penanggung jawab
   └─ Edit tiap BMN → pilih Ruangan
   └─ Edit tiap BMN → pilih PJ (Pegawai atau Ruangan/Tim)

4. Pengelolaan rutin
   └─ Update kondisi BMN
   └─ Tandai BMN henti guna
   └─ Catat usulan hapus
   └─ Upload foto kondisi fisik
```

---

## 8. Migrasi Database (Urutan)

```
1. create_ruangans_table
2. create_pegawais_table
3. create_bmns_table
```

---

## 9. Package & Dependencies

| Package | Kegunaan |
|---|---|
| `filament/filament` ^3.x | Admin panel |
| `maatwebsite/laravel-excel` | Import/export Excel |
| `spatie/laravel-medialibrary` (opsional) | Manajemen foto BMN |

---

## 10. Out of Scope (Fase 1)

- Sinkronisasi otomatis dengan API SIMAN
- Barcode/QR code per BMN
- Notifikasi jadwal penghapusan
- Approval workflow usulan hapus
- Laporan PDF resmi format SIMAN

---

## 11. Checklist Implementasi

- [ ] Migration: `ruangans`, `pegawais`, `bmns`
- [ ] Model + relasi (morphTo penanggung jawab)
- [ ] Seeder ruangan dari data DBR/DBL
- [ ] `RuanganResource` + `BmnsRelationManager`
- [ ] `PegawaiResource` + `BmnsRelationManager`
- [ ] `BmnResource` (form tabs + filter + sort)
- [ ] Import Excel action (mapping kolom SIMAN)
- [ ] Dashboard widgets
- [ ] Export Excel
