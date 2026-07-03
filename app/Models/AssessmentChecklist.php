<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentChecklist extends Model
{
    protected $fillable = [
        'assessment_id',
        'identity_confirmed',
        'contact_details_verified',
        'address_verified',
        'support_at_home_eligibility_confirmed',
        'program_eligibility_confirmed',
        'can_manage_workers',
        'can_make_service_decisions',
        'can_approve_invoices',
        'can_review_spending',
        'understands_responsibilities',
        'funding_verified',
        'funding_documentation_received',
        'budget_confirmed',
        'participant_profile_completed',
        'emergency_contact_added',
        'support_person_added',
        'care_plan_created',
        'support_plan_created',
        'budget_configured',
        'quarter_configured',
        'carry_over_configured',
        'referral_documents_collected',
        'participant_documents_collected',
        'authority_documents_collected',
        'funding_documents_collected',
        'agreements_assigned',
        'mfa_enabled',
        'profile_confirmed',
        'documents_uploaded',
        'agreements_signed',
        'support_person_approved',
        'all_onboarding_steps_completed',
        'completion_percentage',
    ];

    protected $casts = [
        'identity_confirmed' => 'boolean',
        'contact_details_verified' => 'boolean',
        'address_verified' => 'boolean',
        'support_at_home_eligibility_confirmed' => 'boolean',
        'program_eligibility_confirmed' => 'boolean',
        'can_manage_workers' => 'boolean',
        'can_make_service_decisions' => 'boolean',
        'can_approve_invoices' => 'boolean',
        'can_review_spending' => 'boolean',
        'understands_responsibilities' => 'boolean',
        'funding_verified' => 'boolean',
        'funding_documentation_received' => 'boolean',
        'budget_confirmed' => 'boolean',
        'participant_profile_completed' => 'boolean',
        'emergency_contact_added' => 'boolean',
        'support_person_added' => 'boolean',
        'care_plan_created' => 'boolean',
        'support_plan_created' => 'boolean',
        'budget_configured' => 'boolean',
        'quarter_configured' => 'boolean',
        'carry_over_configured' => 'boolean',
        'referral_documents_collected' => 'boolean',
        'participant_documents_collected' => 'boolean',
        'authority_documents_collected' => 'boolean',
        'funding_documents_collected' => 'boolean',
        'agreements_assigned' => 'boolean',
        'mfa_enabled' => 'boolean',
        'profile_confirmed' => 'boolean',
        'documents_uploaded' => 'boolean',
        'agreements_signed' => 'boolean',
        'support_person_approved' => 'boolean',
        'all_onboarding_steps_completed' => 'boolean',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Calculate and update completion percentage
     */
    public function calculateCompletion(): int
    {
        $total_items = 34; // Total number of checklist items

        $completed = 0;
        $completed += $this->identity_confirmed ? 1 : 0;
        $completed += $this->contact_details_verified ? 1 : 0;
        $completed += $this->address_verified ? 1 : 0;
        $completed += $this->support_at_home_eligibility_confirmed ? 1 : 0;
        $completed += $this->program_eligibility_confirmed ? 1 : 0;
        $completed += $this->can_manage_workers ? 1 : 0;
        $completed += $this->can_make_service_decisions ? 1 : 0;
        $completed += $this->can_approve_invoices ? 1 : 0;
        $completed += $this->can_review_spending ? 1 : 0;
        $completed += $this->understands_responsibilities ? 1 : 0;
        $completed += $this->funding_verified ? 1 : 0;
        $completed += $this->funding_documentation_received ? 1 : 0;
        $completed += $this->budget_confirmed ? 1 : 0;
        $completed += $this->participant_profile_completed ? 1 : 0;
        $completed += $this->emergency_contact_added ? 1 : 0;
        $completed += $this->support_person_added ? 1 : 0;
        $completed += $this->care_plan_created ? 1 : 0;
        $completed += $this->support_plan_created ? 1 : 0;
        $completed += $this->budget_configured ? 1 : 0;
        $completed += $this->quarter_configured ? 1 : 0;
        $completed += $this->carry_over_configured ? 1 : 0;
        $completed += $this->referral_documents_collected ? 1 : 0;
        $completed += $this->participant_documents_collected ? 1 : 0;
        $completed += $this->authority_documents_collected ? 1 : 0;
        $completed += $this->funding_documents_collected ? 1 : 0;
        $completed += $this->agreements_assigned ? 1 : 0;
        $completed += $this->mfa_enabled ? 1 : 0;
        $completed += $this->profile_confirmed ? 1 : 0;
        $completed += $this->documents_uploaded ? 1 : 0;
        $completed += $this->agreements_signed ? 1 : 0;
        $completed += $this->support_person_approved ? 1 : 0;
        $completed += $this->all_onboarding_steps_completed ? 1 : 0;

        $percentage = (int) (($completed / $total_items) * 100);
        $this->completion_percentage = $percentage;
        $this->save();

        return $percentage;
    }

    /**
     * Check if all requirements are met for approval
     */
    public function isReadyForApproval(): bool
    {
        return $this->identity_confirmed &&
               $this->contact_details_verified &&
               $this->support_at_home_eligibility_confirmed &&
               $this->program_eligibility_confirmed &&
               $this->can_manage_workers &&
               $this->funding_verified &&
               $this->participant_profile_completed &&
               $this->care_plan_created &&
               $this->support_plan_created &&
               $this->budget_configured &&
               $this->referral_documents_collected &&
               $this->agreements_assigned;
    }
}
