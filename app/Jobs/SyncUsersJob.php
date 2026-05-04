<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class SyncUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $full;

    /**
     * Create a new job instance.
     */
    public function __construct(bool $full = true)
    {
        $this->full = $full;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Artisan::call('sync:users', [
            '--full' => $this->full,
        ]);
    }
}
