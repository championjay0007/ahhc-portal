<?php

namespace App\Http\Controllers;

use App\Models\OnboardingSubmission;
use App\Models\Participant;
use App\Services\NotificationCenterService;
use App\Services\TemplateMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function downloadDocument(OnboardingSubmission $submission, int $index)
    {
        $document = $submission->uploaded_documents[$index] ?? null;

        if (! $document || ! isset($document['path'])) {
            abort(404);
        }

        if (! Storage::disk('private')->exists($document['path'])) {
            abort(404);
        }

        return Storage::disk('private')->download(
            $document['path'], 
            $document['name'] ?? basename($document['path'])
        );
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
            'message' => 'Your onboarding has been approved. We are preparing your account for activation.',
        ], ['in_app']);

        try {
            $html = view('mail.onboarding-status', [
                'title' => 'Your onboarding is approved',
                'greeting' => 'Hello '.$participant->first_name.',',
                'intro' => 'Thank you for completing your onboarding details.',
                'body' => 'Your submission has been approved and your account is now being prepared for activation. You will receive a confirmation email as soon as access is ready.',
                'ctaLabel' => 'Return to portal',
                'ctaUrl' => route('portal.login'),
                'secondaryLabel' => 'Need help?',
                'secondaryUrl' => route('portal.login'),
                'organization' => config('app.name', 'AHHC Portal'),
            ])->render();

            TemplateMailer::send(
                $participant->email,
                'onboarding-status',
                [
                    'title' => 'Your onboarding is approved',
                    'greeting' => 'Hello '.$participant->first_name.',',
                    'intro' => 'Thank you for completing your onboarding details.',
                    'body' => 'Your submission has been approved and your account is now being prepared for activation. You will receive a confirmation email as soon as access is ready.',
                    'ctaLabel' => 'Return to portal',
                    'ctaUrl' => route('portal.login'),
                    'secondaryLabel' => 'Need help?',
                    'secondaryUrl' => route('portal.login'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'Your onboarding is approved',
                $html,
                strip_tags($html),
                'Onboarding Approved',
                'Onboarding'
            );
        } catch (\Throwable $e) {
            // Fail silently so the admin flow still completes.
        }

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
        ], ['in_app']);

        try {
            $html = view('mail.onboarding-status', [
                'title' => 'We need a few updates',
                'greeting' => 'Hello '.$submission->participant->first_name.',',
                'intro' => 'Your onboarding details have been reviewed.',
                'body' => 'Our team has requested a few updates before we can move your account forward. Please review the feedback and continue from the secure onboarding link below.',
                'ctaLabel' => 'Update onboarding',
                'ctaUrl' => route('portal.onboarding.show', ['token' => $submission->participant->onboarding_token]),
                'secondaryLabel' => 'Contact support',
                'secondaryUrl' => route('portal.login'),
                'organization' => config('app.name', 'AHHC Portal'),
            ])->render();

            TemplateMailer::send(
                $submission->participant->email,
                'onboarding-status',
                [
                    'title' => 'We need a few updates',
                    'greeting' => 'Hello '.$submission->participant->first_name.',',
                    'intro' => 'Your onboarding details have been reviewed.',
                    'body' => 'Our team has requested a few updates before we can move your account forward. Please review the feedback and continue from the secure onboarding link below.',
                    'ctaLabel' => 'Update onboarding',
                    'ctaUrl' => route('portal.onboarding.show', ['token' => $submission->participant->onboarding_token]),
                    'secondaryLabel' => 'Contact support',
                    'secondaryUrl' => route('portal.login'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'We need a few updates',
                $html,
                strip_tags($html),
                'Onboarding Changes Requested',
                'Onboarding'
            );
        } catch (\Throwable $e) {
            // Fail silently so the admin flow still completes.
        }

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
        ], ['in_app']);

        try {
            $html = view('mail.onboarding-status', [
                'title' => 'Your onboarding needs attention',
                'greeting' => 'Hello '.$submission->participant->first_name.',',
                'intro' => 'We have reviewed your onboarding submission.',
                'body' => 'At this time, we are unable to approve your onboarding. Please contact our support team for guidance on the next steps.',
                'ctaLabel' => 'Contact support',
                'ctaUrl' => route('portal.login'),
                'secondaryLabel' => 'Return to portal',
                'secondaryUrl' => route('portal.login'),
                'organization' => config('app.name', 'AHHC Portal'),
            ])->render();

            TemplateMailer::send(
                $submission->participant->email,
                'onboarding-status',
                [
                    'title' => 'Your onboarding needs attention',
                    'greeting' => 'Hello '.$submission->participant->first_name.',',
                    'intro' => 'We have reviewed your onboarding submission.',
                    'body' => 'At this time, we are unable to approve your onboarding. Please contact our support team for guidance on the next steps.',
                    'ctaLabel' => 'Contact support',
                    'ctaUrl' => route('portal.login'),
                    'secondaryLabel' => 'Return to portal',
                    'secondaryUrl' => route('portal.login'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'Your onboarding needs attention',
                $html,
                strip_tags($html),
                'Onboarding Rejected',
                'Onboarding'
            );
        } catch (\Throwable $e) {
            // Fail silently so the admin flow still completes.
        }

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

        return redirect()->route('portal.admin.participants.show', $participant)
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
