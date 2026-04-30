# PRD: Fitur Agenda & Notulensi Rapat
> **Status**: Draft v2  
> **Tech Stack**: Laravel 12, Filament v4, PHPWord (`phpoffice/phpword`)  
> **Direvisi**: 30 April 2026

---

## 1. User Flow

```
[1] Buat Agenda Rapat
        ↓ (form: judul, tanggal, tempat, dst)
[2] Edit Agenda → Tab "Peserta"
        ↓ (RelationManager — tambah/edit/hapus peserta)
[3] List Agenda → Tombol "Isi Notulensi" per baris
        ↓ (modal form: hasil pembahasan, keputusan, tindak lanjut)
[4] Tombol "Download Dokumen"
        ↓ (generate 1 file .docx: Undangan + Daftar Hadir + Notulensi)
```

---

## 2. Entity & Database

### 2.1 Tabel: `agendas` (Model: `Agenda`)

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `nomor_surat` | string | Nomor undangan (auto-generate) |
| `judul` | string | Judul/topik rapat |
| `perihal` | string | Perihal surat undangan |
| `tempat` | string | Tempat rapat |
| `tanggal_rapat` | date | Tanggal pelaksanaan |
| `waktu_mulai` | time | Jam mulai |
| `waktu_selesai` | time\|nullable | Jam selesai |
| `pimpinan_rapat` | string | Nama pimpinan rapat |
| `isi_notulensi` | text\|nullable | Hasil pembahasan |
| `keputusan` | text\|nullable | Poin-poin keputusan |
| `tindak_lanjut` | text\|nullable | Tindak lanjut yang disepakati |
| `signer_name` | string | Snapshot nama pejabat |
| `signer_nip` | string | Snapshot NIP |
| `signer_title` | string | Snapshot jabatan |
| `signer_city` | string | Snapshot kota |
| `status` | enum | `draft`, `published` |
| `timestamps` | | created_at, updated_at |

### 2.2 Tabel: `agenda_pesertas` (Model: `AgendaPeserta`)

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigIncrements | PK |
| `agenda_id` | foreignId | FK → agendas (cascade delete) |
| `nama` | string | Nama peserta |
| `jabatan` | string | Jabatan/instansi |
| `no_hp` | string\|nullable | Nomor HP |
| `hadir` | boolean | Default `true` |
| `urutan` | integer | Default `0` (urutan tampil) |
| `timestamps` | | |

---

## 3. Struktur File

```
app/
├── Models/
│   ├── Agenda.php
│   └── AgendaPeserta.php
├── Services/
│   └── AgendaDocService.php
├── Filament/
│   └── Resources/
│       └── Agendas/
│           ├── AgendaResource.php
│           ├── Pages/
│           │   ├── ListAgendas.php
│           │   ├── CreateAgenda.php
│           │   └── EditAgenda.php
│           └── RelationManagers/
│               └── PesertaRelationManager.php
└── Settings/
    └── SystemSettings.php  (+ template_notulensi)
```

---

## 4. Filament Resource: `AgendaResource`

### 4.1 Navigation

```php
protected static string $navigationGroup = 'Notulensi Rapat';
protected static ?int    $navigationSort  = 30;
protected static ?string $navigationLabel = 'Agenda Rapat';
protected static ?string $modelLabel      = 'Agenda';
protected static ?string $pluralModelLabel = 'Agenda Rapat';
```

### 4.2 Form Schema (Create / Edit)

```
Section: "Informasi Agenda"
├── nomor_surat    (TextInput, readOnly, auto-generate dari Agenda::generateNomor())
├── judul          (TextInput, required)
├── perihal        (TextInput, required)
├── tempat         (TextInput, required)
├── tanggal_rapat  (DatePicker, required)
├── waktu_mulai    (TimePicker, required)
├── waktu_selesai  (TimePicker, nullable)
├── pimpinan_rapat (TextInput, required)
└── status         (Select: draft | published)

Section: "Penandatangan (Snapshot)" [collapsed]
├── signer_name    (readonly, default dari SystemSettings)
├── signer_nip     (readonly)
├── signer_title   (readonly, columnSpanFull)
└── signer_city    (readonly)
```

> **Catatan**: Bagian Peserta TIDAK ada di form create/edit.
> Peserta dikelola via **RelationManager** di halaman Edit.

### 4.3 Table Columns

