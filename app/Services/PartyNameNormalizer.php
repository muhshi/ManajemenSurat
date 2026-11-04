<?php

namespace App\Services;

use App\Models\Sp2dPajak;
use App\Models\User;
use App\Models\Pegawai;

class PartyNameNormalizer
{
    /**
     * Common academic and honorific prefixes in Indonesia.
     */
    protected static array $prefixes = [
        'prof\.', 'prof',
        'dr\.', 'dr',
        'dra\.', 'dra',
        'drs\.', 'drs',
        'drg\.', 'drg',
        'drh\.', 'drh',
        'ir\.', 'ir',
        'h\.', 'hj\.',
        'k\.h\.', 'kh\.',
        'apt\.', 'apt',
        'ns\.', 'ns',
    ];

    /**
     * Common academic, professional, and vocational degree suffixes.
     */
    protected static array $suffixes = [
        // S.Tr.* (Sarjana Terapan)
        's\.tr\.stat\.?', 's\.tr\.kom\.?', 's\.tr\.t\.?', 's\.tr\.p\.?', 's\.tr\.keb\.?',
        's\.tr\.sos\.?', 's\.tr\.gz\.?', 's\.tr\.ak\.?', 's\.tr\.ab\.?', 's\.tr\.ap\.?', 's\.tr\.?',
        // Diploma / Ahli Madya
        'a\.md\.stat\.?', 'a\.md\.kom\.?', 'a\.md\.keb\.?', 'a\.md\.kep\.?', 'a\.md\.akt\.?',
        'a\.md\.ak\.?', 'a\.md\.far\.?', 'a\.md\.ro\.?', 'a\.md\.kg\.?', 'a\.md\.?', 'amd\.?',
        'a\.ma\.?', 'ama\.?',
        // SST
        's\.s\.t\.?', 'sst\.?',
        // Sarjana
        's\.stat\.?', 'sstat\.?',
        's\.kom\.?', 'skom\.?',
        's\.e\.?', 'se\.?',
        's\.si\.?', 'ssi\.?',
        's\.t\.?', 'st\.?',
        's\.h\.?', 'sh\.?',
        's\.sos\.?', 'ssos\.?',
        's\.pd\.?', 'spd\.?',
        's\.psi\.?', 'spsi\.?',
        's\.p\.?', 'sp\.?',
        's\.ked\.?', 'sked\.?',
        's\.farm\.?', 'sfarm\.?',
        's\.ip\.?', 'sip\.?',
        's\.sn\.?', 'ssn\.?',
        's\.hum\.?', 'shum\.?',
        's\.mat\.?', 'smat\.?',
        's\.ak\.?', 'sak\.?',
        's\.ab\.?', 'sab\.?',
        's\.ap\.?', 'sap\.?',
        's\.kes\.?', 'skes\.?',
        's\.kg\.?', 'skg\.?',
        's\.kh\.?', 'skh\.?',
        // Magister
        'm\.si\.?', 'msi\.?',
        'm\.stat\.?', 'mstat\.?',
        'm\.kom\.?', 'mkom\.?',
        'm\.m\.?', 'mm\.?',
        'm\.t\.?', 'mt\.?',
        'm\.h\.?', 'mh\.?',
        'm\.pd\.?', 'mpd\.?',
        'm\.p\.?', 'mp\.?',
        'm\.sc\.?', 'msc\.?',
        'm\.eng\.?', 'meng\.?',
        'm\.b\.a\.?', 'mba\.?',
        'm\.kes\.?', 'mkes\.?',
        'm\.farm\.?', 'mfarm\.?',
        'm\.psi\.?', 'mpsi\.?',
        'm\.ak\.?', 'mak\.?',
        'm\.e\.?', 'me\.?',
        'm\.tr\.?', 'mtr\.?',
        // Spesialis / Doktoral / Luar Negeri / Profesi
        'sp\.[a-z]+', 'sp\.[a-z]+\([a-z]+\)',
        'ph\.d\.?', 'phd\.?', 'b\.sc\.?', 'bsc\.?', 'b\.a\.?', 'ba\.?', 'm\.phil\.?', 'mphil\.?',
        'ak\.?', 'ca\.?', 'cpa\.?', 'qia\.?', 'crmp\.?'
    ];

    /**
     * In-memory cache for resolved identifiers.
     */
    protected static array $resolvedIdentifierCache = [];

