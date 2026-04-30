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
        $tanggal = Carbon::parse($agenda->tanggal_rapat);

        // --- Isi Placeholder Umum ---
        $template->setValue('nomor_surat', $agenda->nomor_surat);
        $template->setValue('judul', $agenda->judul);
        $template->setValue('perihal', $agenda->perihal);
        $template->setValue('penerima_undangan', $agenda->penerima_undangan);
        $template->setValue('tempat', $agenda->tempat);
        $template->setValue('hari_tanggal', $tanggal->translatedFormat('l, d F Y'));
        $template->setValue('tanggal_rapat', $tanggal->translatedFormat('d F Y'));
        $template->setValue('waktu_mulai', Carbon::parse($agenda->waktu_mulai)->format('H:i'));
        $template->setValue('waktu_selesai', $agenda->waktu_selesai ? Carbon::parse($agenda->waktu_selesai)->format('H:i') : 'selesai');
        $template->setValue('pimpinan_rapat', $agenda->pimpinan_rapat);
        $template->setValue('narasumber', $agenda->narasumber ?: '-');
        $template->setValue('notulis', $agenda->notulis ?: '-');

        // Notulensi (newline jadi line break Word)
        $template->setValue('isi_notulensi', $this->formatTextForWord($agenda->isi_notulensi));
        $template->setValue('keputusan', $this->formatTextForWord($agenda->keputusan));
        $template->setValue('tindak_lanjut', $this->formatTextForWord($agenda->tindak_lanjut));

        // Signer Snapshot
        $template->setValue('nama_kepala', $agenda->signer_name);
        $template->setValue('nip_kepala', $agenda->signer_nip);
        $template->setValue('jabatan_kepala', $agenda->signer_title);
        $template->setValue('kota_penetapan', $agenda->signer_city);

        // --- Isi Daftar Hadir (cloneRow) ---
        $peserta = $agenda->peserta()->orderBy('urutan')->get();
        
        // Pastikan minimal ada 1 baris agar tidak error jika peserta kosong
        $template->cloneRow('nama', max($peserta->count(), 1));

        if ($peserta->count() > 0) {
            foreach ($peserta as $index => $p) {
                $rowNumber = $index + 1;
                $template->setValue("no#$rowNumber", $rowNumber);
                $template->setValue("nama#$rowNumber", $p->nama);
                $template->setValue("jabatan#$rowNumber", $p->jabatan);
            }
        } else {
            // Jika kosong, isi baris pertama dengan placeholder kosong
            $template->setValue("no#1", "");
            $template->setValue("nama#1", "-");
            $template->setValue("jabatan#1", "-");
        }

        // --- Simpan ke temp file ---
        $safeFilename = str_replace(['/', '\\'], '_', $agenda->nomor_surat);
        $fileName = "Notulensi_Rapat_{$safeFilename}.docx";
        $tempPath = storage_path('app/' . $fileName);
        $template->saveAs($tempPath);

        return $tempPath;
    }

    /**
     * Helper untuk handle newline di Word
     */
    private function formatTextForWord(?string $text): string
    {
        if (!$text) return '-';
        
        // Escape XML special characters except for our manual line breaks
        $text = htmlspecialchars($text, ENT_XML1);
        
        return str_replace("\n", '</w:t><w:br/><w:t xml:space="preserve">', $text);
    }
}
