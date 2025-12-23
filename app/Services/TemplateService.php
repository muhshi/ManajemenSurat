<?php

namespace App\Services;

use App\Models\Surat;
use App\Settings\SystemSettings;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;

class TemplateService
{
    /**
     * Generate surat from template
     */
    public function generateSurat(Surat $surat): string
    {
        $settings = app(SystemSettings::class);

        // Check if template exists
        if (!$settings->template_sk) {
            throw new \Exception('Template SK belum diupload. Silakan upload template di halaman Pengaturan Kantor.');
        }

        $templatePath = Storage::disk('public')->path($settings->template_sk);

        if (!file_exists($templatePath)) {
            throw new \Exception('File template tidak ditemukan.');
        }

        // Load template
        $templateProcessor = new TemplateProcessor($templatePath);

        // Replace placeholders
        $templateProcessor->setValue('nomor_surat', $surat->nomor_surat);
        $templateProcessor->setValue('judul_surat', $surat->judul_surat);

        // Jenis mapping: Pelatihan -> Peserta, Pelaksanaan -> Petugas
        $jenisWord = $surat->perihal === 'Peserta' ? 'Peserta' : 'Petugas';
        $templateProcessor->setValue('jenis', $jenisWord);

        $templateProcessor->setValue('tanggal_surat', \Illuminate\Support\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y'));
        $templateProcessor->setValue('kepala', $settings->cert_signer_name);
        $templateProcessor->setValue('tahun', $surat->tahun);


        // Generate filename
        $filename = str_replace(['/', ' '], ['-', '_'], $surat->nomor_surat) . '.docx';
        $outputPath = 'generated/' . $filename;
        $fullOutputPath = Storage::disk('local')->path($outputPath);

        // Create directory if not exists
        $directory = dirname($fullOutputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Save generated document
        $templateProcessor->saveAs($fullOutputPath);

        return $outputPath;
    }

    public function downloadSurat(Surat $surat)
    {
        if (!$surat->file_path || !Storage::disk('local')->exists($surat->file_path)) {
            throw new \Exception('File surat tidak ditemukan.');
        }

        $filename = str_replace(['/', ' '], ['-', '_'], $surat->nomor_surat) . '.docx';
        $path = Storage::disk('local')->path($surat->file_path);

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }
}
