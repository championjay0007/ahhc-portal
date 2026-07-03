<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentChecklist;
use App\Models\AssessmentNote;
use App\Models\AssessmentStatusHistory;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssessmentService
{
    /**
     * Create new assessment from enquiry
     */
    public function createAssessmentFromEnquiry(array $data, User $createdByUser, ?string $ipAddress = null): Assessment
    {
        $assessment = Assessment::create([
            'created_by_user_id' => $createdByUser->id,
            'status' => Assessment::STATUS_NEW_ENQUIRY,
            'enquiry_source' => $data['enquiry_source'] ?? 'website',
            'enquiry_notes' => $data['enquiry_notes'] ?? null,
            'funding_source' => $data['funding_source'] ?? null,
            'support_person_required' => $data['support_person_required'] ?? false,
            'ip_address' => $ipAddress,
        ]);

        // Create associated checklist
        AssessmentChecklist::create(['assessment_id' => $assessment->id]);

        // Log status transition
        $this->logStatusTransition(
            $assessment,
            null,
            Assessment::STATUS_NEW_ENQUIRY,
            'Assessment created from enquiry',
            $createdByUser,
            $ipAddress
        );

        return $assessment;
    }

    /**
     * Assign assessment to reviewer
     */
    public function assignAssessment(Assessment $assessment, User $reviewer, User $assignedByUser, ?string $ipAddress = null): Assessment
    {
        $assessment->update([
            'assigned_to_user_id' => $reviewer->id,
            'status' => Assessment::STATUS_UNDER_REVIEW,
        ]);

        $this->logStatusTransition(
            $assessment,
            Assessment::STATUS_NEW_ENQUIRY,
            Assessment::STATUS_UNDER_REVIEW,
            "Assessment assigned to {$reviewer->name}",
            $assignedByUser,
            $ipAddress
        );

        return $assessment;
    }

    /**
     * Add note to assessment
     */
    public function addNote(
        Assessment $assessment,
        string $noteText,
        User $createdByUser,
        string $noteType = AssessmentNote::NOTE_TYPE_GENERAL,
        bool $isInternal = true,
        bool $requiresAction = false,
        ?string $ipAddress = null
    ): AssessmentNote {
        return AssessmentNote::create([
            'assessment_id' => $assessment->id,
            'created_by_user_id' => $createdByUser->id,
            'note_text' => $noteText,
            'note_type' => $noteType,
            'is_internal' => $isInternal,
            'requires_action' => $requiresAction,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * Request information from participant
     */
    public function requestInformation(
        Assessment $assessment,
        string $informationNeeded,
        User $requestedByUser,
        ?string $ipAddress = null
    ): Assessment {
        $assessment->update([
            'status' => Assessment::STATUS_AWAITING_INFORMATION,
        ]);

        $this->addNote(
            $assessment,
            $informationNeeded,
            $requestedByUser,
            AssessmentNote::NOTE_TYPE_INFORMATION_REQUEST,
            false,
            true,
            $ipAddress
        );

        $this->logStatusTransition(
            $assessment,
            $assessment->previous_status ?? Assessment::STATUS_UNDER_REVIEW,
            Assessment::STATUS_AWAITING_INFORMATION,
            'Information requested from participant',
            $requestedByUser,
            $ipAddress
        );

        return $assessment->fresh();
    }

    /**
     * Log status transition
     */
    public function logStatusTransition(
        Assessment $assessment,
        ?string $fromStatus,
        string $toStatus,
        string $reason,
        User $changedByUser,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): AssessmentStatusHistory {
        return AssessmentStatusHistory::create([
            'assessment_id' => $assessment->id,
            'changed_by_user_id' => $changedByUser->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'transition_reason' => $reason,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Update assessment status
     */
    public function updateStatus(
        Assessment $assessment,
        string $newStatus,
        User $changedByUser,
        string $reason = '',
        ?string $ipAddress = null
    ): Assessment {
        $oldStatus = $assessment->status;

        $assessment->update([
            'previous_status' => $oldStatus,
            'status' => $newStatus,
        ]);

        $this->logStatusTransition(
            $assessment,
            $oldStatus,
            $newStatus,
            $reason,
            $changedByUser,
            $ipAddress
        );

        return $assessment->fresh();
    }

    /**
     * Check if assessment can be approved
     */
    public function canApprove(Assessment $assessment): array
    {
        $checklist = $assessment->checklist;
        $errors = [];

        if (! $checklist) {
            $errors[] = 'Assessment checklist not found';

            return ['can_approve' => false, 'errors' => $errors];
        }

        $checks = [
            'identity_confirmed' => 'Identity must be confirmed',
            'contact_details_verified' => 'Contact details must be verified',
            'support_at_home_eligibility_confirmed' => 'Support at Home eligibility must be confirmed',
            'program_eligibility_confirmed' => 'Program eligibility must be confirmed',
            'can_manage_workers' => 'Participant must be able to manage workers',
            'funding_verified' => 'Funding must be verified',
            'participant_profile_completed' => 'Participant profile must be completed',
            'care_plan_created' => 'Care plan must be created',
            'support_plan_created' => 'Support plan must be created',
            'budget_configured' => 'Budget must be configured',
            'referral_documents_collected' => 'Referral documents must be collected',
            'agreements_assigned' => 'Agreements must be assigned',
        ];

        foreach ($checks as $field => $message) {
            if (! $checklist->{$field}) {
                $errors[] = $message;
            }
        }

        return [
            'can_approve' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Approve assessment
     */
    public function approveAssessment(
        Assessment $assessment,
        User $approvedByUser,
        string $approvalNotes = '',
        ?string $ipAddress = null
    ): Assessment {
        // Check if can approve
        $approval = $this->canApprove($assessment);
        if (! $approval['can_approve']) {
            throw new \Exception('Assessment cannot be approved: '.implode(', ', $approval['errors']));
        }

        // Update assessment
        $assessment->update([
            'status' => Assessment::STATUS_APPROVED,
            'overall_decision' => Assessment::DECISION_APPROVED,
            'approval_notes' => $approvalNotes,
            'approved_at' => now(),
            'approved_by_user_id' => $approvedByUser->id,
        ]);

        // Log transition
        $this->logStatusTransition(
            $assessment,
            $assessment->previous_status,
            Assessment::STATUS_APPROVED,
            'Assessment approved',
            $approvedByUser,
            $ipAddress
        );

        return $assessment->fresh();
    }

    /**
     * Reject assessment
     */
    public function rejectAssessment(
        Assessment $assessment,
        User $rejectedByUser,
        string $rejectionReason,
        ?string $ipAddress = null
    ): Assessment {
        if (empty($rejectionReason)) {
            throw new \Exception('Rejection reason is required');
        }

        $assessment->update([
            'status' => Assessment::STATUS_REJECTED,
            'overall_decision' => Assessment::DECISION_REJECTED,
            'rejection_reason' => $rejectionReason,
            'rejected_at' => now(),
            'rejected_by_user_id' => $rejectedByUser->id,
        ]);

        // Log transition
        $this->logStatusTransition(
            $assessment,
            $assessment->previous_status,
            Assessment::STATUS_REJECTED,
            "Rejection reason: {$rejectionReason}",
            $rejectedByUser,
            $ipAddress
        );

        return $assessment->fresh();
    }

    /**
     * Generate invitation token
     */
    public function generateInvitationToken(Assessment $assessment, User $sentByUser, ?string $ipAddress = null): Assessment
    {
        if (! $assessment->canReceiveInvitation()) {
            throw new \Exception('Assessment is not eligible for invitation');
        }

        $token = Str::random(64);
        $expiresAt = now()->addDays(30);

        $assessment->update([
            'invitation_token' => $token,
            'invitation_sent_at' => now(),
            'invitation_expires_at' => $expiresAt,
            'status' => Assessment::STATUS_INVITATION_SENT,
        ]);

        // Log transition
        $this->logStatusTransition(
            $assessment,
            Assessment::STATUS_APPROVED,
            Assessment::STATUS_INVITATION_SENT,
            'Invitation token sent',
            $sentByUser,
            $ipAddress
        );

        return $assessment->fresh();
    }

    /**
     * Validate and accept invitation
     */
    public function acceptInvitation(string $token): ?Assessment
    {
        $assessment = Assessment::where('invitation_token', $token)
            ->where('invitation_expires_at', '>', now())
            ->first();

        if (! $assessment) {
            return null;
        }

        $assessment->update([
            'invitation_accepted_at' => now(),
            'status' => Assessment::STATUS_ONBOARDING_IN_PROGRESS,
        ]);

        // Create participant if doesn't exist
        if (! $assessment->participant_id) {
            $participant = Participant::create([
                'email' => $assessment->enquiry_notes ? 'temp@example.com' : null,
                'status' => 'onboarding',
            ]);

            $assessment->update(['participant_id' => $participant->id]);
        }

        return $assessment->fresh();
    }

    /**
     * Mark onboarding as complete
     */
    public function completeOnboarding(Assessment $assessment, User $completedByUser, ?string $ipAddress = null): Assessment
    {
        $assessment->update([
            'onboarding_completed_at' => now(),
            'status' => Assessment::STATUS_FINAL_REVIEW,
        ]);

        $this->logStatusTransition(
            $assessment,
            Assessment::STATUS_ONBOARDING_IN_PROGRESS,
            Assessment::STATUS_FINAL_REVIEW,
            'Onboarding completed by participant',
            $completedByUser,
            $ipAddress
        );

        return $assessment->fresh();
    }

    /**
     * Complete final review and activate
     */
    public function activateParticipant(Assessment $assessment, User $activatedByUser, ?string $ipAddress = null): Assessment
    {
        // Check final review checklist
        $checklist = $assessment->checklist;
        if (! $checklist || ! $this->isFinalReviewComplete($checklist)) {
            throw new \Exception('Final review requirements not met');
        }

        // Update assessment
        $assessment->update([
            'status' => Assessment::STATUS_PORTAL_ACTIVATED,
            'activated_at' => now(),
            'activated_by_user_id' => $activatedByUser->id,
        ]);

        // Update participant status
        if ($assessment->participant) {
            $assessment->participant->update(['status' => 'active']);
        }

        // Log transition
        $this->logStatusTransition(
            $assessment,
            Assessment::STATUS_FINAL_REVIEW,
            Assessment::STATUS_PORTAL_ACTIVATED,
            'Portal activated for participant',
            $activatedByUser,
            $ipAddress
        );

        return $assessment->fresh();
    }

    /**
     * Check if final review is complete
     */
    private function isFinalReviewComplete(AssessmentChecklist $checklist): bool
    {
        return $checklist->mfa_enabled &&
               $checklist->profile_confirmed &&
               $checklist->documents_uploaded &&
               $checklist->agreements_signed &&
               $checklist->all_onboarding_steps_completed;
    }
}
