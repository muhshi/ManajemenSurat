<?php

namespace App\Services;

use App\Models\Sp2dUpload;
use App\Models\Sp2dRekap;
use App\Models\Sp2dPajak;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Common\Entity\Row;
use Exception;

class Sp2dImportService
{
    protected Sp2dUpload $upload;
    protected \Closure $logger;
    
    public function __construct(Sp2dUpload $upload, ?\Closure $logger = null)
    {
        $this->upload = $upload;
        $this->logger = $logger ?? function ($msg) { Log::info("[Sp2dImportService] " . $msg); };
    }

    protected function log(string $msg)
    {
        ($this->logger)($msg);
    }

    public function process(): void
    {
        $this->log("Memulai proses parsing file SP2D dan Potongan SPM...");

        $fileSp2d = Storage::disk('public')->path($this->upload->file_monitoring_sp2d);
        if (!file_exists($fileSp2d)) {
            throw new Exception("File Monitoring SP2D tidak ditemukan: {$this->upload->file_monitoring_sp2d}");
        }

        $sp2dReader = new Reader();
        $sp2dReader->open($fileSp2d);

        $spmList = [];
        $totalTerproses = 0;
        
        $periodeBulan = str_pad($this->upload->periode_bulan, 2, '0', STR_PAD_LEFT);
        $periodeTahun = $this->upload->periode_tahun;

        $this->log("Membaca File Monitoring SP2D...");
        
        // Baca Sheet pertama
        foreach ($sp2dReader->getSheetIterator() as $sheet) {
            $header = [];
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->toArray();
                
                if ($rowIndex === 1) {
                    $header = array_map(fn($c) => trim(strtolower((string)$c)), $cells);
                    continue;
                }

                $data = $this->mapRow($header, $cells);
                if (empty($data['no. spp/spm']) && empty($data['no. sp2d'])) continue;

                $tglSp2d = $this->parseDate($data['tanggal sp2d'] ?? null);
                if (!$tglSp2d) continue;

                // Cek filter periode
                if ($tglSp2d->format('m') !== $periodeBulan || $tglSp2d->format('Y') !== $periodeTahun) {
                    continue;
                }

                $jumlahPotongan = $this->parseAmount($data['jumlah potongan'] ?? 0);
                $jenisSpm = (string)($data['jenis spp/spm'] ?? '');
                
                $isGup = str_contains($jenisSpm, '312') || str_contains($jenisSpm, '317') || str_contains(strtolower($jenisSpm), 'gup');

                // Hanya simpan SP2D yang memiliki Jumlah Potongan > 0 ATAU Jenis SPM berunsur GUP
                if ($jumlahPotongan > 0 || $isGup) {
                    $jalur = $this->tentukanJalur($jenisSpm);
                    
                    $rekap = Sp2dRekap::updateOrCreate(
                        [
                            'upload_id' => $this->upload->id,
                            'no_sp2d'   => (string)($data['no. sp2d'] ?? ''),
                        ],
                        [
                            'tgl_sp2d'           => $tglSp2d->format('Y-m-d'),
                            'no_spm'             => (string)($data['no. spp/spm'] ?? ''),
                            'tgl_spm'            => $this->parseDate($data['tanggal spm'] ?? null)?->format('Y-m-d'),
                            'jenis_spm'          => $jenisSpm,
                            'jalur_transaksi'    => $jalur,
                            'uraian'             => (string)($data['uraian spp/spm'] ?? ''),
                            'jumlah_pengeluaran' => $this->parseAmount($data['jumlah pengeluaran'] ?? 0),
                            'jumlah_potongan'    => $jumlahPotongan,
                            'jumlah_pembayaran'  => $this->parseAmount($data['jumlah pembayaran'] ?? 0),
                            'status_verifikasi'  => $jalur === '1_pihak' ? 'valid' : 'perlu_rincian',
                        ]
                    );

                    $spmList[$rekap->no_spm] = $rekap;
                    $spmList[$rekap->no_sp2d] = $rekap; // bisa lookup by sp2d or spm
                    $totalTerproses++;
                }
            }
            break; // hanya sheet pertama
        }
        $sp2dReader->close();
        
