<?php

namespace App\Imports;

use App\Models\Sp2dRekap;
use App\Models\Sp2dUpload;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class Sp2dImport implements ToCollection, WithHeadingRow
{
    protected $uploadId;
    protected $periode;

    public function __construct(int $uploadId)
    {
        $this->uploadId = $uploadId;
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        $upload = Sp2dUpload::find($this->uploadId);

        if ($rows->isEmpty()) {
            if ($upload) {
                $upload->error_log .= "[DEBUG] Format Excel kosong atau tidak terbaca.\n";
                $upload->save();
            }
            return;
        }

        if ($upload) {
            $upload->error_log .= "[DEBUG] Baris pertama yg terbaca: " . json_encode(array_keys($rows->first()->toArray())) . "\n";
            $upload->save();
        }

        // Auto-detect periode
        $this->periode = $this->detectPeriode($rows);

        // Update upload record
        $upload = Sp2dUpload::find($this->uploadId);
        if ($upload) {
            $upload->update([
                'periode' => $this->periode,
                'total_rows' => $rows->count(),
                'status' => 'done',
            ]);
        }

        $skipped = 0;
        $inserted = 0;

        foreach ($rows as $row) {
            $no_spp = $row['no_sppspm'] ?? null;
            if (!$no_spp) {
                $skipped++;
                continue;
            }

            $inserted++;

            Sp2dRekap::updateOrCreate(
                [
                    'no_spp' => $no_spp,
                    'periode' => $this->periode,
                ],
                [
                    'upload_id' => $this->uploadId,
                    'uraian_spp' => $row['uraian_sppspm'] ?? '',
                    'jenis_spp' => $row['jenis_sppspm'] ?? '',
                    'tanggal_spp' => $this->parseDate($row['tanggal_spp'] ?? null),
                    'jumlah_pengeluaran' => $this->parseRupiah($row['jumlah_pengeluaran'] ?? 0),
                    'jumlah_potongan' => $this->parseRupiah($row['jumlah_potongan'] ?? 0),
                    'jumlah_pembayaran' => $this->parseRupiah($row['jumlah_pembayaran'] ?? 0),
                    'tanggal_sp2d' => $this->parseDate($row['tanggal_sp2d'] ?? null),
                    'no_sp2d' => $row['no_sp2d'] ?? null,
                    'status_sp2d' => $row['status_sp2d'] ?? null,
                    'kppn' => $row['kppn'] ?? null,
                    'nama_satker' => $row['nama_satker'] ?? null,
                ]
            );
        }

        if ($upload) {
            $upload->error_log .= "[DEBUG] Berhasil diproses: $inserted baris. Dilewati (No SPP kosong): $skipped baris.\n";
            $upload->save();
        }
    }

    private function detectPeriode(Collection $rows): string
    {
        $periods = $rows
            ->filter(fn($r) => !empty($r['tanggal_spp']))
            ->map(function ($r) {
                try {
                    return $this->parseDate($r['tanggal_spp'])->format('Y-m');
                } catch (\Exception $e) {
                    return null;
                }
            })
            ->filter()
            ->countBy()
            ->sortDesc();

        return $periods->keys()->first() ?? Carbon::now()->format('Y-m');
    }

    private function parseDate($value)
    {
        if (!$value || $value === '-')
            return null;

        try {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
            }
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseRupiah($val): int
    {
        if (is_numeric($val))
            return (int) $val;
        if (!$val || $val === '-')
            return 0;

        // Remove all non-numeric characters except maybe minus sign for debt/correction?
        // But the Excel shows 1.998.000 (dots as thousand separators)
        return (int) preg_replace('/[^0-9-]/', '', $val);
    }
}
