# PRD — Modul Buku Persediaan (SEP-BP)
**Fitur tambahan di aplikasi Laravel 11 + FilamentPHP v3**

---

## 1. Ringkasan

Modul ini memungkinkan operator mengunggah file **PDF** laporan **Rincian Buku Persediaan** dari sistem SAKTI/SIMAK BMN, mengekstrak datanya secara otomatis menggunakan Python (`pdfplumber`), lalu menyimpannya ke database agar bisa ditampilkan, difilter, dan diekspor dalam format yang rapi. Versi awal ini akan fokus hanya pada ekstraksi data ke format **JSON** sebelum masuk ke database.

---

## 2. Data yang Direkam

### 2.1 Master Barang (`items`)

Setiap kode barang yang ditemukan di file disimpan sebagai satu record.

| Field | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `item_code` | `VARCHAR(30)` UNIQUE | `1.01.03.99.999.001189` | Format: angka dipisah titik, 6 segmen |
| `item_name` | `VARCHAR(255)` | `Kalender Dinding 2026 (1)` | Nama barang dari PDF/sumber lain |
| `satuan` | `VARCHAR(20)` | `eks`, `dus`, `BUAH`, `set` | Simpan apa adanya (case tidak dinormalisasi) |

### 2.2 Transaksi (`transactions`)

Setiap baris transaksi di tabel PDF disimpan sebagai satu record, **kecuali baris Saldo Awal**.

| Field | Tipe | Contoh | Keterangan |
|---|---|---|---|
| `item_id` | FK → `items` | — | Relasi ke master barang |
| `tanggal` | `DATE` | `2026-02-06` | Format input dari PDF: `06-02-2026` atau dari Excel: `2026-02-06` |
| `keterangan` | `VARCHAR(100)` | `Transfer Masuk Online`, `Sosial`, `Umum`, `Distribusi`, `Produksi`, `IPDS`, `Neraca` | Simpan apa adanya termasuk variasi huruf kecil/besar |
| `no_dok` | `VARCHAR(80)` | `001/1/2026`, `B-308-21/33510/PL714`, `B-9-20/33510/PL-613/2026` | Simpan apa adanya, tidak dinormalisasi |
| `masuk_unit` | `INT UNSIGNED` | `46` | Jumlah unit masuk |
| `masuk_harga` | `DECIMAL(15,2)` | `9501.00` | Harga satuan saat masuk |
| `masuk_jumlah` | `DECIMAL(15,2)` | `437046.00` | `masuk_unit × masuk_harga` |
| `keluar_unit` | `INT UNSIGNED` | `1` | Jumlah unit keluar |
| `keluar_harga` | `DECIMAL(15,2)` | `4500.00` | Harga satuan saat keluar |
| `keluar_jumlah` | `DECIMAL(15,2)` | `4500.00` | `keluar_unit × keluar_harga` |
| `saldo_unit` | `INT` | `27` | Sisa unit setelah transaksi |
| `saldo_harga` | `DECIMAL(15,2)` | `4500.00` | Harga satuan saldo |
| `saldo_jumlah` | `DECIMAL(15,2)` | `121500.00` | `saldo_unit × saldo_harga` |

> **Catatan penting:** Kolom `jumlah` (masuk/keluar/saldo) **tidak perlu dihitung ulang di aplikasi** — ambil langsung dari data yang sudah ada di file. Nilai ini sudah dihitung oleh sistem sumber.

### 2.3 Aturan Penggabungan Baris (Merge Rule)

Jika dalam satu kode barang terdapat **lebih dari satu baris dengan tanggal dan no_dok yang sama**, maka **jumlahkan `unit`-nya** dan simpan sebagai satu record.

Contoh kasus ini muncul karena FIFO — satu transaksi keluar bisa mengambil stok dari beberapa batch harga berbeda, sehingga tampil di 2 baris di PDF:

```
Baris A: keluar_unit=1, keluar_harga=4500, saldo_unit=7  (batch lama)
Baris B: keluar_unit=20, keluar_harga=4500, saldo_unit=27 (saldo batch lain)
```

Jika tanggal & no_dok sama → gabungkan: `keluar_unit = 1 + 20 = 21`

---

## 3. Struktur Visual PDF (Referensi Parsing)

