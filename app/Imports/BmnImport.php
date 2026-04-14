<?php

namespace App\Imports;

use App\Models\Bmn;
use App\Models\Pegawai;
use App\Models\Ruangan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class BmnImport implements ToCollection, WithHeadingRow, WithChunkReading, SkipsEmptyRows
{
    public int $imported = 0;
    public int $updated  = 0;
    public int $skipped  = 0;

    protected array $ruanganCache = [];
    protected array $pegawaiCache = [];

    public function __construct()
    {
        // Pre-load lookups
        Ruangan::all()->each(function ($r) {
            // key: "1001 - RUANG PELAYANAN PUBLIK" style & kode only
            $this->ruanganCache[strtoupper($r->kode_ruang . ' - ' . $r->nama_ruang)] = $r->id;
            $this->ruanganCache[strtoupper($r->kode_ruang)]                           = $r->id;
        });

        Pegawai::all()->each(function ($p) {
            $this->pegawaiCache[strtoupper(trim($p->nama))] = $p->id;
        });
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            try {
                $this->processRow($row->toArray());
            } catch (\Throwable $e) {
                $this->skipped++;
            }
        }
    }

    protected function processRow(array $row): void
    {
        // Skip header / empty / subtotal rows
        if (empty($row['kode_barang']) && empty($row['nama_barang'])) {
            $this->skipped++;
            return;
        }

        $kodeRegister = $this->val($row, 'kode_register');
        if (empty($kodeRegister)) {
            $this->skipped++;
            return;
        }

        $ruanganId = $this->resolveRuangan($this->val($row, 'lokasi_ruang'));
        [$pjType, $pjId] = $this->resolvePj($this->val($row, 'penghuni'));

        $kondisi = $this->mapKondisi($this->val($row, 'kondisi'));
        $intraExtra = $this->mapIntraExtra($this->val($row, 'intra_extra'));

        $data = [
            'kode_barang'          => $this->val($row, 'kode_barang'),
            'nup'                  => (int) ($this->val($row, 'nup') ?? 0),
            'nama_barang'          => $this->val($row, 'nama_barang'),
            'jenis_bmn'            => $this->val($row, 'jenis_bmn'),
            'merk'                 => $this->nullVal($row, 'merk'),
            'tipe'                 => $this->nullVal($row, 'tipe'),
            'kondisi'              => $kondisi,
            'umur_aset'            => (int) ($this->val($row, 'umur_aset') ?? 0),
            'henti_guna'           => $this->mapBool($this->val($row, 'henti_guna')),
            'nilai_perolehan'      => $this->mapDecimal($this->val($row, 'nilai_perolehan_pertama')),
            'nilai_penyusutan'     => $this->mapDecimal($this->val($row, 'nilai_penyusutan')),
            'nilai_buku'           => $this->mapDecimal($this->val($row, 'nilai_buku')),
            'tanggal_perolehan'    => $this->mapDate($this->val($row, 'tanggal_perolehan')),
            'no_polisi'            => $this->nullVal($row, 'no_polisi'),
            'no_dokumen'           => $this->nullVal($row, 'no_dokumen'),
            'status_penggunaan'    => $this->nullVal($row, 'status_penggunaan'),
            'intra_extra'          => $intraExtra,
            'usul_hapus'           => $this->mapBool($this->val($row, 'usul_hapus')),
            'alamat'               => $this->nullVal($row, 'alamat'),
            'kode_register'        => $kodeRegister,
            'ruangan_id'           => $ruanganId,
            'penanggung_jawab_type' => $pjType,
            'penanggung_jawab_id'  => $pjId,
        ];

        $existing = Bmn::where('kode_register', $kodeRegister)->first();

        if ($existing) {
            $existing->update($data);
            $this->updated++;
        } else {
            Bmn::create($data);
            $this->imported++;
        }
    }

    protected function resolveRuangan(?string $lokasi): ?int
    {
        if (empty($lokasi) || strtolower($lokasi) === 'belum berlokasi') {
            return null;
        }

        $key = strtoupper(trim($lokasi));
        if (isset($this->ruanganCache[$key])) {
            return $this->ruanganCache[$key];
        }

        // Try to extract kode from "1001 - RUANG ..." format
        if (preg_match('/^(\d+)\s*-/', $lokasi, $m)) {
            $kode = strtoupper($m[1]);
            if (isset($this->ruanganCache[$kode])) {
                return $this->ruanganCache[$kode];
            }
        }

        return null;
    }

    protected function resolvePj(?string $penghuni): array
    {
        if (empty($penghuni) || $penghuni === '-') {
            return [null, null];
        }

        $key = strtoupper(trim($penghuni));
        if (isset($this->pegawaiCache[$key])) {
            return [Pegawai::class, $this->pegawaiCache[$key]];
        }

        return [null, null];
    }

    protected function mapKondisi(?string $val): string
    {
        return match (true) {
            str_contains((string) $val, 'Ringan') => 'Rusak Ringan',
            str_contains((string) $val, 'Berat')  => 'Rusak Berat',
            default                                => 'Baik',
        };
    }

    protected function mapIntraExtra(?string $val): string
    {
        return (strtolower((string) $val) === 'ekstra' || strtolower((string) $val) === 'extra')
            ? 'Ekstra'
            : 'Intra';
    }

    protected function mapBool(?string $val): bool
    {
        return strtolower((string) $val) === 'ya';
    }

    protected function mapDecimal(mixed $val): float
    {
        if ($val === null || $val === '-' || $val === '') {
            return 0;
        }
        return (float) str_replace([',', ' '], ['.',  ''], (string) $val);
    }

    protected function mapDate(mixed $val): ?string
    {
        if (empty($val) || $val === '-') {
            return null;
        }

        // Already a date string
        if (is_string($val) && preg_match('/^\d{4}-\d{2}-\d{2}/', $val)) {
            return substr($val, 0, 10);
        }

        // Excel serial number
        if (is_numeric($val)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $val)
                    ->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function val(array $row, string $key): ?string
    {
        $v = $row[$key] ?? null;
        if ($v === null || trim((string) $v) === '') {
            return null;
        }
        return trim((string) $v);
    }

    protected function nullVal(array $row, string $key): ?string
    {
        $v = $this->val($row, $key);
        return ($v === '-' || $v === '') ? null : $v;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    public function headingRow(): int
    {
        return 1;
    }
}
