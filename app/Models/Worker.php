<?php

namespace App\Models;

use App\Enums\WorkerOnboardingStage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'worker_number',
        'first_name',
        'last_name',
        'phone',
        'email',
        'role_type',
        'status',
        'qualification',
        'availability',
        'compliance_expiry_at',
        'background_check_expiry_at',
        'vehicle_type',
        'notes',
        'onboarding_stage',
        'onboarding_token',
        'onboarding_expires_at',
        'invited_by_id',
        'invited_at',
        'stage_1_completed_at',
        'stage_2_submitted_at',
        'stage_2_completed_at',
        'stage_2_reviewer_id',
        'stage_3_submitted_at',
        'stage_3_completed_at',
        'stage_3_reviewer_id',
        'stage_4_submitted_at',
        'stage_4_completed_at',
        'stage_5_submitted_at',
        'stage_5_completed_at',
        'stage_5_approver_id',
        'stage_6_assigned_at',
        'stage_6_assignor_id',
    ];

    protected $casts = [
        'compliance_expiry_at' => 'date',
        'background_check_expiry_at' => 'date',
        'compliance_suspended_at' => 'datetime',
        'onboarding_expires_at' => 'datetime',
        'invited_at' => 'datetime',
        'stage_1_completed_at' => 'datetime',
        'stage_2_submitted_at' => 'datetime',
        'stage_2_completed_at' => 'datetime',
        'stage_3_submitted_at' => 'datetime',
        'stage_3_completed_at' => 'datetime',
        'stage_4_submitted_at' => 'datetime',
        'stage_4_completed_at' => 'datetime',
        'stage_5_submitted_at' => 'datetime',
        'stage_5_completed_at' => 'datetime',
        'stage_6_assigned_at' => 'datetime',
        'onboarding_stage' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ParticipantAssignment::class);
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

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function complianceDocuments(): HasMany
    {
        return $this->hasMany(WorkerComplianceDocument::class);
    }

    public function complianceAlerts(): HasMany
    {
        return $this->hasMany(WorkerComplianceAlert::class);
    }

    public function declarations(): HasMany
    {
        return $this->hasMany(WorkerDeclaration::class);
    }

    public function serviceApprovals(): HasMany
    {
        return $this->hasMany(WorkerServiceApproval::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_id');
    }

    public function stage2Reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stage_2_reviewer_id');
    }

    public function stage3Reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stage_3_reviewer_id');
    }

    public function stage5Approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stage_5_approver_id');
    }

    public function stage6Assignor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'stage_6_assignor_id');
    }

    // Helper methods for onboarding stages
    public function getCurrentStage(): WorkerOnboardingStage
    {
        return WorkerOnboardingStage::from($this->onboarding_stage);
    }

    public function moveToNextStage(): bool
    {
        $nextStage = $this->onboarding_stage + 1;
        if ($nextStage > 6) {
            return false;
        }
        $this->onboarding_stage = $nextStage;

        return $this->save();
    }

    public function isOnboardingComplete(): bool
    {
        return $this->onboarding_stage === 6;
    }

    public function canAccessParticipantData(): bool
    {
        // Workers can only access participant data once they're in Stage 6 (Assigned)
        return $this->onboarding_stage >= 6;
    }

    public function getAllDeclarationsSignedForStage4(): bool
    {
        $declarations = WorkerDeclaration::where('worker_id', $this->id)->get();
        if ($declarations->isEmpty()) {
            return false;
        }

        return $declarations->every(fn ($d) => $d->isSigned());
    }

    public function hasApprovedServices(): bool
    {
        return $this->serviceApprovals()
            ->where('status', 'approved')
            ->exists();
    }
}
