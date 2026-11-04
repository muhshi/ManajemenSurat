<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Sp2dPajak;
use App\Models\User;
use App\Models\Pegawai;
use App\Services\PartyNameNormalizer;

return new class extends Migration
{
    /**
     * Consolidate identifiers in sp2d_pajaks so that BPS employees consistently
     * have their 18-digit NIP across all transactions (preventing NIP vs NPWP split).
     */
    public function up(): void
    {
        // 1. Sinkronisasi NIP 18 digit dari tabel User
        $users = User::whereNotNull('name')
            ->where(function ($q) {
                $q->whereNotNull('nip_baru')->orWhereNotNull('nip');
            })
            ->get();

        foreach ($users as $user) {
            $nip = PartyNameNormalizer::cleanIdentifier($user->nip_baru ?: $user->nip);
            if (strlen($nip) === 18) {
                $normalizedName = PartyNameNormalizer::normalize($user->name);
                if ($normalizedName) {
                    Sp2dPajak::whereRaw('UPPER(TRIM(nama_pihak)) = ?', [$normalizedName])
                        ->update([
                            'nama_pihak' => $normalizedName,
                            'npwp_nik'   => $nip,
                        ]);
                }
            }
        }

        // 2. Sinkronisasi NIP 18 digit dari tabel Pegawai (jika ada)
        if (class_exists(Pegawai::class)) {
            $pegawais = Pegawai::whereNotNull('nama')
                ->whereNotNull('nip')
                ->get();

            foreach ($pegawais as $pegawai) {
                $nip = PartyNameNormalizer::cleanIdentifier($pegawai->nip);
                if (strlen($nip) === 18) {
                    $normalizedName = PartyNameNormalizer::normalize($pegawai->nama);
                    if ($normalizedName) {
                        Sp2dPajak::whereRaw('UPPER(TRIM(nama_pihak)) = ?', [$normalizedName])
                            ->update([
                                'nama_pihak' => $normalizedName,
                                'npwp_nik'   => $nip,
                            ]);
                    }
                }
            }
        }

        // 3. Propagasi NIP 18 digit yang sudah tercatat di transaksi ke baris lain dengan nama sama
        $nipGroups = Sp2dPajak::whereRaw('LENGTH(TRIM(npwp_nik)) = 18')
            ->select(DB::raw('UPPER(TRIM(nama_pihak)) as nama'), DB::raw('MAX(TRIM(npwp_nik)) as nip'))
            ->groupBy(DB::raw('UPPER(TRIM(nama_pihak))'))
            ->get();

        foreach ($nipGroups as $group) {
            Sp2dPajak::whereRaw('UPPER(TRIM(nama_pihak)) = ?', [$group->nama])
                ->where('npwp_nik', '!=', $group->nip)
                ->update(['npwp_nik' => $group->nip]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way consolidation data migration.
    }
};

