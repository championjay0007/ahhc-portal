<?php

namespace App\Models;

use App\Enums\WorkerNominationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerNomination extends Model
{
    protected $fillable = [
        'participant_id',
        'worker_full_name',
        'worker_email',
        'worker_phone',
        'worker_address',
        'worker_type',
        'service_type',
        'estimated_hours',
        'estimated_cost',
        'start_date',
        'notes',
        'status',
        'ahhc_admin_notes',
        'rejection_reason',
        'uploaded_documents',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'invited_by',
        'invited_at',
    ];

    protected $casts = [
        'status' => WorkerNominationStatus::class,
        'estimated_hours' => 'decimal:2',
        'estimated_cost' => 'decimal:2',
        'start_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'invited_at' => 'datetime',
        'uploaded_documents' => 'array',
    ];

    /**
     * Relationships
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Status Management
     */
    public function updateStatus(WorkerNominationStatus $newStatus, ?string $notes = null, ?int $userId = null): bool
    {
        if (! $this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $updates = ['status' => $newStatus->value];

        if ($notes && in_array($newStatus, [WorkerNominationStatus::Rejected])) {
            $updates['rejection_reason'] = $notes;
        } elseif ($notes) {
            $updates['ahhc_admin_notes'] = $notes;
        }

        if ($userId) {
            match ($newStatus) {
                WorkerNominationStatus::Approved => $updates = array_merge($updates, ['approved_by' => $userId, 'approved_at' => now()]),
                WorkerNominationStatus::Rejected => $updates = array_merge($updates, ['rejected_by' => $userId, 'rejected_at' => now()]),
                WorkerNominationStatus::WorkerInvited => $updates = array_merge($updates, ['invited_by' => $userId, 'invited_at' => now()]),
                default => null,
            };
        }

        return $this->update($updates);
    }

    /**
     * Query Scopes
     */
    public function scopeByParticipant($query, Participant $participant)
    {
        return $query->where('participant_id', $participant->id);
    }

    public function scopeByStatus($query, WorkerNominationStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            WorkerNominationStatus::Submitted,
            WorkerNominationStatus::UnderReview,
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', WorkerNominationStatus::Approved);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', WorkerNominationStatus::Rejected);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            WorkerNominationStatus::Active,
            WorkerNominationStatus::Assigned,
        ]);
    }

    /**
     * Helpers
     */
    public function isPending(): bool
    {
        return in_array($this->status, [
            WorkerNominationStatus::Submitted,
            WorkerNominationStatus::UnderReview,
            WorkerNominationStatus::Approved,
            WorkerNominationStatus::WorkerInvited,
            WorkerNominationStatus::CompliancePending,
            WorkerNominationStatus::PendingSignature,
        ]);
    }

    public function isApproved(): bool
    {
        return $this->status === WorkerNominationStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === WorkerNominationStatus::Rejected;
    }

    public function isActive(): bool
    {
        return in_array($this->status, [
            WorkerNominationStatus::Active,
            WorkerNominationStatus::Assigned,
        ]);
    }

    public function canBeApproved(): bool
    {
        return in_array($this->status, [
            WorkerNominationStatus::Submitted,
            WorkerNominationStatus::UnderReview,
        ]);
    }

    public function canBeRejected(): bool
    {
        return ! $this->isRejected() && $this->isPending();
    }

    public function canSendInvitation(): bool
    {
        return $this->status === WorkerNominationStatus::Approved;
    }
}