PDF ini memiliki layout tabel yang dirender secara teks. Berikut contoh representasi teks yang mungkin terbaca dari PDF:

```
┌─────────────────────────────────────────────────────────────────┐
│ RINCIAN BUKU PERSEDIAAN                                         │
│ PERIODE 01-01-2026 S/D 28-02-2026                               │
│                                                                 │
│ NAMA UAKPB : BADAN PUSAT STATISTIK KAB. DEMAK                  │
│ KODE UAKPB : 054.01.0300.018871                                 │
│                                                                 │
│ METODE PENCATATAN : PERPETUAL    KODE BARANG : 1.01.03.01.003.000003 │
│ METODE PENILAIAN  : FIFO         NAMA BARANG : klip biasa       │
│                                  SATUAN      : dus              │
├────┬────────────┬─────────────┬────────────┬──────────┬─────────┤
│ No │ Tanggal    │ Keterangan  │ No Dok     │  Masuk   │ Keluar  │ Saldo Persediaan │
│    │            │             │            │Unit Harga Jumlah│Unit Harga Jumlah│Unit Harga Jumlah│
├────┼────────────┼─────────────┼────────────┼──────────┼─────────┤
│  1 │            │Saldo Awal   │            │  0   0  0│  0   0  0│  8  4500  36000│
│    │            │01-JAN-26    │            │          │         │ 20  4500  90000│ ← baris kedua FIFO
│    │    Saldo   │             │            │          │         │ 28        126000│
│  2 │ 05-01-2026 │ Umum        │ 002/1/2026 │  0   0  0│  1  4500 4500│  7  4500  31500│
│    │    Saldo   │             │            │          │         │ 27       121500│
│  3 │ 08-01-2026 │ Distribusi  │ 004/1/2026 │  0   0  0│  2  4500 9000│  5  4500  22500│
│    │    Saldo   │             │            │          │         │ 25       112500│
│    │   Jumlah   │             │            │  0       │  8      │ 20        90000│ ← total halaman
└────┴────────────┴─────────────┴────────────┴──────────┴─────────┘
08-04-2026                                                17 dari 555
```

### Pola ekstraksi `pdfplumber`:
Kami akan menggunakan library Python `pdfplumber` untuk mengekstrak tabel. Baris yang terbaca berpotensi mengikuti kolom dalam tabel, sehingga membutuhkan parsing berdasarkan indeks kolom hasil ekstraksi tabel. Apabila tabel sulit di-_parse_, fallback dilakukan dengan parsing teks baris-demi-baris (berdasarkan regex atau split string).
1. **Pencarian Kode Barang**: Teks yang memiliki awalan `KODE BARANG :` atau dari regex format `.++..+`.
2. **Pencarian Satuan**: Teks yang diawali `SATUAN :`.
3. **Pencarian Baris Transaksi**: Diawali nomor urut (contoh `1`, `2`) lalu Tanggal (contoh `05-01-2026`), Keterangan, dll.
4. **Merge Baris**: Baris tambahan yang tidak diawali nomor (seperti stok FIFO kedua) dengan kolom unit/harga pada sisi kanan. Baris unit/harga tersebut akan dikombinasikan.

---

## 4. Algoritma Parsing Python (`pdfplumber`)

### 4.1 Parser Teks / Tabel

```python
import pdfplumber
import re
import json
import sys

def parse_pdf(file_path):
    transactions = {} # Gunakan dict dengan key (tanggal, no_dok) untuk merge otomatis
    items = {}
    
    with pdfplumber.open(file_path) as pdf:
        for page in pdf.pages:
            # Gunakan logika page.extract_text() atau extract_tables()
            # ... parsing baris per baris
```

### 4.2 State Machine PDF

```
current_item_code = None
current_satuan    = None

for setiap baris hasil parse text/tabel PDF:
    jika match 'KODE BARANG :' res = re.search(r'KODE BARANG :\s*([\d\.]+)', text)
        -> set current_item_code = res.group(1)
        
    jika match 'SATUAN :' res = re.search(r'SATUAN\s*:\s*(\S+)', text)
        -> set current_satuan = res.group(1)

    jika baris cocok regex nomor (^\d+\s+\d{2}-\d{2}-\d{4}):
        -> parse `no_urut`, `tanggal`, `keterangan`, `no_dok`
        -> parse value `masuk_unit`, `keluar_unit`, `saldo_unit`, dll.
        -> Simpan atau agregasi (merge) jika (tanggal, no_dok) sama di records dari current_item_code.
```

