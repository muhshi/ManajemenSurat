<?php

namespace App\Filament\Widgets;


use App\Models\Disposisi;
use App\Models\Surat;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class PerluTindakanSaya extends Widget
{
    protected static string $view = 'filament.widgets.perlu-tindakan-saya';

    public static function canView(): bool
    {
        return false;
    }

    protected static ?int $sort = 2; // Position below the main stats

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = Auth::user();

        // 1. Surat Masuk Belum Didisposisi (Pending Dispositions for current user)
        $pendingDisposisi = Disposisi::where('penerima_id', $user->id)
            ->where('status', '!=', 'Selesai')
            ->count();

        // 2. Surat Menunggu Tanda Tangan
        $waitingSignature = 0;
        if ($user->nip) {
            $waitingSignature = Surat::where('signer_nip', $user->nip)
                ->whereNull('file_path')
                ->count();
        }

        // 3. Draft Surat Keluar Belum Selesai
        $draftSurat = Surat::where('jenis_surat', '!=', 'SK')
            ->whereNull('file_path')
            ->count();

        return [
            'stats' => [
                [
                    'label' => 'Disposisi Perlu Tindak Lanjut',
                    'value' => $pendingDisposisi,
                    'description' => 'Surat masuk yang belum selesai didisposisi',
                    'icon' => 'heroicon-m-inbox-arrow-down',
                    'color_hex' => '#ef4444', // Red-500
                    'url' => route('filament.admin.resources.surat-masuks.index'),
                ],
                [
                    'label' => 'Menunggu Tanda Tangan',
                    'value' => $waitingSignature,
                    'description' => 'Surat yang perlu Anda tandatangani',
                    'icon' => 'heroicon-m-pencil-square',
                    'color_hex' => '#f59e0b', // Amber-500
                    'url' => route('filament.admin.resources.surat-keluars.index'),
                ],
                [
                    'label' => 'Draft Belum Selesai',
                    'value' => $draftSurat,
                    'description' => 'Surat keluar yang belum diupload',
                    'icon' => 'heroicon-m-document',
                    'color_hex' => '#6b7280', // Gray-500
                    'url' => route('filament.admin.resources.surat-keluars.index'),
                ],
            ],
        ];
    }
}
