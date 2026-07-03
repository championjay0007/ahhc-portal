<?php

namespace App\Models;

use App\Enums\ComplianceDocumentType;
use App\Enums\ComplianceStatus;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerComplianceDocument extends Model
{
    protected $fillable = [
        'worker_id',
        'worker_compliance_type_id',
        'document_type',
        'document_path',
        'issue_date',
        'expiry_date',
        'status',
        'notes',
        'last_notified_at',
        'verified_by_id',
        'verified_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'last_notified_at' => 'datetime',
        'verified_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function complianceType(): BelongsTo
    {
        return $this->belongsTo(WorkerComplianceType::class, 'worker_compliance_type_id');
    }

    public function getTypeLabel(): string
    {
        return $this->complianceType?->name ?? $this->document_type;
    }

    /**
     * Get the document type enum
     */
    public function getDocumentTypeEnum(): ComplianceDocumentType
    {
        return ComplianceDocumentType::from($this->document_type);
    }

    /**
     * Get the status enum
     */
    public function getStatusEnum(): ComplianceStatus
    {
        return ComplianceStatus::from($this->status);
    }

    /**
     * Check if document is active
     */
    public function isActive(): bool
    {
        return $this->status === ComplianceStatus::ACTIVE->value;
    }

    /**
     * Check if document is expiring soon (within 30 days)
     */
    public function isExpiringSoon(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        $daysUntilExpiry = now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);

        return $daysUntilExpiry > 0 && $daysUntilExpiry <= 30;
    }

    /**
     * Check if document has expired
     */
    public function isExpired(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return now()->startOfDay()->gt($this->expiry_date->startOfDay());
    }

    /**
     * Check if document is missing
     */
    public function isMissing(): bool
    {
        return $this->status === ComplianceStatus::MISSING->value;
    }

    /**
     * Check if document is critical (blocks assignments)
     */
    public function isCritical(): bool
    {
        return $this->getDocumentTypeEnum()->isCritical();
    }

    /**
     * Get days until expiry
     */
    public function daysUntilExpiry(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    /**
     * Mark as verified
     */
    public function markAsVerified(User $verifiedBy): void
    {
        $this->update([
            'status' => ComplianceStatus::ACTIVE->value,
            'verified_by_id' => $verifiedBy->id,
            'verified_at' => now(),
        ]);

        AuditLogService::record('Compliance Document Verified', $this, [], [
            'worker_id' => $this->worker_id,
            'document_type' => $this->document_type,
            'verified_by_id' => $verifiedBy->id,
        ]);
    }

    /**
     * Mark as rejected
     */
    public function markAsRejected(User $rejectedBy, string $reason): void
    {
        $this->update([
            'status' => ComplianceStatus::REJECTED->value,
            'verified_by_id' => $rejectedBy->id,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        AuditLogService::record('Compliance Document Rejected', $this, [], [
            'worker_id' => $this->worker_id,
            'document_type' => $this->document_type,
            'rejected_by_id' => $rejectedBy->id,
            'reason' => $reason,
        ]);
    }

    /**
     * Scope queries to active documents
     */
    public function scopeActive($query)
    {
        return $query->where('status', ComplianceStatus::ACTIVE->value);
    }

    /**
     * Scope queries to expiring soon documents
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('status', ComplianceStatus::EXPIRING_SOON->value);
    }

    /**
     * Scope queries to expired documents
     */
    public function scopeExpired($query)
    {
        return $query->where('status', ComplianceStatus::EXPIRED->value);
    }

    /**
     * Scope queries to missing documents
     */
    public function scopeMissing($query)
    {
        return $query->where('status', ComplianceStatus::MISSING->value);
    }

    /**
     * Scope queries to critical documents
     */
    public function scopeCritical($query)
    {
        $criticalTypes = [
            ComplianceDocumentType::POLICE_CHECK->value,
            ComplianceDocumentType::NDIS_WORKER_SCREENING->value,
            ComplianceDocumentType::INSURANCE->value,
        ];

        return $query->whereIn('document_type', $criticalTypes);
    }

    /**
     * Scope queries to documents for a specific worker
     */
    public function scopeForWorker($query, $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    /**
     * Scope queries to documents by type
     */
    public function scopeOfType($query, $documentType)
    {
        return $query->where('document_type', $documentType);
    }
}
