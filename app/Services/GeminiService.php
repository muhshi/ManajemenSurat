<?php

namespace App\Services;

use App\Settings\SystemSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;
    protected string $model = 'gemini-2.5-flash'; // ✅ Update model

    public function __construct()
    {
        $this->apiKey = app(SystemSettings::class)->gemini_api_key ?? '';
    }

    public function extractMetadata(string $fullPath): ?array
    {
        if (empty($this->apiKey)) {
            Log::error('Gemini API Key not set');
            return null;
        }

        if (!file_exists($fullPath)) {
            Log::error('File not found', ['path' => $fullPath]);
            return null;
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
            $response = Http::timeout(30)->post(
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
                return null;
            }

            $text = $response->json('candidates.0.content.parts.0.text');

            if (!$text) {
                Log::error('Empty Gemini response');
                return null;
            }

            // Bersihkan markdown code block
            $text = preg_replace('/^```json\s*|\s*```$/', '', trim($text));

            $json = json_decode($text, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON decode error', [
                    'error' => json_last_error_msg(),
                    'raw' => $text,
                ]);
                return null;
            }

            return $json;

        } catch (\Throwable $e) {
            Log::error('Gemini Exception', [
                'message' => $e->getMessage(),
            ]);
        }

        return null;
    }
}