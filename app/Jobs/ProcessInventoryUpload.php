<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\InventoryUpload;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Item;
use App\Models\Transaction;

class ProcessInventoryUpload implements ShouldQueue
{
    use Queueable;

    public $upload;
    public $timeout = 600;
    public $tries = 1;
    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(InventoryUpload $upload)
    {
        $this->upload = $upload;
    }

    private function logMessage(string $msg, bool $isError = false): void
    {
        $prefix = "[SEP-BP] ";
        if ($isError) {
            Log::error($prefix . $msg);
        } else {
            Log::info($prefix . $msg);
        }

        // Append to database column
        $currentLog = $this->upload->error_log ?? '';
        $timestamp = now()->format('H:i:s');
        $newLog = $currentLog . "[{$timestamp}] {$msg}\n";

        // Keep only last 10000 characters to avoid text overflow
        if (strlen($newLog) > 10000) {
            $newLog = substr($newLog, -10000);
        }

        $this->upload->error_log = $newLog;
        $this->upload->save();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $upload = $this->upload;

        // Reset the log when starting
        $upload->update(['error_log' => '', 'status' => 'processing']);

        $this->logMessage("====== MULAI PROSES UPLOAD #{$upload->id} ======");
        $this->logMessage("Filename: {$upload->filename}");

        $filePath = Storage::disk('public')->path($upload->filename);

        if (!file_exists($filePath)) {
            $this->logMessage("❌ File tidak ditemukan: {$filePath}", true);
            $upload->update(['status' => 'failed']);
            return;
        }

        $this->logMessage("✅ File ditemukan, ukuran: " . round(filesize($filePath) / 1024, 1) . " KB");

        try {
            // Check for Python venv path (Cross-platform)
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            $pythonPath = $isWindows 
                ? base_path('app/Scripts/venv/Scripts/python.exe')
                : base_path('app/Scripts/venv/bin/python3');

            $scriptPath = base_path('app/Scripts/parse_buku_persediaan.py');

            $this->logMessage("Python Path: {$pythonPath}");

            if (!file_exists($pythonPath)) {
                $setupCmd = $isWindows 
                    ? "python -m venv app/Scripts/venv && app/Scripts/venv/Scripts/pip install pdfplumber"
                    : "python3 -m venv app/Scripts/venv && ./app/Scripts/venv/bin/pip install pdfplumber";
                
                throw new Exception("Python venv tidak ditemukan. Silakan jalankan perintah berikut di terminal: {$setupCmd}");
            }

            $this->logMessage("🔄 Menjalankan Python script... (Ini mungkin memakan waktu)");
            $startTime = microtime(true);

            $result = Process::timeout(600)->run("{$pythonPath} {$scriptPath} '{$filePath}'");

            $elapsed = round(microtime(true) - $startTime, 2);
            $this->logMessage("Python selesai dalam {$elapsed} detik");

            if ($result->failed()) {
                $this->logMessage("❌ Python STDERR: " . substr($result->errorOutput(), 0, 500), true);
                throw new Exception("Python script failed.");
            }

            $this->logMessage("✅ Python berhasil, memproses JSON...");

            $output = json_decode($result->output(), true);
            if (!$output || $output['status'] !== 'success') {
                $this->logMessage("❌ JSON tidak valid.", true);
                throw new Exception("Invalid JSON output.");
            }

            $totalItems = count($output['items'] ?? []);
            $totalTx = count($output['transactions'] ?? []);
            $this->logMessage("📊 Parsing JSON: {$totalItems} items, {$totalTx} transaksi");

            $itemsCount = 0;
            $txCount = 0;

            DB::transaction(function () use ($output, $upload, &$itemsCount, &$txCount) {
                $this->logMessage("🔄 Menyimpan master items ke database...");

                $itemsToUpsert = [];
                foreach ($output['items'] as $itemData) {
                    $itemsToUpsert[] = [
                        'item_code' => $itemData['item_code'],
                        'item_name' => $itemData['item_name'] ?? '',
                        'satuan' => $itemData['satuan'] ?? '',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($itemsToUpsert)) {
                    foreach (array_chunk($itemsToUpsert, 500) as $chunk) {
                        Item::upsert($chunk, ['item_code'], ['item_name', 'satuan', 'updated_at']);
                        $itemsCount += count($chunk);
                    }
                }

                $this->logMessage("✅ {$itemsCount} items disimpan/diupdate secara massal");
                $this->logMessage("🔄 Menyimpan baris transaksi ke database...");

                $txToInsert = [];
                $itemIds = Item::pluck('id', 'item_code')->toArray();
                $dates = [];

                foreach ($output['transactions'] as $txData) {
                    if (isset($itemIds[$txData['item_code']])) {
                        $itemId = $itemIds[$txData['item_code']];
                        $tanggal = $txData['tanggal'];
                        $noDok = str_replace(["\n", "\r"], '', $txData['no_dok'] ?? '');
                        $keterangan = $txData['keterangan'] ?? '';

                        // Create unique fingerprint for this transaction
                        $hashString = "{$itemId}|{$tanggal}|{$noDok}|{$keterangan}";
                        $txHash = hash('sha256', $hashString);

                        $txToInsert[] = [
                            'tx_hash' => $txHash,
                            'item_id' => $itemId,
                            'inventory_upload_id' => $upload->id,
                            'tanggal' => $tanggal,
                            'keterangan' => $keterangan,
                            'no_dok' => $noDok,
                            'masuk_unit' => $txData['masuk_unit'],
                            'masuk_harga' => $txData['masuk_harga'],
                            'masuk_jumlah' => $txData['masuk_jumlah'],
                            'keluar_unit' => $txData['keluar_unit'],
                            'keluar_harga' => $txData['keluar_harga'],
                            'keluar_jumlah' => $txData['keluar_jumlah'],
                            'saldo_unit' => $txData['saldo_unit'],
                            'saldo_harga' => $txData['saldo_harga'],
                            'saldo_jumlah' => $txData['saldo_jumlah'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if ($tanggal) {
                            $dates[] = $tanggal;
                        }
                    }
                }

                if (!empty($txToInsert)) {
                    $chunks = array_chunk($txToInsert, 500);
                    foreach ($chunks as $i => $chunk) {
                        Transaction::upsert($chunk, ['tx_hash'], [
                            'inventory_upload_id', 'masuk_unit', 'masuk_harga', 'masuk_jumlah', 
                            'keluar_unit', 'keluar_harga', 'keluar_jumlah', 
                            'saldo_unit', 'saldo_harga', 'saldo_jumlah', 'updated_at'
                        ]);
                        $txCount += count($chunk);
                        $this->logMessage("📥 Chunk " . ($i + 1) . "/" . count($chunks) . " di-insert / di-upsert...");
                    }
                }

                if (!empty($dates)) {
                    $upload->update([
                        'period_start' => min($dates),
                        'period_end' => max($dates),
                    ]);
                }
            });

            $upload->update([
                'status' => 'done',
                'rows_extracted' => $txCount,
                'processed_at' => now(),
            ]);

            $this->logMessage("✅ ====== UPLOAD SELESAI ======");

        } catch (Exception $e) {
            $this->logMessage("❌ GAGAL: " . $e->getMessage(), true);
            $upload->update(['status' => 'failed']);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $error = $exception ? $exception->getMessage() : 'Unknown error';
        $this->logMessage("💀 JOB FAILED FATALLY: " . $error, true);
        $this->upload->update(['status' => 'failed']);
    }
}