```
- nomor_surat    (searchable, bold, copyable)
- judul          (searchable, limit 40)
- tanggal_rapat  (date: d M Y, sortable)
- waktu_mulai    (time)
- pimpinan_rapat
- status         (badge: draft=gray, published=success)
- peserta_count  (jumlah peserta, badge) — via withCount('peserta')
- notulensi_icon (icon: check-circle jika isi_notulensi terisi, dash jika kosong)
```

### 4.4 Record Actions (per baris tabel)

```
1. ViewAction
2. Action: "Isi Notulensi"
   - icon: heroicon-o-clipboard-document-list
   - color: warning (jika belum ada notulensi) / info (jika sudah ada)
   - label dinamis: "Isi Notulensi" / "Edit Notulensi"
   - form modal: isi_notulensi (Textarea), keputusan (Textarea), tindak_lanjut (Textarea)
   - action: update record langsung via $record->update([...])
3. Action: "Download Dokumen"
   - icon: heroicon-o-document-text
   - color: success
   - disabled jika template_notulensi belum diset di Settings
   - action: panggil AgendaDocService->generate($record), return download
4. EditAction
5. DeleteAction
```

---

## 5. RelationManager: `PesertaRelationManager`

Ditampilkan sebagai tab di halaman **Edit Agenda**.

```php
// Judul panel
protected static string $relationship = 'peserta';
protected static ?string $title = 'Daftar Peserta';

// Form (modal create/edit)
Schema::make([
    TextInput::make('nama')->required(),
    TextInput::make('jabatan')->required(),
    TextInput::make('no_hp')->nullable(),
    Toggle::make('hadir')->default(true),
    TextInput::make('urutan')->numeric()->default(0),
])

// Table columns
- urutan  (sortable, editable inline)
- nama    (searchable)
- jabatan
- no_hp
- hadir   (badge/toggle icon)

// Actions
- CreateAction (bisa tambah dari luar modal: "Tambah Peserta")
- EditAction per baris
- DeleteAction per baris
```

---

## 6. Service Class: `AgendaDocService`

**File**: `app/Services/AgendaDocService.php`

```php
namespace App\Services;

use App\Models\Agenda;
use App\Settings\SystemSettings;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class AgendaDocService
{
    public function generate(Agenda $agenda): string
    {
        $settings = app(SystemSettings::class);
        $templatePath = storage_path('app/public/' . $settings->template_notulensi);

        if (!$settings->template_notulensi || !file_exists($templatePath)) {
            throw new \RuntimeException('Template notulensi belum diupload di Pengaturan.');
        }

        $template = new TemplateProcessor($templatePath);
        $tanggal  = Carbon::parse($agenda->tanggal_rapat);

        // Placeholder umum
        $template->setValue('nomor_surat',    $agenda->nomor_surat);
        $template->setValue('judul',          $agenda->judul);
        $template->setValue('perihal',        $agenda->perihal);
        $template->setValue('tempat',         $agenda->tempat);
        $template->setValue('hari_tanggal',   $tanggal->translatedFormat('l, d F Y'));
        $template->setValue('tanggal_rapat',  $tanggal->translatedFormat('d F Y'));
        $template->setValue('waktu_mulai',    $agenda->waktu_mulai);
        $template->setValue('pimpinan_rapat', $agenda->pimpinan_rapat);

        // Notulensi — ganti newline dengan line break Word
        $template->setValue('isi_notulensi', $this->toWordText($agenda->isi_notulensi));
        $template->setValue('keputusan',     $this->toWordText($agenda->keputusan));
        $template->setValue('tindak_lanjut', $this->toWordText($agenda->tindak_lanjut));

        // Signer snapshot
        $template->setValue('nama_kepala',    $agenda->signer_name);
        $template->setValue('nip_kepala',     $agenda->signer_nip);
        $template->setValue('jabatan_kepala', $agenda->signer_title);
        $template->setValue('kota_penetapan', $agenda->signer_city);

        // Daftar hadir (cloneRow)
        $peserta = $agenda->peserta()->orderBy('urutan')->get();
        $template->cloneRow('nama', max($peserta->count(), 1));

        foreach ($peserta as $i => $p) {
            $row = $i + 1;
            $template->setValue("no#$row",      $row);
            $template->setValue("nama#$row",    $p->nama);
            $template->setValue("jabatan#$row", $p->jabatan);
        }

        // Simpan ke temp
        $safe       = str_replace(['/', '\\'], '_', $agenda->nomor_surat);
        $fileName   = "Agenda_Rapat_{$safe}.docx";
        $outputPath = storage_path('app/' . $fileName);
        $template->saveAs($outputPath);

        return $outputPath;
    }

    private function toWordText(?string $text): string
    {
        if (!$text) return '-';
        return str_replace(
            "\n",
            '</w:t><w:br/><w:t xml:space="preserve">',
            htmlspecialchars($text, ENT_XML1)
        );
    }
}
```

