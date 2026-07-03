<?php

namespace App\Jobs;

use App\Services\ComplianceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ScanWorkerCompliance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public int $tries = 3;

    public function __construct(
        private ComplianceService $complianceService
    ) {}

    public function handle(): void
    {
        try {
            Log::info('Starting daily compliance scan');

            $results = $this->complianceService->scanAllWorkerCompliance();

            Log::info('Compliance scan completed', $results);
        } catch (\Exception $e) {
            Log::error('Compliance scan failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
