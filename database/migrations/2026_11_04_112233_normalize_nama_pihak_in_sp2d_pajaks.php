<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Sp2dPajak;
use App\Services\PartyNameNormalizer;

return new class extends Migration
{
    /**
     * Normalize all existing nama_pihak values in sp2d_pajaks:
     * - Strip academic titles / honorifics (prefix & suffix)
     * - Convert to UPPERCASE
     * - Clean extra whitespace and trailing punctuation
     * - Re-resolve npwp_nik from known sources for rows that still have it empty
     */
    public function up(): void
    {
        $pajaks = Sp2dPajak::all();

        foreach ($pajaks as $pajak) {
            $rawNama = $pajak->nama_pihak;
            $normalizedNama = PartyNameNormalizer::normalize($rawNama);

            if ($normalizedNama === mb_strtoupper(trim($rawNama ?? ''), 'UTF-8') && !empty($pajak->npwp_nik)) {
                // Name is already normalized and identifier exists — skip
                continue;
            }

            $updateData = [];

            if ($normalizedNama !== $rawNama) {
                $updateData['nama_pihak'] = $normalizedNama;
            }

            // Re-resolve npwp_nik if empty
            if (empty($pajak->npwp_nik) && $normalizedNama) {
                $resolved = PartyNameNormalizer::resolveIdentifier($normalizedNama);
                if ($resolved) {
                    $updateData['npwp_nik'] = $resolved;
                }
            }

            if (!empty($updateData)) {
                $pajak->update($updateData);
            }
        }

        // Also propagate known npwp_nik to records with same normalized name
        $knownPajaks = Sp2dPajak::whereNotNull('npwp_nik')
            ->where('npwp_nik', '!=', '')
            ->select(DB::raw('UPPER(TRIM(nama_pihak)) as nama'), DB::raw('MAX(npwp_nik) as npwp'))
            ->groupBy(DB::raw('UPPER(TRIM(nama_pihak))'))
            ->get();

        foreach ($knownPajaks as $item) {
            Sp2dPajak::whereRaw('UPPER(TRIM(nama_pihak)) = ?', [$item->nama])
                ->where(function ($q) {
                    $q->whereNull('npwp_nik')->orWhere('npwp_nik', '');
                })
                ->update(['npwp_nik' => $item->npwp]);
        }
    }

    /**
     * Reverse the migrations.
     * Data enrichment/normalisation cannot be meaningfully reversed.
     */
    public function down(): void
    {
        // Data normalization is a one-way operation; no rollback needed.
    }
};