---

## 7. Template Word (.docx)

Buat **1 file template** dengan 3 halaman dipisah *Page Break*:

```
[HALAMAN 1 — UNDANGAN]
    ${nomor_surat}
    Perihal: Undangan ${judul}
    Kepada: (statis atau baris manual)
    Tempat  : ${tempat}
    Hari/Tgl: ${hari_tanggal}
    Pukul   : ${waktu_mulai} WIB
    [tanda tangan: ${nama_kepala}, ${jabatan_kepala}, NIP ${nip_kepala}]

[PAGE BREAK]

[HALAMAN 2 — DAFTAR HADIR]
    Perihal : ${perihal}
    Tanggal : ${tanggal_rapat}
    | No       | Nama Peserta | Jabatan     | Tanda Tangan |
    | ${no}    | ${nama}      | ${jabatan}  |              |

[PAGE BREAK]

[HALAMAN 3 — NOTULENSI]
    ${judul}
    Hari/Tgl: ${hari_tanggal} | Tempat: ${tempat}
    Dipimpin: ${pimpinan_rapat}

    HASIL PEMBAHASAN:
    ${isi_notulensi}

    KEPUTUSAN:
    ${keputusan}

    TINDAK LANJUT:
    ${tindak_lanjut}
```

**Daftar lengkap placeholder:**

| Placeholder | Sumber data |
|---|---|
| `${nomor_surat}` | `agenda.nomor_surat` |
| `${judul}` | `agenda.judul` |
| `${perihal}` | `agenda.perihal` |
| `${tempat}` | `agenda.tempat` |
| `${hari_tanggal}` | formatted: Senin, 30 April 2026 |
| `${tanggal_rapat}` | formatted: 30 April 2026 |
| `${waktu_mulai}` | `agenda.waktu_mulai` |
| `${pimpinan_rapat}` | `agenda.pimpinan_rapat` |
| `${isi_notulensi}` | `agenda.isi_notulensi` |
| `${keputusan}` | `agenda.keputusan` |
| `${tindak_lanjut}` | `agenda.tindak_lanjut` |
| `${nama_kepala}` | `agenda.signer_name` |
| `${nip_kepala}` | `agenda.signer_nip` |
| `${jabatan_kepala}` | `agenda.signer_title` |
| `${kota_penetapan}` | `agenda.signer_city` |
| `${no}` | nomor urut peserta (cloneRow) |
| `${nama}` | nama peserta (cloneRow) |
| `${jabatan}` | jabatan peserta (cloneRow) |

---

## 8. Settings: Tambah `template_notulensi`

**`app/Settings/SystemSettings.php`** — tambah:
```php
public ?string $template_notulensi;
```

Di halaman Settings Filament, tambah section:
```
Section: "Template Notulensi Rapat"
└── FileUpload: template_notulensi
    - acceptedFileTypes: ['application/vnd.openxmlformats-officedocument.wordprocessingml.document']
    - directory: 'templates'
    - helperText: 'Upload template .docx untuk Notulensi Rapat'
```

---

## 9. Langkah Development (Step-by-Step)

Kerjakan berurutan. Setiap step ada instruksi siap pakai untuk AI.

---

### STEP 1 — Migration: Tabel `agendas`

> **Instruksi AI:**
> "Buat Laravel migration untuk tabel `agendas` dengan kolom: id (bigIncrements), nomor_surat (string), judul (string), perihal (string), tempat (string), tanggal_rapat (date), waktu_mulai (time), waktu_selesai (time nullable), pimpinan_rapat (string), isi_notulensi (text nullable), keputusan (text nullable), tindak_lanjut (text nullable), signer_name (string), signer_nip (string), signer_title (string), signer_city (string), status (string default 'draft'), timestamps."

---

### STEP 2 — Migration: Tabel `agenda_pesertas`