### 4.3 Logika Merge FIFO

Penting agar logika agregasi dipusatkan pada `(tanggal, no_dok)` unik untuk setiap kode barang. Apabila menemukan rekor data pada tagihan yang sama tetapi batch berbeda (baris FIFO tanpa kolom kiri/nomor dokumen yang lengkap), skrip membaca state transaksi yang "sedang dilihat" atau men-stack baris dan ditambahkan value-unit/jumlah ke baris utamanya.

```python
# Pseudo-code Merge:
key = (tanggal, no_dok)
if key not in transactions[current_item_code]:
    transactions[current_item_code][key] = { ...data... }
else:
    # Agregasi merge unit dan jumlah
    transactions[current_item_code][key]['keluar_unit'] += parsed_keluar_unit
    transactions[current_item_code][key]['keluar_jumlah'] += parsed_keluar_jumlah
    # Perbarui data saldo untuk mencerminkan total sisa akhir
    transactions[current_item_code][key]['saldo_unit'] = parsed_saldo_unit 
```

### 4.5 Output Python ke Laravel (JSON)

Script Python output JSON ke stdout. Laravel tangkap dengan `Process::run(...)`.

```json
{
  "status": "success",
  "summary": {
    "total_items": 554,
    "total_transactions": 103
  },
  "items": [
    {
      "item_code": "1.01.03.01.003.000003",
      "satuan": "dus"
    }
  ],
  "transactions": [
    {
      "item_code": "1.01.03.01.003.000003",
      "tanggal": "2026-01-05",
      "keterangan": "Umum",
      "no_dok": "002/1/2026",
      "masuk_unit": 0,
      "masuk_harga": 0,
      "masuk_jumlah": 0,
      "keluar_unit": 1,
      "keluar_harga": 4500,
      "keluar_jumlah": 4500,
      "saldo_unit": 7,
      "saldo_harga": 4500,
      "saldo_jumlah": 31500
    }
  ],
  "errors": [
    "Row 1234: gagal parse — IndexError"
  ]
}
```

---

## 5. Skema Database

### Tabel `items`

```sql
id           BIGINT UNSIGNED PK AUTO_INCREMENT
item_code    VARCHAR(30) UNIQUE NOT NULL   -- '1.01.03.99.999.001189'
item_name    VARCHAR(255) NULL             -- dari PDF jika tersedia
satuan       VARCHAR(20) NOT NULL          -- 'eks', 'dus', 'BUAH', 'set'
created_at   TIMESTAMP
updated_at   TIMESTAMP
```

### Tabel `transactions`

```sql
id             BIGINT UNSIGNED PK AUTO_INCREMENT
item_id        BIGINT UNSIGNED FK NOT NULL
upload_id      BIGINT UNSIGNED FK NULL      -- untuk audit trail
tanggal        DATE NULL
keterangan     VARCHAR(100) NOT NULL
no_dok         VARCHAR(80) NULL
masuk_unit     INT UNSIGNED DEFAULT 0
masuk_harga    DECIMAL(15,2) DEFAULT 0
masuk_jumlah   DECIMAL(15,2) DEFAULT 0
keluar_unit    INT UNSIGNED DEFAULT 0
keluar_harga   DECIMAL(15,2) DEFAULT 0
keluar_jumlah  DECIMAL(15,2) DEFAULT 0
saldo_unit     INT DEFAULT 0
saldo_harga    DECIMAL(15,2) DEFAULT 0
saldo_jumlah   DECIMAL(15,2) DEFAULT 0
created_at     TIMESTAMP
updated_at     TIMESTAMP
```

### Tabel `inventory_uploads`

```sql
id             BIGINT UNSIGNED PK AUTO_INCREMENT
filename       VARCHAR(255)
period_start   DATE NULL
period_end     DATE NULL
status         ENUM('pending','processing','done','failed') DEFAULT 'pending'
rows_extracted INT NULL
error_log      TEXT NULL
processed_at   TIMESTAMP NULL
created_at     TIMESTAMP
updated_at     TIMESTAMP
```

---

## 6. Step-by-Step Development

### Step 1 — Migrations

