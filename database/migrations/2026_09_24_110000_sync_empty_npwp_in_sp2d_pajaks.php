<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Sp2dPajak;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sinkronisasi NPWP/NIK dari data sp2d_pajaks yang sudah ada untuk pihak dengan nama yang sama
        $knownPajaks = Sp2dPajak::whereNotNull('npwp_nik')
            ->where('npwp_nik', '!=', '')
            ->select(DB::raw('TRIM(nama_pihak) as nama'), DB::raw('MAX(npwp_nik) as npwp'))
            ->groupBy(DB::raw('TRIM(nama_pihak)'))
            ->get();

        foreach ($knownPajaks as $item) {
            Sp2dPajak::whereRaw('TRIM(nama_pihak) = ?', [$item->nama])
                ->where(function ($q) {
                    $q->whereNull('npwp_nik')->orWhere('npwp_nik', '');
                })
                ->update(['npwp_nik' => $item->npwp]);
        }

        // 2. Sinkronisasi NPWP/NIK dari tabel users (nip_baru / nip) untuk nama pegawai yang cocok
        $users = User::whereNotNull('name')
            ->where(function ($q) {
                $q->whereNotNull('nip_baru')->orWhereNotNull('nip');
            })
            ->get();

        foreach ($users as $user) {
            $nip = $user->nip_baru ?: $user->nip;
            if ($nip) {
                Sp2dPajak::whereRaw('TRIM(nama_pihak) = ?', [trim($user->name)])
                    ->where(function ($q) {
                        $q->whereNull('npwp_nik')->orWhere('npwp_nik', '');
                    })
                    ->update(['npwp_nik' => $nip]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data enrichment tidak perlu di-reverse
    }
};