> **Instruksi AI:**
> "Buat Laravel migration untuk tabel `agenda_pesertas` dengan kolom: id (bigIncrements), agenda_id (foreignId constrained ke 'agendas' onDelete cascade), nama (string), jabatan (string), no_hp (string nullable), hadir (boolean default true), urutan (integer default 0), timestamps."

---

### STEP 3 — Model: `Agenda`

> **Instruksi AI:**
> "Buat Eloquent model `Agenda` (file: `app/Models/Agenda.php`) untuk tabel `agendas`. fillable: semua kolom kecuali id dan timestamps. casts: tanggal_rapat → 'date'. Relasi: `peserta()` hasMany `AgendaPeserta`. Tambahkan static method `generateNomor(int $tahun): string` yang mencari max(nomor_urut) dari tahun tersebut, lalu menghasilkan format: `'ND-' . str_pad($nextUrut, 4, '0', STR_PAD_LEFT) . '/' . app(SystemSettings::class)->office_code . '/' . $tahun`. Lihat pola yang sudah ada di `app/Models/Surat.php`."

---

### STEP 4 — Model: `AgendaPeserta`

> **Instruksi AI:**
> "Buat Eloquent model `AgendaPeserta` (file: `app/Models/AgendaPeserta.php`) untuk tabel `agenda_pesertas`. fillable: agenda_id, nama, jabatan, no_hp, hadir, urutan. casts: hadir → 'boolean'. Relasi: `agenda()` belongsTo `Agenda`. Pastikan `protected $table = 'agenda_pesertas'`."

---

### STEP 5 — Update SystemSettings

> **Instruksi AI:**
> "Di file `app/Settings/SystemSettings.php`, tambahkan property `public ?string $template_notulensi;` setelah baris `public ?string $template_surat_pengantar;`."

---

### STEP 6 — Migration: Settings

> **Instruksi AI:**
> "Buat Laravel migration untuk menambahkan setting `template_notulensi` ke tabel settings. Lihat pola yang sama di migration `2025_12_19_095215_add_template_sk_to_settings.php`. Gunakan `DB::table('settings')->insert([...])` dengan group 'system', name 'template_notulensi', payload 'null', locked false."

---

### STEP 7 — Service Class: `AgendaDocService`

> **Instruksi AI:**
> "Buat service class di `app/Services/AgendaDocService.php` persis seperti kode yang ada di Section 6 PRD ini. Jangan ada perubahan logika."

---

### STEP 8 — RelationManager: `PesertaRelationManager`

> **Instruksi AI:**
> "Buat Filament v4 RelationManager `PesertaRelationManager` di `app/Filament/Resources/Agendas/RelationManagers/PesertaRelationManager.php`.
> - `protected static string $relationship = 'peserta';`
> - `protected static ?string $title = 'Daftar Peserta';`
> - Form fields: nama (TextInput required), jabatan (TextInput required), no_hp (TextInput nullable), hadir (Toggle default true), urutan (TextInput numeric default 0)
> - Table columns: urutan (sortable), nama (searchable), jabatan, no_hp, hadir (badge: true=Hadir/success, false=Tidak Hadir/danger)
> - Actions: CreateAction, EditAction, DeleteAction"

---

### STEP 9 — Filament Resource: `AgendaResource`

> **Instruksi AI:**
> "Buat Filament v4 Resource `AgendaResource` di `app/Filament/Resources/Agendas/AgendaResource.php`. Ikuti pola `SuratKeluarResource.php` yang sudah ada.
> 
> **Form**: 2 section:
> 1. 'Informasi Agenda': nomor_surat (readonly, default dari `Agenda::generateNomor(now()->year)`), judul, perihal, tempat, DatePicker tanggal_rapat, TimePicker waktu_mulai, TimePicker waktu_selesai (nullable), pimpinan_rapat, Select status (draft/published).
> 2. 'Penandatangan' (collapsed): signer_name, signer_nip, signer_title (columnSpanFull), signer_city — semua readonly, default dari `app(SystemSettings::class)`.
> 
> **Table columns**: nomor_surat (bold, searchable), judul (searchable, limit 40), tanggal_rapat (date d M Y, sortable), waktu_mulai, pimpinan_rapat, status (badge), peserta_count (label 'Peserta', badge — tambahkan `withCount('peserta')` di getEloquentQuery).
> 
> **Record Actions**:
> 1. `ViewAction`
> 2. `Action::make('isi_notulensi')` — label dinamis: jika `$record->isi_notulensi` kosong tampilkan 'Isi Notulensi' (color warning), jika sudah ada tampilkan 'Edit Notulensi' (color info). Buka modal dengan form: Textarea isi_notulensi (label 'Hasil Pembahasan', rows 5), Textarea keputusan (rows 4), Textarea tindak_lanjut (rows 4). Action: `$record->update($data)`.
> 3. `Action::make('download')` — label 'Download .docx', icon heroicon-o-document-text, color success. Action: panggil `app(AgendaDocService::class)->generate($record)`, lalu `return response()->download($path, basename($path))`. Jika exception: tampilkan Notification danger.
> 4. `EditAction`
> 5. `DeleteAction`
> 
> **RelationManagers**: `[PesertaRelationManager::class]`
> 
> **Navigation**: group 'Notulensi Rapat', sort 30"