```bash
php artisan make:migration create_inventory_uploads_table
php artisan make:migration create_items_table
php artisan make:migration create_transactions_table
php artisan migrate
```

### Step 2 — Models & Relasi

```bash
php artisan make:model InventoryUpload
php artisan make:model Item
php artisan make:model Transaction
```

Relasi:
- `Item` → `hasMany(Transaction::class)`
- `Transaction` → `belongsTo(Item::class)`
- `Transaction` → `belongsTo(InventoryUpload::class)`

### Step 3 — Python Parser Script

Buat: `app/Scripts/parse_buku_persediaan.py`

Input: path file Excel sebagai CLI argument
Output: JSON ke stdout (struktur seperti di Bagian 4.5)

Pemanggilan dari Laravel:
```php
$result = Process::run("python3 app/Scripts/parse_buku_persediaan.py {$tempPath}");
$data = json_decode($result->output(), true);
```

### Step 4 — Laravel Service

Buat: `app/Services/InventoryParserService.php`

Tanggung jawab:
1. Terima `UploadedFile`, simpan sementara ke `storage/app/temp/`
2. Panggil Python via `Process::run(...)`
3. Decode JSON output
4. `Item::updateOrCreate(['item_code' => ...], [...])`
5. `Transaction::insert([...])` bulk insert
6. Update status `InventoryUpload`
7. Hapus file temp

### Step 5 — Filament Resource: Upload

```bash
php artisan make:filament-resource InventoryUpload --generate
```

Form fields:
- `FileUpload` → accept `.xlsx` only, max 20MB
- Setelah submit → panggil `InventoryParserService`
- Notifikasi sukses: `"Berhasil ekstrak {X} item dan {Y} transaksi"`
- Notifikasi gagal: tampilkan pesan error dari Python

### Step 6 — Filament Resource: Transactions

```bash
php artisan make:filament-resource Transaction --generate
```

Kolom tabel:
- `tanggal`, `item.item_code`, `item.satuan`, `keterangan`, `no_dok`
- `masuk_unit`, `masuk_jumlah`
- `keluar_unit`, `keluar_jumlah`
- `saldo_unit`, `saldo_jumlah`

Filter yang disediakan:
- **SelectFilter** by `item_code` (searchable)
- **SelectFilter** by `keterangan`
- **Filter** by rentang tanggal
- **TextFilter** by `no_dok`

### Step 7 — PDF Struk (P3)

```bash
composer require barryvdh/laravel-dompdf
```

- Buat view: `resources/views/pdf/struk.blade.php`
- Tambahkan `Action` di TransactionResource → `Cetak Struk`
- Output: `response()->download()` file PDF

### Step 8 — Export Excel Format Baru (P3)

```bash
composer require maatwebsite/excel
```

- Buat: `app/Exports/TransactionsExport.php`
- Tambahkan tombol `Export` di header tabel Filament
- Header kolom output: Kode Barang | Satuan | Tanggal | Keterangan | No Dok | Masuk (Unit/Harga/Jumlah) | Keluar (Unit/Harga/Jumlah) | Saldo (Unit/Harga/Jumlah)

---

## 7. Catatan & Edge Cases

| Kasus | Penanganan |
|---|---|
| Saldo Awal tanggal = null | Field `tanggal` boleh NULL di DB |
| Satuan tidak konsisten case | Simpan apa adanya (`dus`, `BUAH`, ` eks ` → trim whitespace) |
| No dok dengan format berbeda | Simpan apa adanya, tidak dinormalisasi |
| Baris FIFO sisa merge (len < 5) | Skip jika `len(clean_row) < 5` |
| Item dengan semua transaksi = 0 | Tetap simpan ke `items`, tidak perlu simpan ke `transactions` |
| Python error di satu baris | Catat ke array `errors`, lanjutkan parsing, jangan crash |
| File duplikat di-upload ulang | `updateOrCreate` untuk items, pertimbangkan cek duplikat by `upload_id` untuk transactions |

---

## 8. Prioritas

| Prioritas | Fitur |
|---|---|
| **P1 — Wajib** | Python parser + DB storage |
| **P1 — Wajib** | Filament upload form + tabel transactions |
| **P2 — Penting** | Filter & pencarian di tabel |
| **P3 — Opsional** | Export Excel format baru |
| **P3 — Opsional** | PDF struk/invoice |