    /**
     * Normalize a party/employee/vendor name to the single consistent standard:
     * - Strips academic and honorific titles (both prefixes and suffixes).
     * - Preserves legal entity prefixes (PT, CV, UD, etc.).
     * - Capitalization: UPPERCASE (ALL CAPS).
     * - Trims redundant whitespace and trailing punctuation.
     */
    public static function normalize(?string $name): string
    {
        if ($name === null) {
            return '';
        }

        $clean = trim($name);
        if ($clean === '') {
            return '';
        }

        // Clean out quotes if any
        $clean = trim($clean, "\"'`");

        // Do not strip prefixes if it is a company or organization
        if (preg_match('/^(?:pt|cv|ud|pd|koperasi|kop|yayasan|kantor|badan|dinas|pemerintah)\b/i', $clean)) {
            $clean = preg_replace('/\s+/', ' ', $clean);
            return mb_strtoupper(trim($clean), 'UTF-8');
        }

        // 1. Remove prefixes (e.g., Prof., Dr., Drs., Dra., Ir., H., Hj., etc.)
        $prefixPattern = '/^(?:(?:' . implode('|', self::$prefixes) . ')\.?\s+)+/i';
        $clean = preg_replace($prefixPattern, '', $clean);

        // 2. Remove suffixes after a comma (e.g. ", S.Tr.Stat.", ", S.E., M.Si.")
        if (str_contains($clean, ',')) {
            $parts = explode(',', $clean);
            $baseName = trim(array_shift($parts));
            
            if (strlen($baseName) >= 2) {
                $clean = $baseName;
            }
        }

        // 3. Remove trailing suffixes even if written without a comma (e.g., "KHOMARUDIN SST", "SUCIPTO ST")
        $suffixPattern = implode('|', self::$suffixes);
        $iterations = 0;
        while ($iterations < 3 && preg_match('/\s+(?:' . $suffixPattern . ')$/i', $clean)) {
            $clean = preg_replace('/\s+(?:' . $suffixPattern . ')$/i', '', $clean);
            $iterations++;
        }

        // 4. Clean punctuation and spaces
        $clean = preg_replace('/[,\.]+$/', '', trim($clean));
        $clean = preg_replace('/\s+/', ' ', $clean);

        return mb_strtoupper(trim($clean), 'UTF-8');
    }

    /**
     * Clean and standardize unique identifier (NIP, NPWP, NIK).
     * Strips non-digits, formatting characters (dots, hyphens).
     */
    public static function cleanIdentifier(?string $id): string
    {
        if ($id === null) {
            return '';
        }
        return preg_replace('/[^0-9]/', '', (string)$id);
    }

    /**
     * Resolve unique identifier (NIP/NPWP/NIK) for a normalized name.
     * Prioritizes 18-digit NIP for BPS employees.
     */
    public static function resolveIdentifier(string $name, ?string $rawIdentifier = null): string
    {
        $cleanId = self::cleanIdentifier($rawIdentifier);
        $normalizedName = self::normalize($name);
        if (!$normalizedName) {
            return $cleanId;
        }

        // Jika rawIdentifier sudah berupa NIP 18 digit, langsung gunakan
        if (strlen($cleanId) === 18) {
            return $cleanId;
        }

        if (isset(self::$resolvedIdentifierCache[$normalizedName])) {
            return self::$resolvedIdentifierCache[$normalizedName];
        }

        // 1. Cek User dan Pegawai: Jika nama ini terdaftar sebagai pegawai, utamakan NIP (18 digit)
        $employeeNip = null;
        $user = User::whereRaw('UPPER(TRIM(name)) = ?', [$normalizedName])->first();
        if ($user && ($user->nip_baru || $user->nip)) {
            $employeeNip = self::cleanIdentifier($user->nip_baru ?: $user->nip);
        } else {
            $users = User::where(function ($q) {
                $q->whereNotNull('nip_baru')->orWhereNotNull('nip');
            })->get();
            foreach ($users as $u) {
                if (self::normalize($u->name) === $normalizedName) {
                    $employeeNip = self::cleanIdentifier($u->nip_baru ?: $u->nip);
                    break;
                }
            }
        }

        if (!$employeeNip && class_exists(Pegawai::class)) {
            $pegawai = Pegawai::whereRaw('UPPER(TRIM(nama)) = ?', [$normalizedName])->first();
            if ($pegawai && !empty($pegawai->nip)) {
                $employeeNip = self::cleanIdentifier($pegawai->nip);
            } else {
                $pegawais = Pegawai::whereNotNull('nip')->get();
                foreach ($pegawais as $p) {
                    if (self::normalize($p->nama) === $normalizedName) {
                        $employeeNip = self::cleanIdentifier($p->nip);
                        break;
                    }
                }
            }
        }

        if ($employeeNip && strlen($employeeNip) === 18) {
            self::$resolvedIdentifierCache[$normalizedName] = $employeeNip;
            return $employeeNip;
        }

        // 2. Jika bukan pegawai di tabel User/Pegawai, tetapi rawIdentifier ada (misal NPWP 15/16 digit rekanan), pakai rawIdentifier
        if (!empty($cleanId)) {
            self::$resolvedIdentifierCache[$normalizedName] = $cleanId;
            return $cleanId;
        }

        // 3. Cek riwayat Sp2dPajak: utamakan NIP 18 digit jika ada
        $found = Sp2dPajak::whereRaw('UPPER(TRIM(nama_pihak)) = ?', [$normalizedName])
            ->whereRaw('LENGTH(TRIM(npwp_nik)) = 18')
            ->value('npwp_nik');

        // 4. Jika tidak ada NIP 18 digit, cari NPWP/NIK apa pun di riwayat Sp2dPajak
        if (!$found) {
            $found = Sp2dPajak::whereRaw('UPPER(TRIM(nama_pihak)) = ?', [$normalizedName])
                ->whereNotNull('npwp_nik')
                ->where('npwp_nik', '!=', '')
                ->value('npwp_nik');
        }

        self::$resolvedIdentifierCache[$normalizedName] = $found ? self::cleanIdentifier($found) : '';
        return self::$resolvedIdentifierCache[$normalizedName];
    }
}
