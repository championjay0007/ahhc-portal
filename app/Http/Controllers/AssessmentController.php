<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentNote;
use App\Models\Participant;
use App\Models\User;
use App\Services\AssessmentService;
use App\Services\DecisionEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    protected AssessmentService $assessmentService;

    protected DecisionEngine $decisionEngine;

    public function __construct()
    {
        $this->assessmentService = new AssessmentService;
        $this->decisionEngine = new DecisionEngine;
        $this->middleware('auth');
        $this->middleware('role:admin,ahhc_staff');
    }

    /**
     * Display assessment dashboard
     */
    public function dashboard(): View
    {
        $stats = [
            'new_applications' => Assessment::newApplications()->count(),
            'under_review' => Assessment::underReview()->count(),
            'awaiting_information' => Assessment::awaitingInformation()->count(),
            'ready_for_approval' => Assessment::canReceiveInvitation()->count(),
            'approved' => Assessment::approved()->count(),
            'rejected' => Assessment::rejected()->count(),
            'active_participants' => Assessment::active()->count(),
        ];

        $new_applications = Assessment::newApplications()
            ->with('createdByUser', 'assignedToUser')
            ->latest()
            ->limit(10)
            ->get();

        $under_review = Assessment::underReview()
            ->with('createdByUser', 'assignedToUser')
            ->latest()
            ->limit(10)
            ->get();

        $awaiting_info = Assessment::awaitingInformation()
            ->with('createdByUser', 'assignedToUser')
            ->latest()
            ->limit(5)
            ->get();

        $ready_for_activation = Assessment::readyForActivation()
            ->with('createdByUser', 'participant')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.assessments.dashboard', compact(
            'stats',
            'new_applications',
            'under_review',
            'awaiting_info',
            'ready_for_activation'
        ));
    }

    /**
     * Show assessment detail page
     */
    public function show(Assessment $assessment): View
    {
        $assessment->load(
            'participant',
            'assignedToUser',
            'createdByUser',
            'approvedByUser',
            'rejectedByUser',
            'activatedByUser',
            'checklist',
            'notes',
            'documents',
            'statusHistory',
            'budgetSetups'
        );

        $readiness = $this->decisionEngine->getReadinessPercentage($assessment);
        $decision = $this->decisionEngine->makeDecision($assessment);

        $status_history = $assessment->statusHistory()
            ->with('changedByUser')
            ->latest()
            ->get();

        return view('admin.assessments.show', compact(
            'assessment',
            'readiness',
            'decision',
            'status_history'
        ));
    }

    /**
     * Assign assessment to reviewer
     */
    public function assign(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate(['user_id' => 'required|exists:users,id']);

        $reviewer = User::findOrFail($request->user_id);
        $this->assessmentService->assignAssessment(
            $assessment,
            $reviewer,
            auth()->user(),
            $request->ip()
        );

        return redirect()->back()->with('success', "Assessment assigned to {$reviewer->name}");
    }

    /**
     * Show assessment review form
     */
    public function review(Assessment $assessment): View
    {
        $assessment->load('checklist', 'documents', 'notes');
        $readiness = $this->decisionEngine->getReadinessPercentage($assessment);

        return view('admin.assessments.review', compact(
            'assessment',
            'readiness'
        ));
    }

    /**
     * Complete eligibility assessment
     */
    public function completeEligibilityAssessment(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate([
            'identity_confirmed' => 'boolean',
            'contact_details_verified' => 'boolean',
            'address_verified' => 'boolean',
            'support_at_home_eligibility_confirmed' => 'boolean',
            'program_eligibility_confirmed' => 'boolean',
            'eligibility_notes' => 'string|nullable',
        ]);

        $checklist = $assessment->checklist;
        $checklist->update($request->only([
            'identity_confirmed',
            'contact_details_verified',
            'address_verified',
            'support_at_home_eligibility_confirmed',
            'program_eligibility_confirmed',
        ]));

        if ($request->eligibility_notes) {
            $this->assessmentService->addNote(
                $assessment,
                $request->eligibility_notes,
                auth()->user(),
                AssessmentNote::NOTE_TYPE_ELIGIBILITY,
                true,
                false,
                $request->ip()
            );
        }

        $checklist->calculateCompletion();

        return redirect()->back()->with('success', 'Eligibility assessment completed');
    }

    /**
     * Complete suitability assessment
     */
    public function completeSuitabilityAssessment(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate([
            'can_manage_workers' => 'boolean',
            'can_make_service_decisions' => 'boolean',
            'can_approve_invoices' => 'boolean',
            'can_review_spending' => 'boolean',
            'understands_responsibilities' => 'boolean',
            'suitability_notes' => 'string|nullable',
        ]);

        $checklist = $assessment->checklist;
        $checklist->update($request->only([
            'can_manage_workers',
            'can_make_service_decisions',
            'can_approve_invoices',
            'can_review_spending',
            'understands_responsibilities',
        ]));

        if ($request->suitability_notes) {
            $this->assessmentService->addNote(
                $assessment,
                $request->suitability_notes,
                auth()->user(),
                AssessmentNote::NOTE_TYPE_SUITABILITY,
                true,
                false,
                $request->ip()
            );
        }

        // Check if support person needed
        $suitability = $this->decisionEngine->evaluateSuitability($assessment, $checklist);
        if ($suitability['requires_support']) {
            $assessment->update(['support_person_required' => true]);
        }

        $checklist->calculateCompletion();

        return redirect()->back()->with('success', 'Suitability assessment completed');
    }

    /**
     * Complete funding verification
     */
    public function completeFundingVerification(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate([
            'funding_source' => 'string|required',
            'funding_type' => 'string|required',
            'budget_allocation' => 'numeric|required|min:0',
            'funding_verified' => 'boolean',
            'funding_documentation_received' => 'boolean',
            'budget_confirmed' => 'boolean',
            'funding_notes' => 'string|nullable',
        ]);

        $assessment->update([
            'funding_source' => $request->funding_source,
            'funding_type' => $request->funding_type,
            'budget_allocation' => $request->budget_allocation,
        ]);

        $checklist = $assessment->checklist;
        $checklist->update([
            'funding_verified' => $request->boolean('funding_verified'),
            'funding_documentation_received' => $request->boolean('funding_documentation_received'),
            'budget_confirmed' => $request->boolean('budget_confirmed'),
        ]);

        if ($request->funding_notes) {
            $this->assessmentService->addNote(
                $assessment,
                $request->funding_notes,
                auth()->user(),
                AssessmentNote::NOTE_TYPE_FUNDING,
                true,
                false,
                $request->ip()
            );
        }

        $checklist->calculateCompletion();

        return redirect()->back()->with('success', 'Funding verification completed');
    }

    /**
     * Add assessment note
     */
    public function addNote(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate([
            'note_text' => 'string|required|min:3',
            'note_type' => 'string|in:general,eligibility,suitability,funding,decision,information_request',
            'is_internal' => 'boolean',
            'requires_action' => 'boolean',
        ]);

        $this->assessmentService->addNote(
            $assessment,
            $request->note_text,
            auth()->user(),
            $request->note_type,
            $request->boolean('is_internal'),
            $request->boolean('requires_action'),
            $request->ip()
        );

        return redirect()->back()->with('success', 'Note added');
    }

    /**
     * Request information from participant
     */
    public function requestInformation(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate([
            'information_needed' => 'string|required|min:10',
        ]);

        $this->assessmentService->requestInformation(
            $assessment,
            $request->information_needed,
            auth()->user(),
            $request->ip()
        );

        // TODO: Send notification to participant

        return redirect()->back()->with('success', 'Information request sent to participant');
    }

    /**
     * Show approval form
     */
    public function approvalForm(Assessment $assessment): View
    {
        $assessment->load('checklist');
        $decision = $this->decisionEngine->makeDecision($assessment);
        $readiness = $this->decisionEngine->getReadinessPercentage($assessment);

        return view('admin.assessments.approve', compact(
            'assessment',
            'decision',
            'readiness'
        ));
    }

    /**
     * Approve assessment
     */
    public function approve(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate([
            'approval_notes' => 'string|nullable',
        ]);

        try {
            $this->assessmentService->approveAssessment(
                $assessment,
                auth()->user(),
                $request->approval_notes ?? '',
                $request->ip()
            );

            // TODO: Send approval notification

            return redirect()->route('admin.assessments.show', $assessment)
                ->with('success', 'Assessment approved successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show rejection form
     */
    public function rejectionForm(Assessment $assessment): View
    {
        return view('admin.assessments.reject', compact('assessment'));
    }

    /**
     * Reject assessment
     */
    public function reject(Assessment $assessment, Request $request): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => 'string|required|min:20',
        ]);

        try {
            $this->assessmentService->rejectAssessment(
                $assessment,
                auth()->user(),
                $request->rejection_reason,
                $request->ip()
            );

            // TODO: Send rejection notification

            return redirect()->route('admin.assessments.dashboard')
                ->with('success', 'Assessment rejected');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Send invitation
     */
    public function sendInvitation(Assessment $assessment, Request $request): RedirectResponse
    {
        try {
            if (! $assessment->canReceiveInvitation()) {
                throw new \Exception('Assessment is not eligible for invitation');
            }

            $this->assessmentService->generateInvitationToken(
                $assessment,
                auth()->user(),
                $request->ip()
            );

            // TODO: Send invitation email with token

            return redirect()->back()->with('success', 'Invitation sent to participant');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Complete final review and activate
     */
    public function activate(Assessment $assessment, Request $request): RedirectResponse
    {
        try {
            $this->assessmentService->activateParticipant(
                $assessment,
                auth()->user(),
                $request->ip()
            );

            return redirect()->back()->with('success', 'Participant portal activated');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * View status history
     */
    public function statusHistory(Assessment $assessment): View
    {
        $history = $assessment->statusHistory()
            ->with('changedByUser')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.assessments.status-history', compact('assessment', 'history'));
    }
}
