<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    /**
     * Determine whether the user can view any model.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'system_admin', 'ahhc_staff']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Assessment $assessment): bool
    {
        // Admin/staff can always view
        if ($user->hasRole(['admin', 'system_admin', 'ahhc_staff'])) {
            return true;
        }

        // Participant can view their own assessment
        if ($user->participant_id && $assessment->participant_id === $user->participant_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'ahhc_staff']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Assessment $assessment): bool
    {
        // Only admin/ahhc_staff can update assessments
        if (! $user->hasRole(['admin', 'ahhc_staff'])) {
            return false;
        }

        // Cannot update rejected or closed assessments
        if (in_array($assessment->status, [Assessment::STATUS_REJECTED, Assessment::STATUS_CLOSED])) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can assign the assessment
     */
    public function assign(User $user, Assessment $assessment): bool
    {
        return $user->hasRole(['admin', 'system_admin']) &&
               $assessment->status === Assessment::STATUS_NEW_ENQUIRY;
    }

    /**
     * Determine whether the user can review the assessment
     */
    public function review(User $user, Assessment $assessment): bool
    {
        if (! $user->hasRole(['admin', 'ahhc_staff'])) {
            return false;
        }

        // Can only review if assigned to user or user is admin
        if (! $user->hasRole('admin') && $assessment->assigned_to_user_id !== $user->id) {
            return false;
        }

        // Can review if under review, awaiting information, or during assessment phases
        return in_array($assessment->status, [
            Assessment::STATUS_UNDER_REVIEW,
            Assessment::STATUS_AWAITING_INFORMATION,
            Assessment::STATUS_ELIGIBILITY_APPROVED,
            Assessment::STATUS_SUITABILITY_APPROVED,
            Assessment::STATUS_FUNDING_VERIFIED,
            Assessment::STATUS_PROFILE_SETUP_COMPLETE,
            Assessment::STATUS_BUDGET_SETUP_COMPLETE,
            Assessment::STATUS_DOCUMENTS_COLLECTED,
            Assessment::STATUS_AGREEMENTS_ASSIGNED,
            Assessment::STATUS_ASSESSMENT_COMPLETE,
        ]);
    }

    /**
     * Determine whether the user can approve the assessment
     */
    public function approve(User $user, Assessment $assessment): bool
    {
        // Only admin/ahhc_staff can approve
        if (! $user->hasRole(['admin', 'ahhc_staff'])) {
            return false;
        }

        // Can only approve if in assessment complete status
        return $assessment->status === Assessment::STATUS_ASSESSMENT_COMPLETE;
    }

    /**
     * Determine whether the user can reject the assessment
     */
    public function reject(User $user, Assessment $assessment): bool
    {
        // Only admin/ahhc_staff can reject
        if (! $user->hasRole(['admin', 'ahhc_staff'])) {
            return false;
        }

        // Cannot reject if already rejected or closed
        if (in_array($assessment->status, [Assessment::STATUS_REJECTED, Assessment::STATUS_CLOSED])) {
            return false;
        }

        // Can reject at any assessment stage
        return in_array($assessment->status, [
            Assessment::STATUS_NEW_ENQUIRY,
            Assessment::STATUS_UNDER_REVIEW,
            Assessment::STATUS_AWAITING_INFORMATION,
            Assessment::STATUS_ELIGIBILITY_APPROVED,
            Assessment::STATUS_SUITABILITY_APPROVED,
            Assessment::STATUS_FUNDING_VERIFIED,
            Assessment::STATUS_PROFILE_SETUP_COMPLETE,
            Assessment::STATUS_BUDGET_SETUP_COMPLETE,
            Assessment::STATUS_DOCUMENTS_COLLECTED,
            Assessment::STATUS_AGREEMENTS_ASSIGNED,
            Assessment::STATUS_ASSESSMENT_COMPLETE,
        ]);
    }

    /**
     * Determine whether the user can send invitation
     */
    public function sendInvitation(User $user, Assessment $assessment): bool
    {
        return $user->hasRole(['admin', 'ahhc_staff']) &&
               $assessment->canReceiveInvitation();
    }

    /**
     * Determine whether the user can activate portal
     */
    public function activate(User $user, Assessment $assessment): bool
    {
        return $user->hasRole(['admin', 'ahhc_staff']) &&
               $assessment->status === Assessment::STATUS_FINAL_REVIEW;
    }

    /**
     * Determine whether the user can add notes
     */
    public function addNote(User $user, Assessment $assessment): bool
    {
        if (! $user->hasRole(['admin', 'ahhc_staff'])) {
            return false;
        }

        return $assessment->status !== Assessment::STATUS_CLOSED;
    }

    /**
     * Determine whether the user can request information
     */
    public function requestInformation(User $user, Assessment $assessment): bool
    {
        if (! $user->hasRole(['admin', 'ahhc_staff'])) {
            return false;
        }

        return in_array($assessment->status, [
            Assessment::STATUS_UNDER_REVIEW,
            Assessment::STATUS_ASSESSMENT_COMPLETE,
        ]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        // Only system admin can delete assessments
        return $user->hasRole('system_admin') &&
               in_array($assessment->status, [Assessment::STATUS_REJECTED, Assessment::STATUS_CLOSED]);
    }
}
