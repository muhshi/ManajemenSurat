<div x-data="{ activeTab: 'sop' }" class="space-y-4">
    <!-- Header Banner & Download Button -->
    <div class="p-4 rounded-xl bg-gradient-to-r from-primary-600 to-primary-800 text-white shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <x-heroicon-o-book-open class="w-6 h-6 text-primary-200" />
                <h3 class="text-lg font-bold">Pedoman Penggunaan Modul Rekap SP2D & Pajak</h3>
            </div>
            <p class="text-xs text-primary-100 mt-1">
                Panduan operasional resmi untuk pengelolaan, verifikasi, dan rekonsiliasi perpajakan BPS Kabupaten Demak.
            </p>
        </div>
        <a 
            href="{{ route('docs.pedoman-sp2d') }}" 
            target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2 bg-white text-primary-700 hover:bg-primary-50 rounded-lg text-sm font-semibold shadow-sm transition shrink-0"
        >
            <x-heroicon-o-arrow-down-tray class="w-4 h-4" />
            <span>Unduh Dokumen Lengkap (.docx)</span>
        </a>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700 gap-1 overflow-x-auto text-sm font-medium">
        <button 
            type="button"
            @click="activeTab = 'sop'" 
            :class="activeTab === 'sop' ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="py-2.5 px-4 border-b-2 transition whitespace-nowrap flex items-center gap-2"
        >
            <x-heroicon-o-clipboard-document-check class="w-4 h-4" />
            <span>SOP & Alur Bulanan</span>
        </button>
        <button 
            type="button"
            @click="activeTab = 'jalur'" 
            :class="activeTab === 'jalur' ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="py-2.5 px-4 border-b-2 transition whitespace-nowrap flex items-center gap-2"
        >
            <x-heroicon-o-arrows-pointing-in class="w-4 h-4" />
            <span>Jalur & Status Verifikasi</span>
        </button>
        <button 
            type="button"
            @click="activeTab = 'excel'" 
            :class="activeTab === 'excel' ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="py-2.5 px-4 border-b-2 transition whitespace-nowrap flex items-center gap-2"
        >
            <x-heroicon-o-table-cells class="w-4 h-4" />
            <span>Format Excel Rincian</span>
        </button>
        <button 
            type="button"
            @click="activeTab = 'faq'" 
            :class="activeTab === 'faq' ? 'border-primary-600 text-primary-600 dark:text-primary-400 dark:border-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
            class="py-2.5 px-4 border-b-2 transition whitespace-nowrap flex items-center gap-2"
        >
            <x-heroicon-o-question-mark-circle class="w-4 h-4" />
            <span>FAQ & Solusi Masalah</span>
        </button>
    </div>

    <!-- Tab 1: SOP & Alur Bulanan -->
    <div x-show="activeTab === 'sop'" class="space-y-3 text-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-semibold mb-1">
                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-xs flex items-center justify-center font-bold">1</span>
                    Unduh Berkas MyIntress (T+1)
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Unduh file <strong>Monitoring SPP SPM SP2D</strong> dan <strong>Monitoring Potongan SPM</strong> dari aplikasi MyIntress untuk periode bulan berkenaan.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-semibold mb-1">
                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-xs flex items-center justify-center font-bold">2</span>
                    Import ke Aplikasi
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Klik tombol <strong>"Import SP2D MyIntress"</strong> di kanan atas halaman Data Rekap SP2D. Unggah kedua berkas yang telah diunduh lalu proses.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-semibold mb-1">
                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-xs flex items-center justify-center font-bold">3</span>
                    Filter 'Perlu Rincian'
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Gunakan filter tabel untuk melihat transaksi yang berstatus <strong>"Perlu Rincian"</strong> (umumnya SP2D Gaji, Tukin, Uang Makan, atau Lembur).
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-semibold mb-1">
                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-xs flex items-center justify-center font-bold">4</span>
                    Upload Rincian via Excel
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Klik tombol aksi <strong>"Rincian via Excel"</strong> pada baris SP2D, pilih jenis berkas (Daftar Gaji / Tukin / Uang Makan / Lembur) dan unggah file rinciannya.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-semibold mb-1">
                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-xs flex items-center justify-center font-bold">5</span>
                    Verifikasi Keseimbangan
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Pastikan indikator <em>"Total Rincian Saat Ini"</em> berwarna hijau <strong>(Sesuai)</strong>. Saat total rincian tepat 100% sama dengan target potongan, status otomatis menjadi <strong>Valid</strong>.
                </p>
            </div>

            <div class="p-3.5 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-semibold mb-1">
                    <span class="w-6 h-6 rounded-full bg-primary-100 dark:bg-primary-950 text-xs flex items-center justify-center font-bold">6</span>
                    Cetak Laporan Rekap Per Pihak
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Buka menu <strong>Rekap Per Pihak</strong>, tentukan filter bulan/tahun, lalu export ke <strong>PDF Ber-Bookmark</strong> atau <strong>Excel Multi-Sheet</strong> untuk LPJ Bendahara.
                </p>
            </div>
        </div>
    </div>

    <!-- Tab 2: Jalur & Status Verifikasi -->
    <div x-show="activeTab === 'jalur'" class="space-y-3 text-sm">
        <div class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold">
                    <tr>
                        <th class="p-2.5">Jalur Transaksi</th>
                        <th class="p-2.5">Karakteristik Transaksi</th>
                        <th class="p-2.5">Aturan Validasi Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr>
                        <td class="p-2.5 font-semibold text-success-600 dark:text-success-400 whitespace-nowrap">
                            1 Pihak (LS Pihak Ketiga)
                        </td>
                        <td class="p-2.5 text-gray-600 dark:text-gray-400">
                            Penerima tunggal (rekanan/pihak ketiga). Potongan pajak langsung tercatat pada berkas Potongan SPM MyIntress.
                        </td>
                        <td class="p-2.5 text-gray-600 dark:text-gray-400">
                            <strong>Otomatis Valid</strong> saat import jika datanya ada di file Potongan SPM, atau jika potongan SP2D bernilai 0.
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2.5 font-semibold text-warning-600 dark:text-warning-400 whitespace-nowrap">
                            Banyak Pihak (Gaji/Tukin)
                        </td>
                        <td class="p-2.5 text-gray-600 dark:text-gray-400">
                            Penerima jamak (banyak pegawai/rekanan). Pada MyIntress hanya tercatat gelondongan atas nama BPS Kabupaten Demak.
                        </td>
                        <td class="p-2.5 text-gray-600 dark:text-gray-400">
                            Awalnya <strong>Perlu Rincian</strong>. Berubah otomatis menjadi <strong>Valid</strong> begitu total nominal rincian pajak tepat 100% sama dengan target potongan.
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2.5 font-semibold text-purple-600 dark:text-purple-400 whitespace-nowrap">
                            UP (Uang Persediaan)
                        </td>
                        <td class="p-2.5 text-gray-600 dark:text-gray-400">
                            SP2D keperluan Uang Persediaan / Tambahan UP. Rincian potongan pajaknya dinamis mengikuti realisasi SPJ bendahara.
                        </td>
                        <td class="p-2.5 text-gray-600 dark:text-gray-400">
                            <strong>Tidak terikat target kaku</strong>. Operator/Bendahara dapat bebas menentukan status verifikasi secara manual pada form edit.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: Format Excel Rincian -->
    <div x-show="activeTab === 'excel'" class="space-y-3 text-sm">
        <p class="text-xs text-gray-600 dark:text-gray-400">
            Sistem secara otomatis membaca identitas pegawai (mencari kolom <code>npwp</code>, <code>nip</code>, <code>nik</code>/<code>noktp</code>) dan melakukan pencocokan cerdas dengan database pegawai jika kolom tidak ditemukan:
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40">
                <span class="font-bold text-gray-900 dark:text-white block mb-1">1. Daftar Gaji Pusat (Aplikasi GPP)</span>
                <span class="text-gray-600 dark:text-gray-400">
                    Kolom wajib header: <code>nmpeg</code>, serta salah satu dari <code>potpfk10</code> atau <code>iwp</code>.<br>
                    Sistem mengekstrak otomatis akun: <strong>811311</strong> (PFK Bulanan), <strong>811211</strong> (PFK 2%), <strong>811111</strong> (IWP), <strong>811135</strong> (BPJS), <strong>411121</strong> (PPh 21), dan <strong>425151</strong> (Sewa Rumah).
                </span>
            </div>

            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40">
                <span class="font-bold text-gray-900 dark:text-white block mb-1">2. Daftar Tukin (Tunjangan Kinerja)</span>
                <span class="text-gray-600 dark:text-gray-400">
                    Kolom wajib header: <code>nama_pegawai</code> (atau <code>nmpeg</code>/<code>nama</code>) dan <code>pajak</code>.<br>
                    Nominal pada kolom pajak otomatis dicatat sebagai potongan PPh Pasal 21 (Kode Akun <strong>411121</strong>).
                </span>
            </div>

            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40">
                <span class="font-bold text-gray-900 dark:text-white block mb-1">3. Daftar Uang Makan</span>
                <span class="text-gray-600 dark:text-gray-400">
                    Kolom wajib header: <code>nmpeg</code> (atau <code>nama_pegawai</code>) dan <code>potongan</code>.<br>
                    Nilai potongan otomatis dipetakan ke PPh Pasal 21 (Kode Akun <strong>411121</strong>).
                </span>
            </div>

            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40">
                <span class="font-bold text-gray-900 dark:text-white block mb-1">4. Daftar Uang Lembur</span>
                <span class="text-gray-600 dark:text-gray-400">
                    Kolom wajib header: <code>pajak</code> serta <code>nmpeg</code> atau <code>nmrek</code>.<br>
                    Identitas dicocokkan otomatis dari kolom <code>npwp</code> atau <code>nip</code> sebagai pemotong PPh 21 (Kode Akun <strong>411121</strong>).
                </span>
            </div>
        </div>
    </div>

    <!-- Tab 4: FAQ & Solusi Masalah -->
    <div x-show="activeTab === 'faq'" class="space-y-3 text-xs">
        <div class="space-y-2">
            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <span class="font-semibold text-gray-900 dark:text-white block mb-0.5">
                    T: Kenapa saat import MyIntress muncul error "File salah! Kolom No. SP2D tidak ditemukan"?
                </span>
                <p class="text-gray-600 dark:text-gray-400">
                    J: Kemungkinan berkas tertukar. Pastikan Input 1 diisi berkas <em>Monitoring SPP SPM SP2D</em>, dan Input 2 diisi berkas <em>Monitoring Potongan SPM</em>.
                </p>
            </div>

            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <span class="font-semibold text-gray-900 dark:text-white block mb-0.5">
                    T: Mengapa status SP2D tetap "Perlu Rincian" padahal sudah saya isi semua?
                </span>
                <p class="text-gray-600 dark:text-gray-400">
                    J: Periksa indikator total rincian. Jika ada selisih walau hanya Rp 1 (biasanya akibat pembulatan nilai pecahan rupiah pada excel gaji/tukin), sistem belum menganggapnya seimbang. Sesuaikan pembulatan agar totalnya tepat sama dengan Target Potongan.
                </p>
            </div>

            <div class="p-3 rounded-lg border border-gray-200 dark:border-gray-800">
                <span class="font-semibold text-gray-900 dark:text-white block mb-0.5">
                    T: Mengapa file PDF atau Excel hasil ekspor tidak otomatis terdownload?
                </span>
                <p class="text-gray-600 dark:text-gray-400">
                    J: Fitur ekspor menggunakan pembukaan tab unduhan baru. Jika tidak terunduh, periksa bilah alamat browser Anda dan pastikan opsi <em>Pop-up Blocker</em> telah diizinkan (Allow) untuk situs ini.
                </p>
            </div>
        </div>
    </div>
</div>
