<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentChecklist;
use App\Models\AssessmentDocument;

class DecisionEngine
{
    /**
     * Evaluate eligibility
     */
    public function evaluateEligibility(Assessment $assessment, AssessmentChecklist $checklist): array
    {
        $issues = [];

        // Check all eligibility requirements
        if (! $checklist->identity_confirmed) {
            $issues[] = 'Identity not confirmed';
        }

        if (! $checklist->contact_details_verified) {
            $issues[] = 'Contact details not verified';
        }

        if (! $checklist->support_at_home_eligibility_confirmed) {
            $issues[] = 'Support at Home eligibility not confirmed';
        }

        if (! $checklist->program_eligibility_confirmed) {
            $issues[] = 'Program eligibility not confirmed';
        }

        $isEligible = count($issues) === 0;

        return [
            'eligible' => $isEligible,
            'outcome' => $isEligible ? Assessment::OUTCOME_ELIGIBLE : Assessment::OUTCOME_NOT_ELIGIBLE,
            'issues' => $issues,
        ];
    }

    /**
     * Evaluate suitability
     */
    public function evaluateSuitability(Assessment $assessment, AssessmentChecklist $checklist): array
    {
        $suitable = 0;
        $required = 5;

        if ($checklist->can_manage_workers) {
            $suitable++;
        }
        if ($checklist->can_make_service_decisions) {
            $suitable++;
        }
        if ($checklist->can_approve_invoices) {
            $suitable++;
        }
        if ($checklist->can_review_spending) {
            $suitable++;
        }
        if ($checklist->understands_responsibilities) {
            $suitable++;
        }

        // If all 5, fully suitable
        if ($suitable === $required) {
            return [
                'suitable' => true,
                'outcome' => Assessment::OUTCOME_SUITABLE,
                'meets_count' => $suitable,
                'requires_support' => false,
            ];
        }

        // If 3+ of 5, suitable with support
        if ($suitable >= 3) {
            return [
                'suitable' => true,
                'outcome' => Assessment::OUTCOME_SUITABLE_WITH_SUPPORT,
                'meets_count' => $suitable,
                'requires_support' => true,
            ];
        }

        // Less than 3, not suitable
        return [
            'suitable' => false,
            'outcome' => Assessment::OUTCOME_NOT_SUITABLE,
            'meets_count' => $suitable,
            'requires_support' => false,
        ];
    }

    /**
     * Verify funding
     */
    public function verifyFunding(Assessment $assessment, AssessmentChecklist $checklist): array
    {
        $issues = [];

        if (! $checklist->funding_verified) {
            $issues[] = 'Funding not verified with source';
        }

        if (! $checklist->funding_documentation_received) {
            $issues[] = 'Funding documentation not received';
        }

        if (! $checklist->budget_confirmed) {
            $issues[] = 'Budget not confirmed';
        }

        $isVerified = count($issues) === 0;

        return [
            'verified' => $isVerified,
            'outcome' => $isVerified ? 'verified' : Assessment::OUTCOME_FURTHER_INFORMATION,
            'issues' => $issues,
        ];
    }

    /**
     * Check document collection status
     */
    public function checkDocumentCollection(Assessment $assessment): array
    {
        $required_categories = [
            AssessmentDocument::CATEGORY_REFERRAL,
            AssessmentDocument::CATEGORY_CARE_PLAN,
            AssessmentDocument::CATEGORY_SUPPORT_PLAN,
        ];

        $missing_documents = [];
        $collected_documents = [];

        foreach ($required_categories as $category) {
            $document = $assessment->documents()
                ->where('document_category', $category)
                ->where('status', AssessmentDocument::STATUS_RECEIVED)
                ->first();

            if ($document) {
                $collected_documents[] = $category;
            } else {
                $missing_documents[] = $category;
            }
        }

        return [
            'all_collected' => count($missing_documents) === 0,
            'collected' => $collected_documents,
            'missing' => $missing_documents,
        ];
    }

    /**
     * Make overall decision
     */
    public function makeDecision(Assessment $assessment): array
    {
        $checklist = $assessment->checklist;

        if (! $checklist) {
            return [
                'decision' => Assessment::DECISION_PENDING,
                'approved' => false,
                'reasons' => ['Assessment checklist not initialized'],
            ];
        }

        // Run all evaluations
        $eligibility = $this->evaluateEligibility($assessment, $checklist);
        $suitability = $this->evaluateSuitability($assessment, $checklist);
        $funding = $this->verifyFunding($assessment, $checklist);
        $documents = $this->checkDocumentCollection($assessment);

        // Gather all issues
        $all_issues = array_merge(
            $eligibility['issues'],
            $funding['issues'],
            ! $documents['all_collected'] ? ['Required documents missing: '.implode(', ', array_map(fn ($d) => AssessmentDocument::getCategoryLabel($d), $documents['missing']))] : []
        );

        // Check if all requirements met
        $canApprove = $eligibility['eligible'] &&
                      $suitability['suitable'] &&
                      $funding['verified'] &&
                      $documents['all_collected'] &&
                      $checklist->participant_profile_completed &&
                      $checklist->care_plan_created &&
                      $checklist->support_plan_created &&
                      $checklist->budget_configured &&
                      $checklist->agreements_assigned;

        if ($canApprove) {
            return [
                'decision' => Assessment::DECISION_APPROVED,
                'approved' => true,
                'reasons' => ['All assessment requirements met'],
                'eligibility' => $eligibility,
                'suitability' => $suitability,
                'funding' => $funding,
                'documents' => $documents,
            ];
        } else {
            return [
                'decision' => Assessment::DECISION_PENDING,
                'approved' => false,
                'reasons' => $all_issues ?: ['Assessment incomplete'],
                'eligibility' => $eligibility,
                'suitability' => $suitability,
                'funding' => $funding,
                'documents' => $documents,
            ];
        }
    }

    /**
     * Get approval readiness percentage
     */
    public function getReadinessPercentage(Assessment $assessment): int
    {
        $checklist = $assessment->checklist;

        if (! $checklist) {
            return 0;
        }

        $checks = [
            $checklist->identity_confirmed,
            $checklist->contact_details_verified,
            $checklist->support_at_home_eligibility_confirmed,
            $checklist->program_eligibility_confirmed,
            $checklist->can_manage_workers,
            $checklist->funding_verified,
            $checklist->participant_profile_completed,
            $checklist->care_plan_created,
            $checklist->support_plan_created,
            $checklist->budget_configured,
            $checklist->referral_documents_collected,
            $checklist->agreements_assigned,
        ];

        $completed = count(array_filter($checks));
        $total = count($checks);

        return (int) (($completed / $total) * 100);
    }
}
