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

        $spmList = [];
        $totalTerproses = 0;

        if ($this->upload->file_monitoring_sp2d) {
            $fileSp2d = Storage::disk('public')->path($this->upload->file_monitoring_sp2d);
            if (!file_exists($fileSp2d)) {
                throw new Exception("File Monitoring SP2D tidak ditemukan: {$this->upload->file_monitoring_sp2d}");
            }

            $sp2dReader = new Reader();
            $sp2dReader->open($fileSp2d);
        
        $periodeBulan = str_pad($this->upload->periode_bulan, 2, '0', STR_PAD_LEFT);
        $periodeTahun = $this->upload->periode_tahun;

        $this->log("Memuat referensi Kode SPM dari database...");
        $kodeSpmMapping = \App\Models\KodeSpm::pluck('jalur', 'kode')->toArray();

        $this->log("Membaca File Monitoring SP2D...");
        
        // Baca Sheet pertama
        $headerFound = false;
        foreach ($sp2dReader->getSheetIterator() as $sheet) {
            $header = [];
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->toArray();
                
                if (empty($header)) {
                    $tempHeader = array_map(fn($c) => trim(strtolower((string)$c)), $cells);
                    // Jika baris ini mengandung kolom wajib, jadikan header
                    if (in_array('no. sp2d', $tempHeader, true) || in_array('tanggal sp2d', $tempHeader, true) || in_array('no. spp/spm', $tempHeader, true)) {
                        $header = $tempHeader;
                        $headerFound = true;
                    }
                    if ($rowIndex > 30 && !$headerFound) {
                        throw new Exception("File salah! Kolom 'No. SP2D' / 'Tanggal SP2D' tidak ditemukan. Pastikan Anda mengupload File Monitoring SPP/SPM/SP2D di input pertama, bukan file Potongan SPM.");
                    }
                    continue;
                }

                $data = $this->mapRow($header, $cells);
                if (empty($data['no. spp/spm']) && empty($data['no. sp2d'])) continue;

                $tglSp2d = $this->parseDate($data['tanggal sp2d'] ?? null);
                if (!$tglSp2d) continue;

                // Cek filter periode tahun (wajib sama dengan pilihan form)
                if ((string)$tglSp2d->format('Y') !== (string)$this->upload->periode_tahun) {
                    continue;
                }

                // Cek filter periode bulan (jika ada)
                if (!empty($this->upload->periode_bulan)) {
                    $periodeBulan = str_pad($this->upload->periode_bulan, 2, '0', STR_PAD_LEFT);
                    if ($tglSp2d->format('m') !== $periodeBulan) {
                        continue;
                    }
                }

                $jumlahPotongan = $this->parseAmount($data['jumlah potongan'] ?? 0);
                $jenisSpm = (string)($data['jenis spp/spm'] ?? '');
                
                $isGup = str_contains($jenisSpm, '312') || str_contains($jenisSpm, '317') || str_contains(strtolower($jenisSpm), 'gup');

                $jalur = $this->tentukanJalur($jenisSpm, $kodeSpmMapping);
                
                $noSp2d = (string)($data['no. sp2d'] ?? '');
                
                $existingRekap = Sp2dRekap::where('no_sp2d', $noSp2d)->first();

                if ($existingRekap && $existingRekap->upload_id != $this->upload->id) {
                    $this->log("Skip: SP2D {$noSp2d} sudah pernah diunggah sebelumnya.");
                    continue;
                }

                $rekap = Sp2dRekap::updateOrCreate(
                    [
                        'upload_id' => $this->upload->id,
                        'no_sp2d'   => $noSp2d,
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
                        'status_verifikasi'  => ($jalur === 'gup') ? 'perlu_rincian' : (($jalur === '1_pihak' || $jumlahPotongan == 0) ? 'valid' : 'perlu_rincian'),
                    ]
                );

                $spmList[$rekap->no_spm] = $rekap;
                $spmList[$rekap->no_sp2d] = $rekap; // bisa lookup by sp2d or spm
                $totalTerproses++;
            }
            break; // hanya sheet pertama
        }
        $sp2dReader->close();
        
        $this->log("Selesai membaca File 1. Total SP2D relevan terproses: {$totalTerproses}");
        } else {
            $this->log("File Monitoring SP2D tidak dilampirkan, melewati proses 1.");
        }

        if ($this->upload->file_potongan_spm) {
            $filePotongan = Storage::disk('public')->path($this->upload->file_potongan_spm);
            if (file_exists($filePotongan)) {
                $this->log("Membaca File Monitoring Potongan SPM...");
                
                $potonganReader = new Reader();
                $potonganReader->open($filePotongan);

                $headerFoundPotongan = false;
                foreach ($potonganReader->getSheetIterator() as $sheet) {
                    $header = [];
                    foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                        $cells = $row->toArray();
                        if (empty($header)) {
                            $tempHeader = array_map(fn($c) => trim(strtolower((string)$c)), $cells);
                            if (in_array('no.sp2d/ntpn', $tempHeader, true) || in_array('no.spm', $tempHeader, true) || in_array('akun', $tempHeader, true)) {
                                $header = $tempHeader;
                                $headerFoundPotongan = true;
                            }
                            if ($rowIndex > 30 && !$headerFoundPotongan) {
                                throw new Exception("File salah! Kolom 'No.SP2D/NTPN' atau 'Akun' tidak ditemukan. Pastikan Anda mengupload File Potongan SPM di input kedua, bukan sebaliknya.");
                            }
                            continue;
                        }

                        $data = $this->mapRow($header, $cells);
                        $noSpm = (string)($data['no.spm'] ?? '');
                        $noSp2d = (string)($data['no.sp2d/ntpn'] ?? '');
                        
                        $rekap = $spmList[$noSp2d] ?? $spmList[$noSpm] ?? null;

                        if (!$rekap && ($noSp2d || $noSpm)) {
                            $query = \App\Models\Sp2dRekap::query();
                            if ($noSp2d) {
                                $query->where('no_sp2d', $noSp2d);
                            } else {
                                $query->where('no_spm', $noSpm);
                            }
                            $rekap = $query->first();
                        }

                        if ($rekap) {
                            $atasNama = trim((string)($data['atas nama'] ?? ''));
                            
                            // Deteksi: Apakah Atas Nama = BPS Demak?
                            $isBps = str_contains(strtoupper($atasNama), 'BADAN PUSAT STATISTIK KAB. DEMAK') || str_contains(strtoupper($atasNama), '018871-');

                            // Jika bukan BPS (Pihak Ketiga) dan bukan GUP, otomatis paksa jadi 1 Pihak!
                            if (!$isBps && $rekap->jalur_transaksi !== 'gup') {
                                $rekap->update([
                                    'jalur_transaksi' => '1_pihak',
                                    'status_verifikasi' => 'valid'
                                ]);
                            }

                            if ($rekap->jalur_transaksi === '1_pihak') {
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

    protected function tentukanJalur(string $jenisSpm, array &$kodeSpmMapping): string
    {
        $jenisSpmAsli = trim($jenisSpm);
        $jenisSpm = strtolower($jenisSpm);
        
        // Ekstrak 3 digit angka pertama dari jenisSpm
        preg_match('/^(\d{3})/', trim($jenisSpm), $matches);
        $kode = $matches[1] ?? null;

        // Cek secara eksplisit jika mengandung gup, maka otomatis jadi 'gup' (baik kode baru maupun fallback)
        $isGup = str_contains($jenisSpm, 'gup');

        if ($kode) {
            if (isset($kodeSpmMapping[$kode])) {
                return $kodeSpmMapping[$kode];
            } else {
                // Ekstrak nama (setelah " - ")
                $parts = explode('-', $jenisSpmAsli, 2);
                $nama = isset($parts[1]) ? trim($parts[1]) : $jenisSpmAsli;

                $jalurOtomatis = $isGup ? 'gup' : 'banyak_pihak';

                // Insert ke database otomatis
                \App\Models\KodeSpm::firstOrCreate(
                    ['kode' => $kode],
                    [
                        'nama' => $nama, 
                        'jalur' => $jalurOtomatis
                    ]
                );
                
                // Tambahkan ke mapping memori agar import baris selanjutnya lebih cepat
                $kodeSpmMapping[$kode] = $jalurOtomatis;
                
                return $jalurOtomatis;
            }
        }

        // Fallback default (sesuai persetujuan pengguna)
        return $isGup ? 'gup' : 'banyak_pihak';
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
        
        if (is_int($value) || is_float($value)) {
            return (int) round((float) $value);
        }
        
        $valueStr = (string) $value;
        
        // Hapus ".00" atau ",00" di belakang jika ada
        if (preg_match('/[\.,]\d{1,2}$/', $valueStr, $matches, PREG_OFFSET_CAPTURE)) {
            $valueStr = substr($valueStr, 0, $matches[0][1]);
        }
        
        $cleaned = preg_replace('/[^\d]/', '', $valueStr);
        return (int)$cleaned;
    }
}
