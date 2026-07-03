<?php

namespace App\Http\Controllers;

use App\Models\OnboardingSubmission;
use App\Models\Participant;
use App\Services\NotificationCenterService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminOnboardingController extends Controller
{
    /**
     * List all onboarding submissions
     */
    public function index(Request $request): View
    {
        $query = OnboardingSubmission::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $submissions = $query->latest('submitted_at')
            ->paginate(20);

        return view('admin.onboarding.index', compact('submissions'));
    }

    /**
     * Review onboarding submission
     */
    public function show(OnboardingSubmission $submission): View
    {
        $participant = $submission->participant;

        return view('admin.onboarding.show', compact('submission', 'participant'));
    }

    /**
     * Approve onboarding
     */
    public function approve(OnboardingSubmission $submission)
    {
        $participant = $submission->participant;

        // Verify all requirements are met
        if (! $this->validateOnboardingComplete($participant)) {
            return back()->with('error', 'Onboarding is not complete.');
        }

        // Update submission
        $submission->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Update participant
        $participant->update([
            'onboarding_status' => 'approved',
            'approved_at' => now(),
        ]);

        NotificationCenterService::send('portal_invitation', $participant->user_id, [
            'participant_id' => $participant->id,
            'url' => route('portal.login'),
            'message' => 'Your onboarding has been approved. AHHC will activate your account shortly.',
        ]);

        // TODO: Log audit trail

        return redirect()->route('admin.onboarding.show', $submission)
            ->with('success', 'Onboarding approved.');
    }

    /**
     * Request changes to onboarding
     */
    public function requestChanges(OnboardingSubmission $submission, Request $request)
    {
        $validated = $request->validate([
            'admin_comments' => ['required', 'string', 'max:1000'],
        ]);

        // Create new submission record with changes_requested status
        OnboardingSubmission::create([
            'participant_id' => $submission->participant_id,
            'personal_data' => $submission->personal_data,
            'support_person_data' => $submission->support_person_data,
            'uploaded_documents' => $submission->uploaded_documents,
            'signed_agreements' => $submission->signed_agreements,
            'status' => 'changes_requested',
            'admin_comments' => $validated['admin_comments'],
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Update participant status
        $submission->participant->update([
            'onboarding_status' => 'changes_requested',
            'onboarding_token' => Str::random(64),
            'onboarding_expires_at' => now()->addDays(14),
        ]);

        NotificationCenterService::send('portal_invitation', $submission->participant->user_id, [
            'participant_id' => $submission->participant->id,
            'url' => route('portal.onboarding.show', ['token' => $submission->participant->onboarding_token]),
            'message' => 'Changes were requested for your onboarding. Please review the comments and complete the updates.',
        ]);

        // TODO: Log audit trail

        return redirect()->route('admin.onboarding.index')
            ->with('success', 'Changes requested. Participant notified.');
    }

    /**
     * Reject onboarding
     */
    public function reject(OnboardingSubmission $submission, Request $request)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $submission->update([
            'status' => 'rejected',
            'admin_comments' => $validated['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        $submission->participant->update([
            'onboarding_status' => 'rejected',
            'onboarding_token' => null,
            'onboarding_expires_at' => null,
        ]);

        NotificationCenterService::send('portal_invitation', $submission->participant->user_id, [
            'participant_id' => $submission->participant->id,
            'url' => route('portal.login'),
            'message' => 'Your onboarding has been rejected. Please contact AHHC support for next steps.',
        ]);

        // TODO: Log audit trail

        return redirect()->route('admin.onboarding.index')
            ->with('success', 'Onboarding rejected.');
    }

    /**
     * Activate approved participant (give full portal access)
     */
    public function activate(Participant $participant)
    {
        if ($participant->onboarding_status !== 'approved') {
            return back()->with('error', 'Onboarding is not approved.');
        }

        $participant->update([
            'status' => 'active',
            'onboarding_status' => 'activated',
            'activated_at' => now(),
        ]);

        $participant->user->update([
            'status' => 'active',
        ]);

        // TODO: Send activation notification
        // TODO: Log audit trail

        return redirect()->route('admin.participants.show', $participant)
            ->with('success', 'Participant activated.');
    }

    /**
     * Validate onboarding is complete
     */
    private function validateOnboardingComplete(Participant $participant): bool
    {
        $submission = $participant->latestOnboardingSubmission();

        if (! $submission || ! $submission->personal_data) {
            return false;
        }

        if (! $submission->uploaded_documents || empty($submission->uploaded_documents)) {
            return false;
        }

        if (! $participant->hasSignedAllRequiredAgreements()) {
            return false;
        }

        return true;
    }
}
