<div class="fi-ta-ctn rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10" style="width: 100%;">
    <div style="overflow-x: auto; width: 100%;">
        <table class="fi-ta-table w-full text-left divide-y divide-gray-200 dark:divide-white/5" style="width: 100%; min-width: 100%; border-collapse: collapse; text-align: left;">
            <thead class="bg-gray-50 dark:bg-white/5" style="background-color: rgba(156, 163, 175, 0.1);">
                <tr>
                    <th class="fi-ta-header-cell" style="padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; width: 100%;">No. SP2D / SP2D Referensi</th>
                    <th class="fi-ta-header-cell" style="padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; white-space: nowrap; width: 1%;">Tanggal SP2D</th>
                    <th class="fi-ta-header-cell" style="padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; white-space: nowrap; width: 1%;">Jenis Potongan</th>
                    <th class="fi-ta-header-cell" style="padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; text-align: right; white-space: nowrap; width: 1%;">Nominal Pajak</th>
                    <th class="fi-ta-header-cell" style="padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; text-align: center; white-space: nowrap; width: 1%;">Aksi</th>
                </tr>
            </thead>
            @php
                $masterAkunPajak = \App\Models\AkunPajak::all()->pluck('nama_pendek', 'kode')->toArray();
            @endphp
            @forelse($pajaks as $pajak)
                <tbody x-data="{ expanded: false }" class="divide-y divide-gray-200 dark:divide-white/5">
                    <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5" style="border-top: 1px solid rgba(156, 163, 175, 0.2);">
                        <td class="fi-ta-cell" style="padding: 1rem; font-size: 0.875rem; font-weight: 500; width: 100%; max-width: 300px; white-space: normal; word-break: break-word;">
                            {{ $pajak->rekap->no_sp2d }}
                        </td>
                        <td class="fi-ta-cell" style="padding: 1rem; font-size: 0.875rem; white-space: nowrap; width: 1%;">
                            {{ \Carbon\Carbon::parse($pajak->rekap->tgl_sp2d)->format('d/m/Y') }}
                        </td>
                        <td class="fi-ta-cell" style="padding: 1rem; font-size: 0.875rem; white-space: nowrap; width: 1%;">
                            {{ $pajak->kode_akun_pajak }}
                            @php
                                $namaAkun = $masterAkunPajak[(string)$pajak->kode_akun_pajak] ?? 'Pajak Lainnya';
                            @endphp
                            <span style="font-size: 0.75rem; opacity: 0.7; display: block; margin-top: 0.125rem;">{{ $namaAkun }}</span>
                        </td>
                        <td class="fi-ta-cell" style="padding: 1rem; font-size: 0.875rem; white-space: nowrap; text-align: right; width: 1%;">
                            Rp {{ number_format($pajak->nominal_pajak, 0, ',', '.') }}
                        </td>
                        <td class="fi-ta-cell" style="padding: 1rem; font-size: 0.875rem; white-space: nowrap; text-align: center; width: 1%;">
                            <x-filament::button
                                @click="expanded = !expanded"
                                size="sm"
                                color="gray"
                            >
                                <span x-text="expanded ? 'Tutup Detail' : 'Detail SP2D'"></span>
                            </x-filament::button>
                        </td>
                    </tr>
                    <tr x-show="expanded" x-transition style="display: none; background-color: rgba(156, 163, 175, 0.05);">
                        <td colspan="5" style="padding: 1rem;">
                            <div style="font-size: 0.875rem; display: flex; gap: 2rem; flex-wrap: wrap;">
                                <div style="flex: 3; min-width: 200px; word-break: break-word; white-space: normal;">
                                    <strong>Uraian:</strong><br>
                                    {{ $pajak->rekap->uraian ?: '-' }}
                                </div>
                                <div style="flex: 1; min-width: 150px; word-break: break-word; white-space: normal;">
                                    <strong>Jenis SPM:</strong><br>
                                    {{ $pajak->rekap->jenis_spm ?: '-' }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="5" class="fi-ta-cell" style="padding: 1.5rem; text-align: center; font-size: 0.875rem; opacity: 0.7;">
                            Tidak ada rincian ditemukan.
                        </td>
                    </tr>
                </tbody>
            @endforelse
            <tfoot class="bg-gray-50 dark:bg-white/5" style="border-top: 1px solid rgba(156, 163, 175, 0.2); background-color: rgba(156, 163, 175, 0.05);">
                @php
                    $groupedPajaks = $pajaks->groupBy('kode_akun_pajak');
                @endphp
                @foreach($groupedPajaks as $kode => $group)
                    @php
                        $namaAkun = $masterAkunPajak[(string)$kode] ?? 'Pajak Lainnya';
                        $subtotal = $group->sum('nominal_pajak');
                    @endphp
                    <tr>
                        <td colspan="3" style="padding: 0.5rem 1rem; font-size: 0.875rem; text-align: right; opacity: 0.8;">
                            Total {{ $namaAkun }}
                        </td>
                        <td style="padding: 0.5rem 1rem; font-size: 0.875rem; text-align: right; font-weight: 500; opacity: 0.8;">
                            Rp {{ number_format($subtotal, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                @endforeach
                <tr style="border-top: 1px solid rgba(156, 163, 175, 0.2);">
                    <td colspan="3" style="padding: 1rem; font-size: 0.875rem; font-weight: 600; text-align: right;">
                        Grand Total
                    </td>
                    <td style="padding: 1rem; font-size: 1rem; font-weight: 700; text-align: right;">
                        Rp {{ number_format($pajaks->sum('nominal_pajak'), 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