        $this->log("Selesai membaca File 1. Total SP2D relevan terproses: {$totalTerproses}");

        if ($this->upload->file_potongan_spm) {
            $filePotongan = Storage::disk('public')->path($this->upload->file_potongan_spm);
            if (file_exists($filePotongan)) {
                $this->log("Membaca File Monitoring Potongan SPM...");
                
                $potonganReader = new Reader();
                $potonganReader->open($filePotongan);

                foreach ($potonganReader->getSheetIterator() as $sheet) {
                    $header = [];
                    foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                        $cells = $row->toArray();
                        if ($rowIndex === 1) {
                            $header = array_map(fn($c) => trim(strtolower((string)$c)), $cells);
                            continue;
                        }

                        $data = $this->mapRow($header, $cells);
                        $noSpm = (string)($data['no.spm'] ?? '');
                        $noSp2d = (string)($data['no.sp2d/ntpn'] ?? '');
                        
                        $rekap = $spmList[$noSp2d] ?? $spmList[$noSpm] ?? null;

                        if ($rekap && $rekap->jalur_transaksi === '1_pihak') {
                            $atasNama = (string)($data['atas nama'] ?? '');
                            $rekap->update(['atas_nama_default' => $atasNama]);

                            // Hanya untuk jalur 1 pihak kita otomatis buat pajaknya
                            $akun = (string)($data['akun'] ?? '');
                            $jumlahPajak = $this->parseAmount($data['jumlah'] ?? 0);
                            
                            if ($jumlahPajak > 0) {
                                // Ekstrak kode akun (6 digit pertama jika ada)
                                preg_match('/^(\d{6})/', $akun, $matches);
                                $kodeAkun = $matches[1] ?? $akun;
                                
                                Sp2dPajak::firstOrCreate([
                                    'sp2d_rekap_id' => $rekap->id,
                                    'kode_akun_pajak' => $kodeAkun,
                                    'nama_pihak' => $atasNama,
                                ], [
                                    'nama_akun_pajak' => $akun,
                                    'nominal_pajak' => $jumlahPajak,
                                    'dpp' => 0, // DPP mungkin perlu dihitung jika perlu
                                ]);
                            }
                        }
                    }
                    break;
                }
                $potonganReader->close();
            } else {
                $this->log("File Potongan SPM tidak ditemukan, diabaikan.");
            }
        }

        $this->upload->update(['total_sp2d_terproses' => $totalTerproses]);
        $this->log("Proses import selesai!");
    }

    protected function tentukanJalur(string $jenisSpm): string
    {
        $jenisSpm = strtolower($jenisSpm);
        if (str_contains($jenisSpm, '312') || str_contains($jenisSpm, '317') || str_contains($jenisSpm, 'gup')) {
            return 'gup';
        }

        // Cek jika banyak pihak
        $banyakPihakKeywords = ['211', '212', '221', '269', '237', 'gaji', 'honor', 'tukin', 'lembur', 'banyak penerima'];
        foreach ($banyakPihakKeywords as $kw) {
            if (str_contains($jenisSpm, $kw)) {
                return 'banyak_pihak';
            }
        }

        // Default 1 pihak (231, 111, dll)
        return '1_pihak';
    }

    protected function mapRow(array $header, array $cells): array
    {
        $data = [];
        foreach ($header as $index => $key) {
            if ($key) {
                $data[$key] = $cells[$index] ?? null;
            }
        }
        return $data;
    }

    protected function parseDate($value): ?\Carbon\Carbon
    {
        if (empty($value)) return null;
        if ($value instanceof \DateTimeInterface) {
            return \Carbon\Carbon::instance($value);
        }
        try {
            return \Carbon\Carbon::parse((string)$value);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function parseAmount($value): int
    {
        if (empty($value)) return 0;
        if (is_numeric($value)) return (int)$value;
        // Hapus koma/titik ribuan
        $cleaned = preg_replace('/[^\d]/', '', (string)$value);
        return (int)$cleaned;
    }
}
