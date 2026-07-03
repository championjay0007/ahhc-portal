<?php

namespace App\Services;

use App\Enums\ComplianceDocumentType;
use App\Enums\ComplianceStatus;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Models\WorkerComplianceType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class ComplianceService
{
    public const EXPIRING_SOON_DAYS = 30;

    public function __construct(private ComplianceNotificationService $notificationService) {}

    /**
     * Create or update a compliance document
     */
    public function createOrUpdateDocument(
        Worker $worker,
        ComplianceDocumentType $documentType,
        ?string $documentPath = null,
        ?Carbon $issueDate = null,
        ?Carbon $expiryDate = null,
        ?string $notes = null
    ): WorkerComplianceDocument {
        $typeRecord = WorkerComplianceType::where('name', $documentType->value)->first();

        $document = WorkerComplianceDocument::updateOrCreate(
            [
                'worker_id' => $worker->id,
                'document_type' => $documentType->value,
            ],
            [
                'worker_compliance_type_id' => $typeRecord?->id,
                'document_path' => $documentPath,
                'issue_date' => $issueDate,
                'expiry_date' => $expiryDate,
                'notes' => $notes,
            ]
        );

        // Update status based on dates
        $this->updateDocumentStatus($document);

        return $document->fresh();
    }

    /**
     * Update document status based on expiry date
     */
    public function updateDocumentStatus(WorkerComplianceDocument $document): void
    {
        if ($document->status === ComplianceStatus::REJECTED->value) {
            return; // Don't change rejected status automatically
        }

        if ($document->isExpired()) {
            $document->update(['status' => ComplianceStatus::EXPIRED->value]);
        } elseif ($document->isExpiringSoon()) {
            $document->update(['status' => ComplianceStatus::EXPIRING_SOON->value]);
        } elseif ($document->expiry_date || $document->issue_date) {
            $document->update(['status' => ComplianceStatus::ACTIVE->value]);
        } else {
            $document->update(['status' => ComplianceStatus::MISSING->value]);
        }
    }

    public function getComplianceTypes(): array
    {
        WorkerComplianceType::ensureDefaults();

        return WorkerComplianceType::options();
    }

    public function getWorkerAlerts(Worker $worker)
    {
        return $worker->complianceAlerts()->latest('sent_at')->get();
    }

    /**
     * Scan all workers for compliance status changes
     */
    public function scanAllWorkerCompliance(): array
    {
        $results = [
            'processed' => 0,
            'expiring_soon' => 0,
            'expired' => 0,
            'missing' => 0,
            'workers_affected' => [],
        ];

        Worker::chunk(100, function ($workers) use (&$results) {
            foreach ($workers as $worker) {
                $changes = $this->scanWorkerCompliance($worker);
                if ($changes['status_changed']) {
                    $results['processed']++;
                    $results['expiring_soon'] += $changes['expiring_soon'];
                    $results['expired'] += $changes['expired'];
                    $results['missing'] += $changes['missing'];
                    $results['workers_affected'][] = $worker->id;
                }
            }
        });

        return $results;
    }

    /**
     * Scan a specific worker's compliance documents
     */
    public function scanWorkerCompliance(Worker $worker): array
    {
        $results = [
            'worker_id' => $worker->id,
            'status_changed' => false,
            'expiring_soon' => 0,
            'expired' => 0,
            'missing' => 0,
        ];

        $documents = $worker->complianceDocuments;

        foreach ($documents as $document) {
            $oldStatus = $document->status;
            $this->updateDocumentStatus($document);
            $newStatus = $document->status;

            if ($oldStatus !== $newStatus) {
                $results['status_changed'] = true;

                if ($newStatus === ComplianceStatus::EXPIRING_SOON->value) {
                    $results['expiring_soon']++;
                } elseif ($newStatus === ComplianceStatus::EXPIRED->value) {
                    $results['expired']++;
                } elseif ($newStatus === ComplianceStatus::MISSING->value) {
                    $results['missing']++;
                }
            }

            $this->notificationService->checkAndSendReminders($document);
        }

        $missingDocuments = $worker->complianceDocuments()
            ->where('status', ComplianceStatus::MISSING->value)
            ->pluck('document_type')
            ->toArray();

        if (! empty($missingDocuments) && ! $this->notificationService->hasRecentWorkerAlert($worker, 'missing_documents')) {
            $this->notificationService->sendMissingDocumentsNotification($worker, $missingDocuments);
        }

        $this->updateWorkerAutoSuspension($worker);

        return $results;
    }

    protected function updateWorkerAutoSuspension(Worker $worker): void
    {
        $issues = $this->getWorkerCriticalIssues($worker);
        $message = $issues->map(fn ($doc) => "{$doc->document_type} ({$doc->status})")->implode(', ');

        if ($issues->isNotEmpty()) {
            $worker->update([
                'compliance_suspended_at' => now(),
                'compliance_suspension_reason' => "Worker has critical compliance issues: {$message}",
                'status' => $worker->status === 'active' ? 'inactive' : $worker->status,
            ]);

            return;
        }

        if ($worker->compliance_suspended_at && $worker->status === 'inactive') {
            $worker->update([
                'compliance_suspended_at' => null,
                'compliance_suspension_reason' => null,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Get all expiring documents within next 30 days
     */
    public function getExpiringDocuments(int $days = self::EXPIRING_SOON_DAYS): Collection
    {
        $targetDate = now()->addDays($days);

        return WorkerComplianceDocument::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->toDateString(), $targetDate->toDateString()])
            ->where('status', '!=', ComplianceStatus::EXPIRED->value)
            ->where('status', '!=', ComplianceStatus::MISSING->value)
            ->where('status', '!=', ComplianceStatus::REJECTED->value)
            ->with('worker')
            ->get();
    }

    /**
     * Get expired documents
     */
    public function getExpiredDocuments(): Collection
    {
        return WorkerComplianceDocument::where('status', ComplianceStatus::EXPIRED->value)
            ->with('worker')
            ->get();
    }

    /**
     * Get missing documents
     */
    public function getMissingDocuments(): Collection
    {
        return WorkerComplianceDocument::where('status', ComplianceStatus::MISSING->value)
            ->with('worker')
            ->get();
    }

    /**
     * Get critical documents that are not active for a worker
     */
    public function getWorkerCriticalIssues(Worker $worker): Collection
    {
        $criticalTypes = [
            ComplianceDocumentType::POLICE_CHECK->value,
            ComplianceDocumentType::NDIS_WORKER_SCREENING->value,
            ComplianceDocumentType::INSURANCE->value,
        ];

        return $worker->complianceDocuments()
            ->whereIn('document_type', $criticalTypes)
            ->where('status', '!=', ComplianceStatus::ACTIVE->value)
            ->get();
    }

    /**
     * Check if a worker can be assigned (all critical docs active)
     */
    public function canWorkerBeAssigned(Worker $worker): bool
    {
        return $this->getWorkerCriticalIssues($worker)->isEmpty();
    }

    /**
     * Get assignment blocking reason if worker has issues
     */
    public function getAssignmentBlockingReason(Worker $worker): ?string
    {
        $issues = $this->getWorkerCriticalIssues($worker);

        if ($issues->isEmpty()) {
            return null;
        }

        $issuesList = $issues->map(function ($doc) {
            return "{$doc->document_type} ({$doc->status})";
        })->implode(', ');

        return "Worker has compliance issues: {$issuesList}";
    }

    /**
     * Get compliance dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'expiring_soon' => WorkerComplianceDocument::where('status', ComplianceStatus::EXPIRING_SOON->value)->count(),
            'expired' => WorkerComplianceDocument::where('status', ComplianceStatus::EXPIRED->value)->count(),
            'missing' => WorkerComplianceDocument::where('status', ComplianceStatus::MISSING->value)->count(),
            'rejected' => WorkerComplianceDocument::where('status', ComplianceStatus::REJECTED->value)->count(),
            'workers_with_issues' => Worker::whereHas('complianceDocuments', function ($query) {
                $query->whereIn('status', [
                    ComplianceStatus::EXPIRED->value,
                    ComplianceStatus::EXPIRING_SOON->value,
                    ComplianceStatus::MISSING->value,
                ]);
            })->count(),
        ];
    }

    /**
     * Get expiring documents for a specific number of days
     */
    public function getDocumentsExpiringInDays(int $days): Collection
    {
        $targetDate = now()->addDays($days);

        return WorkerComplianceDocument::whereNotNull('expiry_date')
            ->whereDate('expiry_date', $targetDate->toDateString())
            ->where('status', '!=', ComplianceStatus::EXPIRED->value)
            ->where('status', '!=', ComplianceStatus::REJECTED->value)
            ->with('worker')
            ->get();
    }

    /**
     * Delete a compliance document and its file
     */
    public function deleteDocument(WorkerComplianceDocument $document): bool
    {
        if ($document->document_path && Storage::disk('private')->exists($document->document_path)) {
            Storage::disk('private')->delete($document->document_path);
        }

        return $document->delete();
    }

    /**
     * Initialize required compliance documents for a worker
     */
    public function initializeWorkerCompliance(Worker $worker): void
    {
        $requiredDocuments = [
            ComplianceDocumentType::POLICE_CHECK,
            ComplianceDocumentType::NDIS_WORKER_SCREENING,
            ComplianceDocumentType::INSURANCE,
            ComplianceDocumentType::FIRST_AID_CERTIFICATE,
            ComplianceDocumentType::CPR_CERTIFICATE,
            ComplianceDocumentType::QUALIFICATION,
            ComplianceDocumentType::REGISTRATION,
            ComplianceDocumentType::ABN_VERIFICATION,
            ComplianceDocumentType::MARKETPLACE_AGREEMENT,
        ];

        foreach ($requiredDocuments as $documentType) {
            WorkerComplianceDocument::firstOrCreate(
                [
                    'worker_id' => $worker->id,
                    'document_type' => $documentType->value,
                ],
                ['status' => ComplianceStatus::MISSING->value]
            );
        }
    }

    /**
     * Get workers needing compliance attention
     */
    public function getWorkersNeedingAttention(): Collection
    {
        return Worker::whereHas('complianceDocuments', function ($query) {
            $query->where('status', ComplianceStatus::MISSING->value)
                ->orWhere('status', ComplianceStatus::EXPIRED->value)
                ->orWhere('status', ComplianceStatus::EXPIRING_SOON->value);
        })->with('complianceDocuments')->get();
    }
}
