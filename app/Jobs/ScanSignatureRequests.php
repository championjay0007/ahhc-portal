<?php

namespace App\Jobs;

use App\Services\SignatureRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanSignatureRequests implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(private SignatureRequestService $signatureRequestService) {}

    public function handle(): void
    {
        try {
            Log::info('Starting signature request reminder scan');
            $results = $this->signatureRequestService->scanAndSendReminders();
            Log::info('Signature request scan finished', $results);
        } catch (\Throwable $e) {
            Log::error('Signature request scan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
