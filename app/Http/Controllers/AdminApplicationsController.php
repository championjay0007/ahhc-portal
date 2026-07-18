<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\User;
use App\Services\NotificationCenterService;
use App\Services\TemplateMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminApplicationsController extends Controller
{
    /**
     * List all applications
     */
    public function index(Request $request): View
    {
        $query = ParticipantApplication::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->latest('submitted_at')
            ->paginate(20);

        return view('admin.applications.index', compact('applications'));
    }

    /**
     * Show application details
     */
    public function show(ParticipantApplication $application): View
    {
        return view('admin.applications.show', compact('application'));
    }

    /**
     * Approve application and send onboarding invitation
     */
    public function approve(ParticipantApplication $application)
    {
        // Update application status
        $application->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Create participant from application
        $user = User::create([
            'name' => "{$application->first_name} {$application->last_name}",
            'email' => $application->email,
            'phone' => $application->phone,
            'password' => bcrypt(Str::random(16)), // Temporary password
            'role' => 'participant',
            'status' => 'inactive', // Stays inactive until onboarding approved
        ]);

        $onboardingToken = Str::random(64);
        $participant = Participant::create([
            'user_id' => $user->id,
            'application_id' => $application->id,
            'first_name' => $application->first_name,
            'last_name' => $application->last_name,
            'email' => $application->email,
            'phone' => $application->phone,
            'address' => $application->address,
            'city' => $application->city,
            'state' => $application->state,
            'postcode' => $application->postcode,
            'status' => 'inactive',
            'onboarding_status' => 'invitation_sent',
            'onboarding_token' => $onboardingToken,
            'onboarding_expires_at' => now()->addDays(14),
        ]);

        // Assign default required agreements to participant
        $requiredAgreementIds = Agreement::where('is_required', true)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        if (! empty($requiredAgreementIds)) {
            $participant->agreements()->syncWithoutDetaching($requiredAgreementIds);
        }

        // Send onboarding invitation email
        $html = view('mail.participant-onboarding-invitation', ['participant' => $participant])->render();
        try {
            TemplateMailer::send(
                $participant->email,
                'participant-onboarding-invitation',
                [
                    'name' => trim($participant->first_name.' '.$participant->last_name),
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'email' => $participant->email,
                    'onboarding_url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
                    'expires_at' => optional($participant->onboarding_expires_at)->format('d M Y H:i') ?? now()->addDays(30)->format('d M Y H:i'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'Complete your AHHC portal onboarding',
                $html,
                strip_tags($html),
                'Participant Onboarding Invitation',
                'Onboarding'
            );
        } catch (\Throwable $e) {
            // TemplateMailer already handles fallback delivery. Keep this as best-effort only.
        }

        NotificationCenterService::send('portal_invitation', $user->id, [
            'participant_id' => $participant->id,
            'url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
            'message' => 'Your onboarding invitation has been sent. Click the link to complete your onboarding.',
        ], ['in_app']);

        // Log audit trail
        // TODO: AuditLogService::record(...)

        return redirect()->route('admin.applications.show', $application)
            ->with('success', 'Application approved and onboarding invitation sent.');
    }

    /**
     * Reject application
     */
    public function reject(ParticipantApplication $application, Request $request)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $application->update([
            'status' => 'rejected',
            'rejected_reason' => $validated['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        // Send rejection email
        // TODO: Send rejection notification to applicant

        // Log audit trail
        // TODO: AuditLogService::record(...)

        return redirect()->route('admin.applications.index')
            ->with('success', 'Application rejected.');
    }

    /**
     * Mark application as under review
     */
    public function markUnderReview(ParticipantApplication $application)
    {
        $application->update([
            'status' => 'under_review',
            'reviewed_by' => auth()->id(),
        ]);

        return back()->with('success', 'Application marked as under review.');
    }
}
