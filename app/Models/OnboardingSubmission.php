<?php

namespace App\Models;

use App\Enums\OnboardingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingSubmission extends Model
{
    protected $table = 'onboarding_submissions';

    protected $fillable = [
        'participant_id',
        'personal_data',
        'support_person_data',
        'uploaded_documents',
        'signed_agreements',
        'status',
        'admin_comments',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'personal_data' => 'array',
        'support_person_data' => 'array',
        'uploaded_documents' => 'array',
        'signed_agreements' => 'array',
        'status' => OnboardingStatus::class,
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Participant who submitted
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Admin who reviewed
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if pending review
     */
    public function isPendingReview(): bool
    {
        return $this->status === OnboardingStatus::PENDING_REVIEW;
    }

    /**
     * Check if approved
     */
    public function isApproved(): bool
    {
        return $this->status === OnboardingStatus::APPROVED;
    }

    /**
     * Check if rejected
     */
    public function isRejected(): bool
    {
        return $this->status === OnboardingStatus::REJECTED;
    }

    /**
     * Check if changes requested
     */
    public function changesRequested(): bool
    {
        return $this->status === OnboardingStatus::CHANGES_REQUESTED;
    }

    /**
     * Get the latest submission for a participant
     */
    public static function latestForParticipant(Participant $participant): ?self
    {
        return self::where('participant_id', $participant->id)
            ->orderByDesc('created_at')
            ->first();
    }
}
