<?php

namespace App\Jobs;

use App\Services\CareReviewNotificationService;
use App\Services\CareReviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanCareReviews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(
        private CareReviewService $reviewService,
        private CareReviewNotificationService $notificationService
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Starting care review scan');

            // Update statuses (Due → Overdue)
            $statusResults = $this->reviewService->scanAndUpdateReviewStatuses();
            Log::info('Review status updates completed', $statusResults);

            // Send reminders
            $notificationResults = $this->notificationService->scanAndSendReminders();
            Log::info('Care review notifications sent', $notificationResults);
        } catch (\Exception $e) {
            Log::error('Care review scan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
