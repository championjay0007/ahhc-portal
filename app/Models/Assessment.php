<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'participant_id',
        'assigned_to_user_id',
        'created_by_user_id',
        'status',
        'previous_status',
        'enquiry_source',
        'enquiry_notes',
        'funding_source',
        'funding_type',
        'funding_details',
        'budget_allocation',
        'support_person_required',
        'support_person_name',
        'support_person_relationship',
        'support_person_email',
        'support_person_phone',
        'support_person_authority_level',
        'eligibility_outcome',
        'suitability_outcome',
        'funding_verification_outcome',
        'overall_decision',
        'approval_notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
        'approved_by_user_id',
        'rejected_by_user_id',
        'invitation_token',
        'invitation_sent_at',
        'invitation_expires_at',
        'invitation_accepted_at',
        'onboarding_started_at',
        'onboarding_completed_at',
        'final_review_started_at',
        'final_review_completed_at',
        'activated_at',
        'activated_by_user_id',
        'metadata',
        'ip_address',
    ];

    protected $casts = [
        'support_person_required' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'invitation_sent_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'invitation_accepted_at' => 'datetime',
        'onboarding_started_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'final_review_started_at' => 'datetime',
        'final_review_completed_at' => 'datetime',
        'activated_at' => 'datetime',
        'budget_allocation' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Status constants
    const STATUS_NEW_ENQUIRY = 'new_enquiry';

    const STATUS_UNDER_REVIEW = 'under_review';

    const STATUS_AWAITING_INFORMATION = 'awaiting_information';

    const STATUS_ELIGIBILITY_APPROVED = 'eligibility_approved';

    const STATUS_SUITABILITY_APPROVED = 'suitability_approved';

    const STATUS_FUNDING_VERIFIED = 'funding_verified';

    const STATUS_PROFILE_SETUP_COMPLETE = 'profile_setup_complete';

    const STATUS_BUDGET_SETUP_COMPLETE = 'budget_setup_complete';

    const STATUS_DOCUMENTS_COLLECTED = 'documents_collected';

    const STATUS_AGREEMENTS_ASSIGNED = 'agreements_assigned';

    const STATUS_ASSESSMENT_COMPLETE = 'assessment_complete';

    const STATUS_APPROVED = 'approved';

    const STATUS_INVITATION_SENT = 'invitation_sent';

    const STATUS_ONBOARDING_IN_PROGRESS = 'onboarding_in_progress';

    const STATUS_FINAL_REVIEW = 'final_review';

    const STATUS_PORTAL_ACTIVATED = 'portal_activated';

    const STATUS_ACTIVE_PARTICIPANT = 'active_participant';

    const STATUS_REJECTED = 'rejected';

    const STATUS_CLOSED = 'closed';

    // Approval outcomes
    const OUTCOME_ELIGIBLE = 'eligible';

    const OUTCOME_NOT_ELIGIBLE = 'not_eligible';

    const OUTCOME_FURTHER_INFORMATION = 'further_information';

    const OUTCOME_SUITABLE = 'suitable';

    const OUTCOME_SUITABLE_WITH_SUPPORT = 'suitable_with_support';

    const OUTCOME_NOT_SUITABLE = 'not_suitable';

    const DECISION_APPROVED = 'approved';

    const DECISION_REJECTED = 'rejected';

    const DECISION_PENDING = 'pending';

    const DECISION_AWAITING_INFORMATION = 'awaiting_information';

    /**
     * Get all assessment statuses
     */
    public static function getAllStatuses(): array
    {
        return [
            self::STATUS_NEW_ENQUIRY,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_AWAITING_INFORMATION,
            self::STATUS_ELIGIBILITY_APPROVED,
            self::STATUS_SUITABILITY_APPROVED,
            self::STATUS_FUNDING_VERIFIED,
            self::STATUS_PROFILE_SETUP_COMPLETE,
            self::STATUS_BUDGET_SETUP_COMPLETE,
            self::STATUS_DOCUMENTS_COLLECTED,
            self::STATUS_AGREEMENTS_ASSIGNED,
            self::STATUS_ASSESSMENT_COMPLETE,
            self::STATUS_APPROVED,
            self::STATUS_INVITATION_SENT,
            self::STATUS_ONBOARDING_IN_PROGRESS,
            self::STATUS_FINAL_REVIEW,
            self::STATUS_PORTAL_ACTIVATED,
            self::STATUS_ACTIVE_PARTICIPANT,
            self::STATUS_REJECTED,
            self::STATUS_CLOSED,
        ];
    }

    /**
     * Get status label for display
     */
    public static function getStatusLabel(string $status): string
    {
        $labels = [
            self::STATUS_NEW_ENQUIRY => 'New Enquiry',
            self::STATUS_UNDER_REVIEW => 'Under Review',
            self::STATUS_AWAITING_INFORMATION => 'Awaiting Information',
            self::STATUS_ELIGIBILITY_APPROVED => 'Eligibility Approved',
            self::STATUS_SUITABILITY_APPROVED => 'Suitability Approved',
            self::STATUS_FUNDING_VERIFIED => 'Funding Verified',
            self::STATUS_PROFILE_SETUP_COMPLETE => 'Profile Setup Complete',
            self::STATUS_BUDGET_SETUP_COMPLETE => 'Budget Setup Complete',
            self::STATUS_DOCUMENTS_COLLECTED => 'Documents Collected',
            self::STATUS_AGREEMENTS_ASSIGNED => 'Agreements Assigned',
            self::STATUS_ASSESSMENT_COMPLETE => 'Assessment Complete',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_INVITATION_SENT => 'Invitation Sent',
            self::STATUS_ONBOARDING_IN_PROGRESS => 'Onboarding In Progress',
            self::STATUS_FINAL_REVIEW => 'Final Review',
            self::STATUS_PORTAL_ACTIVATED => 'Portal Activated',
            self::STATUS_ACTIVE_PARTICIPANT => 'Active Participant',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CLOSED => 'Closed',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get status badge color
     */
    public static function getStatusBadgeClass(string $status): string
    {
        $classes = [
            self::STATUS_NEW_ENQUIRY => 'badge-info',
            self::STATUS_UNDER_REVIEW => 'badge-warning',
            self::STATUS_AWAITING_INFORMATION => 'badge-secondary',
            self::STATUS_ELIGIBILITY_APPROVED => 'badge-success',
            self::STATUS_SUITABILITY_APPROVED => 'badge-success',
            self::STATUS_FUNDING_VERIFIED => 'badge-success',
            self::STATUS_PROFILE_SETUP_COMPLETE => 'badge-success',
            self::STATUS_BUDGET_SETUP_COMPLETE => 'badge-success',
            self::STATUS_DOCUMENTS_COLLECTED => 'badge-success',
            self::STATUS_AGREEMENTS_ASSIGNED => 'badge-success',
            self::STATUS_ASSESSMENT_COMPLETE => 'badge-success',
            self::STATUS_APPROVED => 'badge-success',
            self::STATUS_INVITATION_SENT => 'badge-info',
            self::STATUS_ONBOARDING_IN_PROGRESS => 'badge-info',
            self::STATUS_FINAL_REVIEW => 'badge-warning',
            self::STATUS_PORTAL_ACTIVATED => 'badge-success',
            self::STATUS_ACTIVE_PARTICIPANT => 'badge-success',
            self::STATUS_REJECTED => 'badge-danger',
            self::STATUS_CLOSED => 'badge-dark',
        ];

        return $classes[$status] ?? 'badge-secondary';
    }

    // Relationships

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }

    public function activatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by_user_id');
    }

    public function checklist(): HasOne
    {
        return $this->hasOne(AssessmentChecklist::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(AssessmentNote::class)->orderByDesc('created_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AssessmentDocument::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(AssessmentStatusHistory::class)->orderByDesc('created_at');
    }

    public function budgetSetups(): HasMany
    {
        return $this->hasMany(ParticipantBudgetSetup::class);
    }

    public function currentBudgetSetup()
    {
        return $this->hasOne(ParticipantBudgetSetup::class)->where('is_current', true);
    }

    // Scopes

    public function scopeNewApplications($query)
    {
        return $query->where('status', self::STATUS_NEW_ENQUIRY);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', self::STATUS_UNDER_REVIEW);
    }

    public function scopeAwaitingInformation($query)
    {
        return $query->where('status', self::STATUS_AWAITING_INFORMATION);
    }

    public function scopeApproved($query)
    {
        return $query->where('overall_decision', self::DECISION_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('overall_decision', self::DECISION_REJECTED);
    }

    public function scopeCanReceiveInvitation($query)
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->where('overall_decision', self::DECISION_APPROVED);
    }

    public function scopeReadyForActivation($query)
    {
        return $query->where('status', self::STATUS_FINAL_REVIEW);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE_PARTICIPANT);
    }

    // Helper methods

    /**
     * Check if application can be invited
     */
    public function canReceiveInvitation(): bool
    {
        return $this->status === self::STATUS_APPROVED &&
               $this->overall_decision === self::DECISION_APPROVED &&
               is_null($this->invitation_sent_at);
    }

    /**
     * Check if application is in active review
     */
    public function isUnderActiveReview(): bool
    {
        return in_array($this->status, [
            self::STATUS_NEW_ENQUIRY,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_AWAITING_INFORMATION,
        ]);
    }

    /**
     * Check if invitation is still valid
     */
    public function isInvitationValid(): bool
    {
        if (is_null($this->invitation_token) || is_null($this->invitation_expires_at)) {
            return false;
        }

        return $this->invitation_expires_at > now();
    }

    /**
     * Check if final onboarding is complete
     */
    public function isOnboardingComplete(): bool
    {
        return ! is_null($this->onboarding_completed_at);
    }

    /**
     * Get assessment completion percentage
     */
    public function getCompletionPercentage(): int
    {
        if ($this->checklist) {
            return $this->checklist->completion_percentage;
        }

        return 0;
    }

    /**
     * Check if assessment can be approved
     */
    public function canBeApproved(): bool
    {
        $checklist = $this->checklist;

        if (! $checklist) {
            return false;
        }

        return $checklist->identity_confirmed &&
               $checklist->contact_details_verified &&
               $checklist->support_at_home_eligibility_confirmed &&
               $checklist->program_eligibility_confirmed &&
               $checklist->can_manage_workers &&
               $checklist->funding_verified &&
               $checklist->participant_profile_completed &&
               $checklist->care_plan_created &&
               $checklist->support_plan_created &&
               $checklist->budget_configured &&
               $checklist->referral_documents_collected &&
               $checklist->agreements_assigned;
    }
}
