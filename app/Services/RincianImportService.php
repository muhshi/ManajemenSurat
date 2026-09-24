<?php

namespace App\Services;

use OpenSpout\Reader\XLSX\Reader;
use Illuminate\Support\Str;
use App\Models\Sp2dPajak;
use App\Models\User;

class RincianImportService
{
    public static function parseExcelData(string $filePath, string $jenisFile): array
    {
        $reader = new Reader();
        $reader->open($filePath);
        
        $results = [];
        $headerFound = false;
        foreach ($reader->getSheetIterator() as $sheet) {
            $header = [];
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $cells = $row->toArray();
                
                if (empty($header)) {
                    $tempHeader = array_map(fn($c) => trim(strtolower((string)$c)), $cells);
                    
                    if ($jenisFile === 'gaji' && in_array('nmpeg', $tempHeader, true) && (in_array('potpfk10', $tempHeader, true) || in_array('iwp', $tempHeader, true))) {
                        $header = $tempHeader;
                        $headerFound = true;
                    } elseif ($jenisFile === 'tukin' && (in_array('nama_pegawai', $tempHeader, true) || in_array('nmpeg', $tempHeader, true) || in_array('nama', $tempHeader, true)) && in_array('pajak', $tempHeader, true)) {
                        $header = $tempHeader;
                        $headerFound = true;
                    } elseif ($jenisFile === 'uang_makan' && (in_array('nmpeg', $tempHeader, true) || in_array('nama_pegawai', $tempHeader, true)) && in_array('potongan', $tempHeader, true)) {
                        $header = $tempHeader;
                        $headerFound = true;
                    } elseif ($jenisFile === 'uang_lembur' && in_array('pajak', $tempHeader, true) && (in_array('nmpeg', $tempHeader, true) || in_array('nmrek', $tempHeader, true))) {
                        $header = $tempHeader;
                        $headerFound = true;
                    }

                    if ($rowIndex > 50 && !$headerFound) {
                        throw new \Exception("Header file tidak dikenali untuk jenis: {$jenisFile}");
                    }
                    continue;
                }
                
                $data = [];
                foreach ($header as $index => $key) {
                    if ($key) {
                        $data[$key] = $cells[$index] ?? null;
                    }
                }
                
                self::extractTaxes($jenisFile, $data, $results);
            }
            break; // only parse first sheet
        }
        $reader->close();
        
        return $results;
    }
    
    private static function extractTaxes(string $jenisFile, array $data, array &$results): void
    {
        $parseAmount = function($value) {
            if (empty($value)) return 0;
            if (is_numeric($value)) return (int) round((float) $value);
            $valStr = (string)$value;
            if (preg_match('/[\.,]\d{1,2}$/', $valStr, $matches, PREG_OFFSET_CAPTURE)) {
                $valStr = substr($valStr, 0, $matches[0][1]);
            }
            return (int) preg_replace('/[^\d]/', '', $valStr);
        };
        
        $cleanDigits = fn($val) => preg_replace('/[^0-9]/', '', (string)($val ?? ''));

        $npwp = $cleanDigits($data['npwp'] ?? '');
        $nip = $cleanDigits($data['nip'] ?? $data['nip_baru'] ?? '');
        $nik = $cleanDigits($data['nik'] ?? $data['noktp'] ?? $data['no_ktp'] ?? '');
        $rawIdentifier = $npwp ?: ($nip ?: $nik);

        static $resolvedCache = [];
        $resolveIdentifier = function (string $nama, string $identifier) use (&$resolvedCache): string {
            if (!empty($identifier)) {
                return $identifier;
            }
            $cleanNama = trim($nama);
            if (!$cleanNama) return '';

            if (isset($resolvedCache[$cleanNama])) {
                return $resolvedCache[$cleanNama];
            }

            // Cek riwayat Sp2dPajak
            $found = Sp2dPajak::whereRaw('TRIM(nama_pihak) = ?', [$cleanNama])
                ->whereNotNull('npwp_nik')
                ->where('npwp_nik', '!=', '')
                ->value('npwp_nik');

            if (!$found) {
                // Cek tabel User
                $user = User::whereRaw('TRIM(name) = ?', [$cleanNama])->first();
                if ($user) {
                    $found = (string)($user->nip_baru ?: ($user->nip ?? ''));
                }
            }

            $resolvedCache[$cleanNama] = $found ?: '';
            return $resolvedCache[$cleanNama];
        };

        if ($jenisFile === 'gaji') {
            $nama = (string)($data['nmpeg'] ?? '');
            if (!$nama) return;
            $identifier = $resolveIdentifier($nama, $rawIdentifier);
            
            $taxes = [
                '811311' => $parseAmount($data['potpfkbul'] ?? 0),
                '811211' => $parseAmount($data['potpfk2'] ?? 0),
                '811111' => $parseAmount($data['potpfk10'] ?? $data['iwp'] ?? 0),
                '811135' => $parseAmount($data['bpjs'] ?? 0),
                '411121' => $parseAmount($data['potpph'] ?? $data['pph'] ?? 0),
                '425151' => $parseAmount($data['potswrum'] ?? $data['sewarmh'] ?? 0),
            ];
            
            foreach ($taxes as $kode => $nominal) {
                if ($nominal > 0) {
                    $results[Str::uuid()->toString()] = [
                        'npwp_nik' => $identifier,
                        'nama_pihak' => $nama,
                        'kode_akun_pajak' => $kode,
                        'dpp' => 0,
                        'nominal_pajak' => $nominal,
                    ];
                }
            }
        } elseif ($jenisFile === 'tukin') {
            $nama = (string)($data['nama_pegawai'] ?? $data['nama'] ?? $data['nmpeg'] ?? '');
            if (!$nama) return;
            $identifier = $resolveIdentifier($nama, $rawIdentifier);
            
            $nominal = $parseAmount($data['pajak'] ?? 0);
            if ($nominal > 0) {
                $results[Str::uuid()->toString()] = [
                    'npwp_nik' => $identifier,
                    'nama_pihak' => $nama,
                    'kode_akun_pajak' => '411121', // PPh 21
                    'dpp' => 0,
                    'nominal_pajak' => $nominal,
                ];
            }
        } elseif ($jenisFile === 'uang_makan') {
            $nama = (string)($data['nmpeg'] ?? $data['nama_pegawai'] ?? $data['nama'] ?? '');
            if (!$nama) return;
            $identifier = $resolveIdentifier($nama, $rawIdentifier);
            
            $nominal = $parseAmount($data['potongan'] ?? 0);
            if ($nominal > 0) {
                $results[Str::uuid()->toString()] = [
                    'npwp_nik' => $identifier,
                    'nama_pihak' => $nama,
                    'kode_akun_pajak' => '411121', // PPh 21
                    'dpp' => 0,
                    'nominal_pajak' => $nominal,
                ];
            }
        } elseif ($jenisFile === 'uang_lembur') {
            // Nama bisa di kolom nmpeg (nama pegawai) atau nmrek (nama rekening)
            $nama = trim((string)($data['nmpeg'] ?? $data['nmrek'] ?? $data['nama_pegawai'] ?? ''));
            if (!$nama) return;
            $identifier = $resolveIdentifier($nama, $rawIdentifier);

            $nominal = $parseAmount($data['pajak'] ?? 0);
            if ($nominal > 0) {
                $results[Str::uuid()->toString()] = [
                    'npwp_nik' => $identifier,
                    'nama_pihak' => $nama,
                    'kode_akun_pajak' => '411121', // PPh Pasal 21
                    'dpp' => 0,
                    'nominal_pajak' => $nominal,
                ];
            }
        }
    }
}
