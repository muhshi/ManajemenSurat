<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\Sp2dUpload;
use App\Imports\Sp2dImport;
use Maatwebsite\Excel\Facades\Excel;

class ProcessSp2dImport implements ShouldQueue
{
    use Queueable;

    public $upload;
    public $timeout = 600;
    public $tries = 1;
    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(Sp2dUpload $upload)
    {
        $this->upload = $upload;
    }

    private function logMessage(string $msg, bool $isError = false): void
    {
        $prefix = "[SP2D] ";
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

        $this->logMessage("====== MULAI PROSES IMPORT SP2D #{$upload->id} ======");
        $this->logMessage("Filename: {$upload->filename}");

        $filePath = Storage::disk('public')->path($upload->filename);

        if (!file_exists($filePath)) {
            $this->logMessage("❌ File tidak ditemukan di server: {$filePath}", true);
            $upload->update(['status' => 'failed']);
            return;
        }

        $this->logMessage("✅ File ditemukan. Ukuran: " . round(filesize($filePath) / 1024, 1) . " KB");

        try {
            $this->logMessage("🔄 Mulai membaca Excel...");
            
            // Lakukan import data. Sp2dImport sudah punya logic untuk deteksi periode dan insert ke db.
            // Sp2dImport akan mencari upload->id di databasenya dan otomatis melakukan update status 'done'.
            // Namun kita tangani log di sini.
            Excel::import(new Sp2dImport($upload->id), $filePath);
            
            // Jika proses Sp2dImport selesai dengan normal, maka seharusnya tabel sp2d_uploads 
            // sudah terupdate (oleh collection). Tapi kita double check di sini:
            $upload->refresh();
            if ($upload->status !== 'done') {
                $upload->update([
                    'status' => 'done',
                ]);
            }
            
            $this->logMessage("✅ ====== IMPORT SP2D SELESAI ======");

        } catch (Exception $e) {
            $this->logMessage("❌ GAGAL: " . $e->getMessage(), true);
            $upload->update(['status' => 'failed']);
        }
    }

    /**
     * Optional helper to log from within the Import class if needed.
     */
    public function addLog(string $msg): void
    {
        $this->logMessage($msg);
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
