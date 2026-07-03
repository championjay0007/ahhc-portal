<?php

namespace App\Services;

use App\Enums\ComplianceDocumentType;
use App\Enums\ComplianceStatus;
use App\Models\Worker;
use App\Models\WorkerComplianceAlert;
use App\Models\WorkerComplianceDocument;

class ComplianceDashboardService
{
    public function __construct(private ComplianceService $complianceService) {}

    /**
     * Get all dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'total_workers' => $this->getTotalWorkers(),
            'expiring_soon' => $this->getExpiringDocuments(),
            'expired' => $this->getExpiredDocuments(),
            'missing' => $this->getMissingDocuments(),
            'rejected' => $this->getRejectedDocuments(),
            'workers_with_issues' => $this->getWorkersWithIssues(),
            'alerts' => $this->getAlertCount(),
            'compliance_score' => $this->getComplianceScore(),
            'recent_expirations' => $this->getRecentExpirations(),
        ];
    }

    public function getAlertCount(): int
    {
        return WorkerComplianceAlert::count();
    }

    /**
     * Get total number of workers
     */
    public function getTotalWorkers(): int
    {
        return Worker::count();
    }

    /**
     * Get documents expiring within 30 days
     */
    public function getExpiringDocuments(): array
    {
        $documents = $this->complianceService->getExpiringDocuments();

        return [
            'count' => $documents->count(),
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'worker_id' => $doc->worker_id,
                    'worker_name' => $doc->worker->first_name.' '.$doc->worker->last_name,
                    'document_type' => $doc->document_type,
                    'expiry_date' => $doc->expiry_date->toDateString(),
                    'days_remaining' => $doc->daysUntilExpiry(),
                    'status' => $doc->status,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get expired documents
     */
    public function getExpiredDocuments(): array
    {
        $documents = $this->complianceService->getExpiredDocuments();

        return [
            'count' => $documents->count(),
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'worker_id' => $doc->worker_id,
                    'worker_name' => $doc->worker->first_name.' '.$doc->worker->last_name,
                    'document_type' => $doc->document_type,
                    'expiry_date' => $doc->expiry_date->toDateString(),
                    'days_overdue' => $doc->daysUntilExpiry(),
                    'status' => $doc->status,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get missing documents
     */
    public function getMissingDocuments(): array
    {
        $documents = $this->complianceService->getMissingDocuments();

        return [
            'count' => $documents->count(),
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'worker_id' => $doc->worker_id,
                    'worker_name' => $doc->worker->first_name.' '.$doc->worker->last_name,
                    'document_type' => $doc->document_type,
                    'status' => $doc->status,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get rejected documents
     */
    public function getRejectedDocuments(): array
    {
        $documents = WorkerComplianceDocument::where('status', ComplianceStatus::REJECTED->value)
            ->with('worker')
            ->get();

        return [
            'count' => $documents->count(),
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'worker_id' => $doc->worker_id,
                    'worker_name' => $doc->worker->first_name.' '.$doc->worker->last_name,
                    'document_type' => $doc->document_type,
                    'rejection_reason' => $doc->rejection_reason,
                    'status' => $doc->status,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get workers with compliance issues
     */
    public function getWorkersWithIssues(): array
    {
        $workers = Worker::whereHas('complianceDocuments', function ($query) {
            $query->whereIn('status', [
                ComplianceStatus::EXPIRED->value,
                ComplianceStatus::EXPIRING_SOON->value,
                ComplianceStatus::MISSING->value,
            ]);
        })->with('complianceDocuments')->get();

        return [
            'count' => $workers->count(),
            'workers' => $workers->map(function ($worker) {
                return [
                    'id' => $worker->id,
                    'name' => $worker->first_name.' '.$worker->last_name,
                    'worker_number' => $worker->worker_number,
                    'issues' => $worker->complianceDocuments
                        ->whereIn('status', [
                            ComplianceStatus::EXPIRED->value,
                            ComplianceStatus::EXPIRING_SOON->value,
                            ComplianceStatus::MISSING->value,
                        ])
                        ->map(fn ($doc) => [
                            'type' => $doc->document_type,
                            'status' => $doc->status,
                        ])
                        ->values()
                        ->toArray(),
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Calculate overall compliance score (0-100)
     */
    public function getComplianceScore(): int
    {
        $totalWorkers = $this->getTotalWorkers();

        if ($totalWorkers === 0) {
            return 100;
        }

        $workersWithIssues = $this->getWorkersWithIssues()['count'];
        $compliantWorkers = $totalWorkers - $workersWithIssues;

        return (int) (($compliantWorkers / $totalWorkers) * 100);
    }

    /**
     * Get recent expiration history (last 30 days)
     */
    public function getRecentExpirations(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        $documents = WorkerComplianceDocument::where('status', ComplianceStatus::EXPIRED->value)
            ->where('expiry_date', '>=', $thirtyDaysAgo)
            ->with('worker')
            ->orderByDesc('expiry_date')
            ->get();

        return [
            'count' => $documents->count(),
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'worker_name' => $doc->worker->first_name.' '.$doc->worker->last_name,
                    'document_type' => $doc->document_type,
                    'expiry_date' => $doc->expiry_date->toDateString(),
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Get compliance documents by type summary
     */
    public function getComplianceByDocumentType(): array
    {
        $documentTypes = ComplianceDocumentType::cases();
        $summary = [];

        foreach ($documentTypes as $type) {
            $documents = WorkerComplianceDocument::where('document_type', $type->value)->get();

            $summary[] = [
                'type' => $type->value,
                'total' => $documents->count(),
                'active' => $documents->where('status', ComplianceStatus::ACTIVE->value)->count(),
                'expiring_soon' => $documents->where('status', ComplianceStatus::EXPIRING_SOON->value)->count(),
                'expired' => $documents->where('status', ComplianceStatus::EXPIRED->value)->count(),
                'missing' => $documents->where('status', ComplianceStatus::MISSING->value)->count(),
                'rejected' => $documents->where('status', ComplianceStatus::REJECTED->value)->count(),
            ];
        }

        return $summary;
    }

    /**
     * Get worker compliance details
     */
    public function getWorkerComplianceDetails(Worker $worker): array
    {
        $documents = $worker->complianceDocuments;

        return [
            'worker_id' => $worker->id,
            'worker_name' => $worker->first_name.' '.$worker->last_name,
            'worker_number' => $worker->worker_number,
            'total_documents' => $documents->count(),
            'active' => $documents->where('status', ComplianceStatus::ACTIVE->value)->count(),
            'expiring_soon' => $documents->where('status', ComplianceStatus::EXPIRING_SOON->value)->count(),
            'expired' => $documents->where('status', ComplianceStatus::EXPIRED->value)->count(),
            'missing' => $documents->where('status', ComplianceStatus::MISSING->value)->count(),
            'rejected' => $documents->where('status', ComplianceStatus::REJECTED->value)->count(),
            'can_be_assigned' => $this->complianceService->canWorkerBeAssigned($worker),
            'blocking_reason' => $this->complianceService->getAssignmentBlockingReason($worker),
            'documents' => $documents->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'type' => $doc->document_type,
                    'status' => $doc->status,
                    'issue_date' => $doc->issue_date?->toDateString(),
                    'expiry_date' => $doc->expiry_date?->toDateString(),
                    'days_remaining' => $doc->daysUntilExpiry(),
                    'verified_at' => $doc->verified_at?->toDateTimeString(),
                    'rejected_reason' => $doc->rejection_reason,
                ];
            })->values()->toArray(),
        ];
    }
}
