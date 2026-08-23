<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use App\Models\Sp2dUpload;
use App\Services\Sp2dImportService;

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

        try {
            $service = new Sp2dImportService($upload, function($msg) {
                $this->logMessage($msg);
            });
            
            $service->process();
            
            $upload->update([
                'status' => 'done',
            ]);
            
            $this->logMessage("✅ ====== IMPORT SP2D SELESAI ======");

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
