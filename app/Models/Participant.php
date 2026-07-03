<?php

namespace App\Models;

use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Participant extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_ONBOARDING = 'onboarding';

    public const STATUS_PENDING_ADMIN_REVIEW = 'pending_admin_review';

    public const STATUS_AHHC_REVIEW = 'ahhc_review';

    public const STATUS_ELIGIBILITY_ASSESSMENT = 'eligibility_assessment';

    public const STATUS_SUITABILITY_ASSESSMENT = 'suitability_assessment';

    public const STATUS_CLOSED = 'closed';

    public static function statusOptions(): array
    {
        return [
            self::STATUS_ONBOARDING,
            self::STATUS_PENDING_ADMIN_REVIEW,
            self::STATUS_AHHC_REVIEW,
            self::STATUS_ELIGIBILITY_ASSESSMENT,
            self::STATUS_SUITABILITY_ASSESSMENT,
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_CLOSED,
        ];
    }

    protected $fillable = [
        'user_id',
        'application_id',
        'participant_number',
        'first_name',
        'last_name',
        'date_of_birth',
        'preferred_name',
        'status',
        'onboarding_status',
        'onboarding_token',
        'onboarding_expires_at',
        'approved_at',
        'activated_at',
        'care_plan_start_date',
        'care_plan_end_date',
        'primary_language',
        'address',
        'city',
        'state',
        'postcode',
        'phone',
        'email',
        'medical_alerts',
        'notes',
        'consent_to_share',
        'budget_limit_cents',
        'current_budget_used_cents',
        'assigned_support_person_id',
        'created_by_id',
        'updated_by_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'care_plan_start_date' => 'date',
        'care_plan_end_date' => 'date',
        'onboarding_expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'activated_at' => 'datetime',
        'consent_to_share' => 'boolean',
        'budget_limit_cents' => 'integer',
        'current_budget_used_cents' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supportPerson(): BelongsTo
    {
        return $this->belongsTo(SupportPerson::class, 'assigned_support_person_id');
    }

    public function participantStatusHistories(): HasMany
    {
        return $this->hasMany(ParticipantStatusHistory::class)->latest('created_at');
    }

    protected static function booted(): void
    {
        static::created(function (Participant $participant) {
            if ($participant->status) {
                $participant->recordStatusHistory(null, $participant->status, auth()->id(), 'Initial status set.');
            }
        });

        static::updating(function (Participant $participant) {
            if ($participant->isDirty('status')) {
                $participant->recordStatusHistory(
                    $participant->getOriginal('status'),
                    $participant->status,
                    auth()->id()
                );
            }
        });

        static::deleting(function (Participant $participant) {
            $participant->agreements()->detach();
        });
    }

    protected function recordStatusHistory(?string $previousStatus, string $newStatus, ?int $changedById = null, ?string $notes = null): ParticipantStatusHistory
    {
        $history = $this->participantStatusHistories()->create([
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'changed_by_id' => $changedById,
            'notes' => $notes,
        ]);

        AuditLogService::record(
            'Participant Status Changed',
            $this,
            ['status' => $previousStatus],
            ['status' => $newStatus, 'participant_status_history_id' => $history->id],
            $changedById
        );

        if ($previousStatus !== null && $this->user) {
            NotificationService::notify([
                'user_id' => $this->user->id,
                'participant_id' => $this->id,
                'type' => 'info',
                'data' => [
                    'title' => 'Account status updated',
                    'message' => sprintf(
                        'Your AHHC account status changed from %s to %s.',
                        ucfirst($previousStatus),
                        ucfirst($newStatus)
                    ),
                    'url' => route('portal.dashboard'),
                ],
            ]);
        }

        return $history;
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ParticipantAssignment::class);
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(Assessment::class);
    }

    public function careNotes(): HasMany
    {
        return $this->hasMany(CareNote::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function preApprovalRequests(): HasMany
    {
        return $this->hasMany(PreApprovalRequest::class);
    }

    public function riskScores(): HasMany
    {
        return $this->hasMany(ParticipantRiskScore::class);
    }

    public function latestRiskScore(): ?ParticipantRiskScore
    {
        return $this->riskScores()->latest('calculated_at')->first();
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'owner');
    }

    public function monthlyReviews(): HasMany
    {
        return $this->hasMany(MonthlyCareReview::class);
    }

    public function contactLogs(): HasMany
    {
        return $this->hasMany(CareContactLog::class);
    }

    public function workerNominations(): HasMany
    {
        return $this->hasMany(WorkerNomination::class);
    }

    /**
     * Participant Application (if created from public form)
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(ParticipantApplication::class, 'application_id');
    }

    /**
     * Onboarding Submissions
     */
    public function onboardingSubmissions(): HasMany
    {
        return $this->hasMany(OnboardingSubmission::class);
    }

    /**
     * Get latest onboarding submission
     */
    public function latestOnboardingSubmission(): ?OnboardingSubmission
    {
        return $this->onboardingSubmissions()->latest()->first();
    }

    /**
     * Agreements assigned to this participant
     */
    public function agreements()
    {
        return $this->belongsToMany(Agreement::class, 'agreement_participant');
    }

    /**
     * Signed agreements
     */
    public function signedAgreements()
    {
        return $this->hasMany(AgreementSignature::class);
    }

    /**
     * Check if participant is in onboarding process
     */
    public function isOnboarding(): bool
    {
        return in_array($this->onboarding_status, [
            'invitation_sent',
            'in_progress',
            'pending_review',
            'changes_requested',
        ]);
    }

    /**
     * Check if onboarding is approved
     */
    public function isOnboardingApproved(): bool
    {
        return $this->onboarding_status === 'approved';
    }

    /**
     * Check if onboarding is activated
     */
    public function isActivated(): bool
    {
        return $this->onboarding_status === 'activated' && $this->activated_at !== null;
    }

    /**
     * Check if onboarding token is valid
     */
    public function hasValidOnboardingToken(): bool
    {
        return $this->onboarding_token !== null &&
               $this->onboarding_expires_at !== null &&
               $this->onboarding_expires_at->isFuture();
    }

    /**
     * Get all required agreements for this participant
     */
    public function getRequiredAgreements()
    {
        return $this->agreements()->where('is_required', true)->get();
    }

    /**
     * Check if all required agreements are signed
     */
    public function hasSignedAllRequiredAgreements(): bool
    {
        $required = $this->getRequiredAgreements();
        if ($required->isEmpty()) {
            return true;
        }

        $signed = $this->signedAgreements()->pluck('agreement_id')->toArray();
        foreach ($required as $agreement) {
            if (! in_array($agreement->id, $signed)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get unsigned required agreements
     */
    public function getUnsignedRequiredAgreements()
    {
        $signed = $this->signedAgreements()->pluck('agreement_id')->toArray();

        return $this->getRequiredAgreements()->filter(function ($agreement) use ($signed) {
            return ! in_array($agreement->id, $signed);
        });
    }
}
