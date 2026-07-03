<?php

namespace App\Jobs;

use App\Services\ShiftNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendShiftReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(private ShiftNotificationService $notificationService) {}

    public function handle(): void
    {
        try {
            Log::info('Starting shift reminder scan');
            $results = $this->notificationService->scanAndSendReminders();
            Log::info('Shift reminders sent', $results);
        } catch (\Throwable $e) {
            Log::error('Shift reminder scan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
