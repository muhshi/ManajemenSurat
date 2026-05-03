<?php

namespace App\Services;

use App\Models\Agenda;
use App\Settings\SystemSettings;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class AgendaDocService
{
    public function generate(Agenda $agenda): string
    {
        $settings = app(SystemSettings::class);
        $templateName = $settings->template_notulensi;
        $templatePath = storage_path('app/public/' . $templateName);

        if (!$templateName || !file_exists($templatePath)) {
            throw new \RuntimeException('Template notulensi belum diupload di Pengaturan Sistem.');
        }

        $template = new TemplateProcessor($templatePath);
        $tanggal  = Carbon::parse($agenda->tanggal_rapat);
        $hariTanggalRapat = $tanggal->translatedFormat('l/d-m-Y');

        // --- Halaman 1: Surat Undangan ---
        $template->setValue('nomor_surat',       $agenda->nomor_surat);
        $template->setValue('tanggal_surat',     $agenda->tanggal_surat ? Carbon::parse($agenda->tanggal_surat)->translatedFormat('d F Y') : '-');
        $template->setValue('perihal',           $agenda->perihal);
        $template->setValue('penerima_undangan', $agenda->penerima_undangan);
        $template->setValue('hari_tanggal_rapat', $hariTanggalRapat);
        $template->setValue('waktu_mulai',       Carbon::parse($agenda->waktu_mulai)->format('H:i'));
        $template->setValue('waktu_selesai',     $agenda->waktu_selesai
            ? Carbon::parse($agenda->waktu_selesai)->format('H:i')
            : 'selesai');
        $template->setValue('tempat', $agenda->tempat);
        $template->setValue('agenda', $agenda->judul);

        // ${kepala} = Nama + NIP (satu placeholder mencakup seluruh blok tanda tangan)
        $signerName = $agenda->signer_name ?: ($settings->cert_signer_name ?? '');
        $signerNip  = $agenda->signer_nip  ?: ($settings->cert_signer_nip  ?? '');
        $template->setValue('kepala', $signerName . "\nNIP. " . $signerNip);

        // --- Halaman 2: Daftar Hadir (cloneRow) ---
        $peserta       = $agenda->peserta()->orderBy('urutan')->get();
        $jumlahPeserta = max($peserta->count(), 1);
        $template->cloneRow('nama', $jumlahPeserta);

        if ($peserta->count() > 0) {
            foreach ($peserta as $index => $p) {
                $row = $index + 1;
                $template->setValue("no#$row",      $row);
                $template->setValue("nama#$row",    $p->nama);
                $template->setValue("jabatan#$row", $p->jabatan);
            }
        } else {
            $template->setValue('no#1',      '');
            $template->setValue('nama#1',    '-');
            $template->setValue('jabatan#1', '-');
        }

        // --- Halaman 3: Notulen Rapat ---
        $template->setValue('notulis', $agenda->notulis ?: '-');

        // ${peserta} = keterangan peserta rapat (Ketua Tim, Kepala, dll)
        $template->setValue('peserta', $agenda->peserta_rapat ?: '-');

        // Placeholder konten notulensi
        $this->setValueSafe($template, 'isi_agenda',    $this->formatTextForWord($agenda->isi_notulensi));
        $this->setValueSafe($template, 'keputusan',     $this->formatTextForWord($agenda->keputusan));
        $this->setValueSafe($template, 'tindak_lanjut', $this->formatTextForWord($agenda->tindak_lanjut));

        // --- Dokumentasi / Foto ---
        // ${foto} di template akan dikosongkan (foto embed butuh metode berbeda)
        $this->setValueSafe($template, 'foto', '');

        // --- Simpan ke temp file ---
        $safeFilename = str_replace(['/', '\\'], '_', $agenda->nomor_surat);
        $fileName     = "Notulensi_Rapat_{$safeFilename}.docx";
        $tempPath     = storage_path('app/' . $fileName);
        $template->saveAs($tempPath);

        return $tempPath;
    }

    /**
     * Set value tapi skip jika placeholder tidak ditemukan di template.
     */
    private function setValueSafe(TemplateProcessor $template, string $key, string $value): void
    {
        try {
            $template->setValue($key, $value);
        } catch (\Exception) {
            // Placeholder tidak ada di template — lewati saja
        }
    }

    /**
     * Convert newline ke line break Word XML.
     */
    private function formatTextForWord(?string $text): string
    {
        if (!$text) return '-';

        $text = htmlspecialchars($text, ENT_XML1);

        return str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $text);
    }
}
