<?php
namespace App\Services;

use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Common\Entity\Cell\FormulaCell;
use Illuminate\Support\Str;

class RincianImportService
{
    public static function parseExcelData(string $filePath, string $jenisFile): array
    {
        $reader = new Reader();
        $reader->open($filePath);
        
        $results = [];
        $headerFound = false;

        $parseAmount = function($value) {
            if (empty($value)) return 0;
            if (is_numeric($value)) return (int) round((float) $value);
            $valStr = (string)$value;
            if (preg_match('/[\.,]\d{1,2}$/', $valStr, $matches, PREG_OFFSET_CAPTURE)) {
                $valStr = substr($valStr, 0, $matches[0][1]);
            }
            return (int) preg_replace('/[^\d]/', '', $valStr);
        };

        foreach ($reader->getSheetIterator() as $sheet) {
            $header = [];
            $nominatifColMap = null;

            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                // Konversi cell dengan membaca computed value jika cell bertipe FormulaCell
                $cells = array_map(function ($cell) {
                    if ($cell instanceof FormulaCell) {
                        return $cell->getComputedValue() ?? $cell->getValue();
                    }
                    return $cell->getValue();
                }, $row->getCells());
                
                // 1. Parsing khusus untuk Daftar Nominatif Pembayaran
                if ($jenisFile === 'nominatif') {
                    if ($nominatifColMap === null) {
                        $namaCol = null;
                        $pphCol = null;
                        $nipCol = null;
                        $brutoCol = null;
                        
                        foreach ($cells as $idx => $val) {
                            $norm = strtolower(preg_replace('/\s+/', '', (string)$val));
                            if (in_array($norm, ['nama', 'namapenerima', 'namapegawai'], true) || str_contains($norm, 'namapenerima') || str_contains($norm, 'namapegawai')) {
                                $namaCol = $idx;
                            } elseif (str_contains($norm, 'pph') || $norm === 'potongan') {
                                $pphCol = $idx;
                            } elseif (str_contains($norm, 'nip') || str_contains($norm, 'npwp') || str_contains($norm, 'nik')) {
                                $nipCol = $idx;
                            } elseif (str_contains($norm, 'bruto') || str_contains($norm, 'kotor')) {
                                $brutoCol = $idx;
                            }
                        }
                        
                        if ($namaCol !== null && $pphCol !== null) {
                            if ($brutoCol === null && $pphCol > 0) {
                                $brutoCol = $pphCol - 1;
                            }
                            $nominatifColMap = [
                                'nama'  => $namaCol,
                                'pph'   => $pphCol,
                                'nip'   => $nipCol,
                                'bruto' => $brutoCol,
                            ];
                            $headerFound = true;
                        }

                        if ($rowIndex > 50 && !$headerFound) {
                            throw new \Exception("Header file tidak dikenali untuk jenis: {$jenisFile}. Pastikan tabel memiliki kolom Nama dan PPh 21.");
                        }
                        continue;
                    }

                    // Cek jika baris ini adalah baris ringkasan / total / akhir tabel
                    $isSummaryRow = false;
                    foreach ($cells as $cVal) {
                        $normCheck = strtoupper(preg_replace('/\s+/', '', (string)$cVal));
                        if (str_starts_with($normCheck, 'JUMLAH') || str_starts_with($normCheck, 'TOTAL') || str_starts_with($normCheck, 'DENGANHURUF') || str_starts_with($normCheck, 'TERBILANG') || str_starts_with($normCheck, 'SETUJUDIBAYAR')) {
                            $isSummaryRow = true;
                            break;
                        }
                    }
                    if ($isSummaryRow) {
                        break;
                    }

                    // Lewati baris nomor kolom petunjuk seperti (1), (2), (3) atau 1, 2, 3 atau subheader
                    $rawNama = trim((string)($cells[$nominatifColMap['nama']] ?? ''));
                    if ($rawNama === '' || preg_match('/^\(?\d+\)?$/', $rawNama) || in_array(strtolower($rawNama), ['per', 'jam', '(rp)'], true)) {
                        continue;
                    }

                    $rawPph = $cells[$nominatifColMap['pph']] ?? 0;
                    $rawNip = $nominatifColMap['nip'] !== null ? ($cells[$nominatifColMap['nip']] ?? '') : '';
                    $rawBruto = $nominatifColMap['bruto'] !== null ? ($cells[$nominatifColMap['bruto']] ?? 0) : 0;

                    $nominalPajak = $parseAmount($rawPph);
                    $nominalBruto = $parseAmount($rawBruto);

                    if ($nominalPajak > 0) {
                        $normalizedNama = PartyNameNormalizer::normalize($rawNama);
                        if ($normalizedNama) {
                            $cleanId = PartyNameNormalizer::cleanIdentifier($rawNip);
                            $identifier = PartyNameNormalizer::resolveIdentifier($normalizedNama, $cleanId);

                            $results[Str::uuid()->toString()] = [
                                'npwp_nik'        => $identifier,
                                'nama_pihak'      => $normalizedNama,
                                'kode_akun_pajak' => '411121', // PPh Pasal 21
                                'dpp'             => $nominalBruto,
                                'nominal_pajak'   => $nominalPajak,
                            ];
                        }
                    }
                    continue;
                }

                // 2. Parsing format Gaji, Tukin, Uang Makan, Lembur
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

        $nip = $cleanDigits($data['nip'] ?? $data['nip_baru'] ?? '');
        $npwp = $cleanDigits($data['npwp'] ?? '');
        $nik = $cleanDigits($data['nik'] ?? $data['noktp'] ?? $data['no_ktp'] ?? '');
        $rawIdentifier = $nip ?: ($npwp ?: $nik);

        $resolveParty = function(string $rawNama) use ($rawIdentifier): array {
            $normalizedNama = \App\Services\PartyNameNormalizer::normalize($rawNama);
            $identifier = \App\Services\PartyNameNormalizer::resolveIdentifier($normalizedNama, $rawIdentifier);
            return [$normalizedNama, $identifier];
        };

        if ($jenisFile === 'gaji') {
            $rawNama = (string)($data['nmpeg'] ?? '');
            if (!$rawNama) return;
            [$nama, $identifier] = $resolveParty($rawNama);
            if (!$nama) return;
            
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
            $rawNama = (string)($data['nama_pegawai'] ?? $data['nama'] ?? $data['nmpeg'] ?? '');
            if (!$rawNama) return;
            [$nama, $identifier] = $resolveParty($rawNama);
            if (!$nama) return;
            
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
            $rawNama = (string)($data['nmpeg'] ?? $data['nama_pegawai'] ?? $data['nama'] ?? '');
            if (!$rawNama) return;
            [$nama, $identifier] = $resolveParty($rawNama);
            if (!$nama) return;
            
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
            $rawNama = trim((string)($data['nmpeg'] ?? $data['nmrek'] ?? $data['nama_pegawai'] ?? ''));
            if (!$rawNama) return;
            [$nama, $identifier] = $resolveParty($rawNama);
            if (!$nama) return;

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
