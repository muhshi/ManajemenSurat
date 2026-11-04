<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Sp2dPajak;
use App\Services\PartyNameNormalizer;

class NormalizeSp2dParties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp2d:normalize-parties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menstandardisasi nama pihak SP2D (huruf kapital, tanpa gelar) dan membersihkan/melengkapi NPWP/NIK';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai normalisasi nama pihak SP2D...');

        $totalUpdated = 0;
        $totalProcessed = 0;

        $count = Sp2dPajak::count();
        if ($count === 0) {
            $this->warn('Tidak ada data Sp2dPajak yang ditemukan.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Sp2dPajak::chunkById(200, function ($pajaks) use (&$totalUpdated, &$totalProcessed, $bar) {
            foreach ($pajaks as $pajak) {
                $totalProcessed++;
                $rawNama = $pajak->nama_pihak;
                $normalizedNama = PartyNameNormalizer::normalize($rawNama);

                $updateData = [];

                if ($normalizedNama !== '' && $normalizedNama !== $rawNama) {
                    $updateData['nama_pihak'] = $normalizedNama;
                }

                if (!empty($pajak->npwp_nik)) {
                    $cleanedId = PartyNameNormalizer::cleanIdentifier($pajak->npwp_nik);
                    if ($cleanedId !== $pajak->npwp_nik) {
                        $updateData['npwp_nik'] = $cleanedId;
                    }
                } elseif ($normalizedNama) {
                    $resolved = PartyNameNormalizer::resolveIdentifier($normalizedNama);
                    if ($resolved) {
                        $updateData['npwp_nik'] = $resolved;
                    }
                }

                if (!empty($updateData)) {
                    $pajak->update($updateData);
                    $totalUpdated++;
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        $this->info('Mempropagasi NPWP/NIK ke transaksi dengan nama yang sama...');
        $knownPajaks = Sp2dPajak::whereNotNull('npwp_nik')
            ->where('npwp_nik', '!=', '')
            ->select(DB::raw('UPPER(TRIM(nama_pihak)) as nama'), DB::raw('MAX(npwp_nik) as npwp'))
            ->groupBy(DB::raw('UPPER(TRIM(nama_pihak))'))
            ->get();

        $propagatedCount = 0;
        foreach ($knownPajaks as $item) {
            $affected = Sp2dPajak::whereRaw('UPPER(TRIM(nama_pihak)) = ?', [$item->nama])
                ->where(function ($q) {
                    $q->whereNull('npwp_nik')->orWhere('npwp_nik', '');
                })
                ->update(['npwp_nik' => $item->npwp]);
            $propagatedCount += $affected;
        }

        $this->info("Selesai! {$totalProcessed} data diproses, {$totalUpdated} data diperbarui format namanya, {$propagatedCount} transaksi dilengkapi NPWP/NIK-nya.");
        return self::SUCCESS;
    }
}