---

### STEP 10 — Pages Resource

> **Instruksi AI:**
> "Buat 3 file Pages untuk AgendaResource di folder `app/Filament/Resources/Agendas/Pages/`:
> - `ListAgendas.php` — extends `ListRecords`, `$resource = AgendaResource::class`
> - `CreateAgenda.php` — extends `CreateRecord`
> - `EditAgenda.php` — extends `EditRecord`, tambahkan method `getRelationManagers()` yang return `[PesertaRelationManager::class]`
> Ikuti konvensi Filament v4 seperti file Pages yang sudah ada di project."

---

### STEP 11 — Update Halaman Settings Filament

> **Instruksi AI:**
> "Cari file halaman Settings Filament di `app/Filament/`. Tambahkan Section baru dengan judul 'Template Notulensi Rapat' berisi FileUpload untuk field `template_notulensi`. acceptedFileTypes: `['application/vnd.openxmlformats-officedocument.wordprocessingml.document']`, directory: 'templates'. Ikuti pola upload template yang sudah ada di file tersebut."

---

### STEP 12 — Buat File Template Word (MANUAL)

Kerjakan secara manual di Microsoft Word / LibreOffice:

1. Buat dokumen baru dengan 3 halaman (pisahkan dengan *Page Break*)
2. Halaman 1: Undangan (gunakan placeholder dari tabel di Section 7)
3. Halaman 2: Daftar Hadir — tabel 4 kolom, baris data pakai `${no}`, `${nama}`, `${jabatan}`
4. Halaman 3: Notulensi
5. **Simpan sebagai `.docx`** (bukan `.doc`)
6. Upload via admin panel → Pengaturan → Template Notulensi

---

### STEP 13 — Jalankan Migration & Test

```bash
php artisan migrate
php artisan optimize:clear
```

**Checklist verifikasi:**
- [ ] Menu "Agenda Rapat" muncul di sidebar
- [ ] Buat agenda baru → nomor otomatis ter-generate
- [ ] Edit agenda → tab "Daftar Peserta" muncul → bisa tambah peserta
- [ ] List agenda → kolom jumlah peserta ter-update
- [ ] Tombol "Isi Notulensi" → modal terbuka → data tersimpan
- [ ] Tombol "Download .docx" → file terunduh dengan data yang benar
- [ ] Daftar hadir di file Word = jumlah baris sesuai peserta

---

## 10. Catatan Teknis

### `cloneRow` — Cara Kerja

Template Word (baris tabel):
```
| ${no} | ${nama} | ${jabatan} | [kosong tanda tangan] |
```

Setelah `$template->cloneRow('nama', 3)`:
```
| ${no#1} | ${nama#1} | ${jabatan#1} | |
| ${no#2} | ${nama#2} | ${jabatan#2} | |
| ${no#3} | ${nama#3} | ${jabatan#3} | |
```

Lalu di-loop `setValue("nama#1", "Budi")`, dst.

### `getEloquentQuery` untuk `peserta_count`

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()->withCount('peserta');
}
```

Di table column:
```php
TextColumn::make('peserta_count')->label('Peserta')->badge(),
```

### Error Handling Download

```php
try {
    $path = app(AgendaDocService::class)->generate($record);
    return response()->download($path, basename($path));
} catch (\Exception $e) {
    Notification::make()
        ->title($e->getMessage())
        ->danger()
        ->send();
}
```
