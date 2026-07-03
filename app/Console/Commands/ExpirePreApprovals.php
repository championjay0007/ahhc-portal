<?php

namespace App\Console\Commands;

use App\Models\PreApprovalRequest;
use App\Services\AuditLogService;
use App\Services\BudgetService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpirePreApprovals extends Command
{
    protected $signature = 'preapprovals:expire';

    protected $description = 'Expire approved pre-approvals when the service end date has passed and release committed budget.';

    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        parent::__construct();

        $this->budgetService = $budgetService;
    }

    public function handle(): int
    {
        $expiredRequests = PreApprovalRequest::where('status', 'approved')
            ->whereDate('end_date', '<', Carbon::today())
            ->get();

        foreach ($expiredRequests as $preApprovalRequest) {
            DB::transaction(function () use ($preApprovalRequest) {
                $this->budgetService->releasePreApproval($preApprovalRequest);
                $preApprovalRequest->update([
                    'status' => 'expired',
                ]);

                AuditLogService::record('Pre-Approval Expired', $preApprovalRequest, [], [
                    'request_number' => $preApprovalRequest->request_number,
                    'participant_id' => $preApprovalRequest->participant_id,
                    'expired_at' => now()->toDateTimeString(),
                ]);
            });

            $this->info(sprintf('Expired pre-approval %s', $preApprovalRequest->request_number));
        }

        return 0;
    }
}
