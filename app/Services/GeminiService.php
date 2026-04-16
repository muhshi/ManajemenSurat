<?php

namespace App\Services;

use Throwable;
use App\Settings\SystemSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model = 'gemini-2.5-flash'; // ✅ Update model

    public function __construct()
    {
        $this->apiKey = app(SystemSettings::class)->gemini_api_key ?: env('GEMINI_API_KEY', '');
    }

    /**
     * @throws \Exception
     */
    public function extractMetadata(string $fullPath): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API Key not set');
            throw new \Exception('Gemini API Key belum dikonfigurasi. Silakan cek file .env atau menu Pengaturan.');
        }

        if (!file_exists($fullPath)) {
            Log::error('File not found', ['path' => $fullPath]);
            throw new \Exception('File tidak ditemukan di sistem.');
        }

        $mimeType = mime_content_type($fullPath) ?: 'application/pdf';
        $fileContent = base64_encode(file_get_contents($fullPath));

        $prompt = <<<PROMPT
Anda adalah asisten OCR surat masuk instansi pemerintah.

Kembalikan JSON VALID SAJA dengan field:
- nama_pengirim
- jabatan_pengirim
- instansi_pengirim
- nomor_surat
- tanggal_surat (YYYY-MM-DD)
- perihal
- isi_ringkas (maks 2 kalimat)

JANGAN beri teks tambahan di luar JSON.
PROMPT;

        try {
            $response = Http::timeout(60)->post(
                "https://generativelanguage.googleapis.com/v1/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inlineData' => [
                                        'mimeType' => $mimeType,
                                        'data' => $fileContent,
                                    ],
                                ],
                            ],
                        ]
                    ],
                ]
            );

            if (!$response->successful()) {
                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                if ($response->status() === 429) {
                    throw new \Exception('Kuota API Gemini telah habis atau limit tercapai (Rate Limit). Silakan coba lagi nanti.');
                }

                if ($response->status() === 400 && str_contains($response->body(), 'API_KEY_INVALID')) {
                    throw new \Exception('API Key Gemini tidak valid. Silakan periksa kembali konfigurasi Anda.');
                }

                $errorMessage = $response->json('error.message') ?? 'Terjadi kesalahan sistem pada API Gemini.';
                throw new \Exception("Gemini API Error: {$errorMessage}");
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (!$text) {
                Log::error('Empty Gemini response');
                throw new \Exception('Gagal mendapatkan respon teks dari AI.');
            }

            // Bersihkan markdown code block
            $text = preg_replace('/^```json\s*|\s*```$/', '', trim($text));

            $json = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'error' => json_last_error_msg(),
                    'raw' => $text,
                ]);
                throw new \Exception('Format data yang dikirim AI tidak valid.');
            }

            return $json;

        } catch (\Exception $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Gemini Exception', [
                'message' => $e->getMessage(),
            ]);
            throw new \Exception('Terjadi kegagalan koneksi atau sistem saat memproses AI.');
        }
    }
}