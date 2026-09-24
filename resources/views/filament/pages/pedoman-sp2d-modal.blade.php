<div x-data="{ activeTab: 'sop' }" style="font-family: inherit; font-size: 0.875rem; color: #27272a; line-height: 1.5;">

    <!-- Banner Header & Tombol Unduh -->
    <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border-radius: 0.75rem; padding: 1.25rem 1.5rem; margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div style="flex: 1; min-width: 240px;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <svg style="width: 22px; height: 22px; min-width: 22px; max-width: 22px; fill: none; stroke: currentColor; stroke-width: 2;" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
                <h4 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #ffffff;">Petunjuk Penggunaan Modul Rekap SP2D</h4>
            </div>
            <p style="margin: 0; font-size: 0.8rem; color: #e0f2fe;">
                Panduan ringkas alur kerja, pengelolaan rincian potongan pajak, dan rekonsiliasi per entitas.
            </p>
        </div>
        <div>
            <a 
                href="{{ route('docs.pedoman-sp2d') }}" 
                target="_blank"
                style="display: inline-flex; align-items: center; gap: 0.5rem; background: #ffffff; color: #0369a1; padding: 0.55rem 1.1rem; border-radius: 0.5rem; font-size: 0.85rem; font-weight: 600; text-decoration: none; box-shadow: 0 1px 3px rgba(0,0,0,0.15);"
            >
                <svg style="width: 17px; height: 17px; min-width: 17px; max-width: 17px; fill: none; stroke: currentColor; stroke-width: 2.2;" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                <span>Unduh Dokumen Lengkap (.docx)</span>
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid #e4e4e7; padding-bottom: 0.5rem; margin-bottom: 1.25rem; overflow-x: auto;">
        <button 
            type="button"
            @click="activeTab = 'sop'" 
            :style="activeTab === 'sop' ? 'background: #0284c7; color: #ffffff; font-weight: 600;' : 'background: #f4f4f5; color: #52525b;'"
            style="border: none; padding: 0.45rem 0.9rem; border-radius: 0.375rem; font-size: 0.825rem; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.35rem;"
        >
            <span>📋 1. Alur & SOP Bulanan</span>
        </button>
        <button 
            type="button"
            @click="activeTab = 'jalur'" 
            :style="activeTab === 'jalur' ? 'background: #0284c7; color: #ffffff; font-weight: 600;' : 'background: #f4f4f5; color: #52525b;'"
            style="border: none; padding: 0.45rem 0.9rem; border-radius: 0.375rem; font-size: 0.825rem; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.35rem;"
        >
            <span>⚖️ 2. Jalur Transaksi</span>
        </button>
        <button 
            type="button"
            @click="activeTab = 'excel'" 
            :style="activeTab === 'excel' ? 'background: #0284c7; color: #ffffff; font-weight: 600;' : 'background: #f4f4f5; color: #52525b;'"
            style="border: none; padding: 0.45rem 0.9rem; border-radius: 0.375rem; font-size: 0.825rem; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.35rem;"
        >
            <span>📊 3. Format Excel Rincian</span>
        </button>
        <button 
            type="button"
            @click="activeTab = 'faq'" 
            :style="activeTab === 'faq' ? 'background: #0284c7; color: #ffffff; font-weight: 600;' : 'background: #f4f4f5; color: #52525b;'"
            style="border: none; padding: 0.45rem 0.9rem; border-radius: 0.375rem; font-size: 0.825rem; cursor: pointer; transition: all 0.15s; display: inline-flex; align-items: center; gap: 0.35rem;"
        >
            <span>❓ 4. Tanya & Jawab</span>
        </button>
    </div>

    <!-- Tab 1: SOP Bulanan -->
    <div x-show="activeTab === 'sop'" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.75rem;">
        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 9999px; background: #0284c7; color: #fff; font-size: 0.75rem; font-weight: 700;">1</span>
                <strong style="color: #0369a1; font-size: 0.85rem;">Unduh MyIntress (T+1)</strong>
            </div>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Unduh file <strong>Monitoring SPP SPM SP2D</strong> dan <strong>Monitoring Potongan SPM</strong> bulan berkenaan dari portal MyIntress.
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 9999px; background: #0284c7; color: #fff; font-size: 0.75rem; font-weight: 700;">2</span>
                <strong style="color: #0369a1; font-size: 0.85rem;">Import ke Sistem</strong>
            </div>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Buka menu <strong>Data Rekap SP2D</strong>, klik tombol <strong>"Import SP2D MyIntress"</strong>, lalu unggah kedua berkas excel tersebut.
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 9999px; background: #0284c7; color: #fff; font-size: 0.75rem; font-weight: 700;">3</span>
                <strong style="color: #0369a1; font-size: 0.85rem;">Filter 'Perlu Rincian'</strong>
            </div>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Filter status tabel untuk menampilkan SP2D yang butuh rincian penerima (biasanya SP2D Gaji, Tukin, Uang Makan, atau Lembur).
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 9999px; background: #0284c7; color: #fff; font-size: 0.75rem; font-weight: 700;">4</span>
                <strong style="color: #0369a1; font-size: 0.85rem;">Unggah Rincian Excel</strong>
            </div>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Klik tombol aksi <strong>"Upload Excel"</strong> pada baris SP2D, pilih jenis berkas (Gaji/Tukin/Uang Makan/Lembur), lalu unggah.
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 9999px; background: #0284c7; color: #fff; font-size: 0.75rem; font-weight: 700;">5</span>
                <strong style="color: #0369a1; font-size: 0.85rem;">Verifikasi Keseimbangan</strong>
            </div>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Saat total rincian tepat 100% sama dengan target potongan SP2D, status transaksi otomatis berubah menjadi <strong>Valid</strong>.
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 9999px; background: #0284c7; color: #fff; font-size: 0.75rem; font-weight: 700;">6</span>
                <strong style="color: #0369a1; font-size: 0.85rem;">Cetak Rekap Per Pihak</strong>
            </div>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Buka menu <strong>Rekap Per Pihak</strong>, tentukan filter bulan/tahun, lalu export ke <strong>PDF Ber-Bookmark</strong> atau <strong>Excel</strong> untuk LPJ.
            </p>
        </div>
    </div>

    <!-- Tab 2: Jalur Transaksi -->
    <div x-show="activeTab === 'jalur'" style="border: 1px solid #e4e4e7; border-radius: 0.5rem; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: left;">
            <thead style="background: #f4f4f5; color: #3f3f46; font-weight: 600;">
                <tr>
                    <th style="padding: 0.65rem 0.85rem; border-bottom: 1px solid #e4e4e7;">Jalur</th>
                    <th style="padding: 0.65rem 0.85rem; border-bottom: 1px solid #e4e4e7;">Karakteristik</th>
                    <th style="padding: 0.65rem 0.85rem; border-bottom: 1px solid #e4e4e7;">Validasi Status</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #f4f4f5;">
                    <td style="padding: 0.65rem 0.85rem; font-weight: 600; color: #16a34a; white-space: nowrap;">1 Pihak</td>
                    <td style="padding: 0.65rem 0.85rem; color: #52525b;">Penerima tunggal (rekanan/pihak ketiga). Potongan pajak langsung tercatat di file Potongan SPM.</td>
                    <td style="padding: 0.65rem 0.85rem; color: #52525b;"><strong>Otomatis Valid</strong> saat import jika datanya ada di Potongan SPM atau bernilai Rp 0.</td>
                </tr>
                <tr style="border-bottom: 1px solid #f4f4f5;">
                    <td style="padding: 0.65rem 0.85rem; font-weight: 600; color: #d97706; white-space: nowrap;">Banyak Pihak</td>
                    <td style="padding: 0.65rem 0.85rem; color: #52525b;">Penerima jamak (gaji, tukin, uang makan). Di MyIntress hanya tercatat gelondongan.</td>
                    <td style="padding: 0.65rem 0.85rem; color: #52525b;">Awalnya <strong>Perlu Rincian</strong>. Otomatis Valid begitu rincian 100% cocok dengan target potongan.</td>
                </tr>
                <tr>
                    <td style="padding: 0.65rem 0.85rem; font-weight: 600; color: #7c3aed; white-space: nowrap;">UP (Uang Persediaan)</td>
                    <td style="padding: 0.65rem 0.85rem; color: #52525b;">SP2D keperluan UP / TUP bendahara. Rincian potongannya dinamis sesuai realisasi belanja.</td>
                    <td style="padding: 0.65rem 0.85rem; color: #52525b;"><strong>Tidak terikat target kaku</strong>. Operator/Bendahara bebas mengubah status verifikasi di form edit.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Tab 3: Format Excel Rincian -->
    <div x-show="activeTab === 'excel'" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.75rem;">
        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <strong style="color: #0f172a; font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">1. Daftar Gaji Pusat (Aplikasi GPP)</strong>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Kolom header: <code>nmpeg</code>, <code>nip</code> / <code>npwp</code>, serta salah satu dari <code>potpfk10</code> atau <code>iwp</code>.<br>
                Mengekstrak otomatis: 811311, 811211, 811111, 811135, 411121, 425151.
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <strong style="color: #0f172a; font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">2. Daftar Tukin (Tunjangan Kinerja)</strong>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Kolom header: <code>nama_pegawai</code> (atau <code>nmpeg</code>), <code>pajak</code>, dan <code>nip</code> / <code>npwp</code>.<br>
                Nominal pajak otomatis dicatat sebagai PPh Pasal 21 (Kode Akun <strong>411121</strong>).
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <strong style="color: #0f172a; font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">3. Daftar Uang Makan</strong>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Kolom header: <code>nmpeg</code>, <code>potongan</code>, dan <code>nip</code> / <code>npwp</code>.<br>
                Nilai potongan dipetakan otomatis ke PPh Pasal 21 (Kode Akun <strong>411121</strong>).
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <strong style="color: #0f172a; font-size: 0.85rem; display: block; margin-bottom: 0.25rem;">4. Daftar Uang Lembur</strong>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                Kolom header: <code>pajak</code>, serta <code>nmpeg</code> atau <code>nmrek</code>, dan <code>nip</code> / <code>npwp</code>.<br>
                Nominal pajak dipetakan otomatis ke PPh Pasal 21 (Kode Akun <strong>411121</strong>).
            </p>
        </div>
    </div>

    <!-- Tab 4: FAQ -->
    <div x-show="activeTab === 'faq'" style="display: flex; flex-direction: column; gap: 0.65rem;">
        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <strong style="font-size: 0.825rem; color: #0f172a; display: block; margin-bottom: 0.25rem;">
                T: Kenapa import MyIntress gagal dengan pesan "Kolom No. SP2D tidak ditemukan"?
            </strong>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                J: File tertukar. Pastikan Input 1 diisi berkas <em>Monitoring SPP SPM SP2D</em>, dan Input 2 diisi berkas <em>Monitoring Potongan SPM</em>.
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <strong style="font-size: 0.825rem; color: #0f172a; display: block; margin-bottom: 0.25rem;">
                T: Mengapa status SP2D tetap "Perlu Rincian" padahal semua baris sudah terisi?
            </strong>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                J: Periksa indikator total rincian. Jika ada selisih walau hanya Rp 1 (karena pembulatan pecahan pada excel gaji/tukin), sesuaikan salah satu baris rincian agar totalnya tepat sama dengan Target Potongan.
            </p>
        </div>

        <div style="border: 1px solid #e4e4e7; border-radius: 0.5rem; padding: 0.85rem; background: #fafafa;">
            <strong style="font-size: 0.825rem; color: #0f172a; display: block; margin-bottom: 0.25rem;">
                T: Mengapa berkas PDF / Excel hasil export tidak otomatis terunduh?
            </strong>
            <p style="margin: 0; font-size: 0.785rem; color: #52525b;">
                J: Periksa bilah alamat browser Anda dan pastikan fitur <em>Pop-up Blocker</em> diizinkan (Allow) untuk situs ini.
            </p>
        </div>
    </div>
</div>
