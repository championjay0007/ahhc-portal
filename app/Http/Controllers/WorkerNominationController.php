<?php

namespace App\Http\Controllers;

use App\Enums\WorkerNominationStatus;
use App\Mail\WorkerOnboardingInvitation;
use App\Models\Participant;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerNomination;
use App\Notifications\WorkerInvitationSent;
use App\Notifications\WorkerNominationApproved;
use App\Notifications\WorkerNominationRejected;
use App\Notifications\WorkerNominationSubmitted;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WorkerNominationController extends Controller
{
    /**
     * Display list of nominations (participant view).
     */
    public function index()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $nominations = WorkerNomination::byParticipant($participant)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('portal.participant.nominations.index', compact('participant', 'nominations'));
    }

    /**
     * Show nomination creation form.
     */
    public function create()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        return view('portal.participant.nominations.create', compact('participant'));
    }

    /**
     * Store nomination from participant.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'worker_full_name' => ['required', 'string', 'max:255'],
            'worker_email' => ['required', 'email', 'max:255'],
            'worker_phone' => ['required', 'string', 'max:20'],
            'worker_address' => ['nullable', 'string', 'max:500'],
            'worker_type' => ['required', Rule::in(['Independent', 'Mable', 'Supplier', 'Therapist', 'Other'])],
            'service_type' => ['required', 'string', 'max:255'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0', 'max:999'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'start_date' => ['nullable', 'date', 'after:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        // Handle document uploads
        $uploadedDocuments = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $path = $document->store('nominations/'.$participant->id, 'private');
                $uploadedDocuments[] = [
                    'path' => $path,
                    'name' => $document->getClientOriginalName(),
                    'type' => $document->getMimeType(),
                    'uploaded_at' => now()->toDateTimeString(),
                ];
            }
        }

        $nomination = WorkerNomination::create([
            ...$validated,
            'participant_id' => $participant->id,
            'uploaded_documents' => $uploadedDocuments ?: null,
            'status' => WorkerNominationStatus::Submitted,
        ]);

        // Notify all admins about the new nomination
        $admins = User::whereIn('role', ['admin', 'system_admin'])->where('status', 'active')->get();

        foreach ($admins as $admin) {
            // Send email notification
            Notification::send($admin, new WorkerNominationSubmitted($nomination));

            // Create in-app notification
            NotificationService::notify([
                'user_id' => $admin->id,
                'title' => 'New Worker Nomination Submitted',
                'message' => $participant->user->name.' has submitted a nomination for '.$nomination->worker_full_name,
                'type' => 'info',
                'channel' => 'in_app',
                'data' => [
                    'nomination_id' => $nomination->id,
                    'participant_id' => $nomination->participant_id,
                    'url' => route('portal.admin.nominations.show', $nomination->id),
                ],
                'action_url' => route('portal.admin.nominations.show', $nomination->id),
            ]);
        }

        return redirect()->route('portal.participant.nominations.show', $nomination)
            ->with('status', 'Worker nomination submitted successfully. AHHC will review it shortly.');
    }

    /**
     * Show nomination details.
     */
    public function show(WorkerNomination $nomination)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($nomination->participant_id !== $participant->id) {
            abort(403, 'Unauthorized');
        }

        return view('portal.participant.nominations.show', compact('nomination', 'participant'));
    }

    /**
     * Destroy nomination (participant can only delete if not under review).
     */
    public function destroy(WorkerNomination $nomination)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($nomination->participant_id !== $participant->id) {
            abort(403, 'Unauthorized');
        }

        if (! in_array($nomination->status, [WorkerNominationStatus::Submitted, WorkerNominationStatus::Rejected])) {
            return back()->withErrors('Cannot delete this nomination at this stage.');
        }

        $nomination->delete();

        return redirect()->route('portal.participant.nominations.index')
            ->with('status', 'Nomination deleted.');
    }

    // ===== ADMIN METHODS =====

    /**
     * Display all nominations (admin view).
     */
    public function adminIndex()
    {
        $this->authorize('viewAny', WorkerNomination::class);

        $nominations = WorkerNomination::with(['participant', 'approvedBy', 'rejectedBy'])
            ->when(request('status'), fn ($q) => $q->where('status', request('status')))
            ->when(request('search'), fn ($q) => $q->where('worker_email', 'like', '%'.request('search').'%')
                ->orWhere('worker_full_name', 'like', '%'.request('search').'%'))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statuses = WorkerNominationStatus::cases();

        return view('admin.nominations.index', compact('nominations', 'statuses'));
    }

    /**
     * Show nomination detail (admin view).
     */
    public function adminShow(WorkerNomination $nomination)
    {
        $this->authorize('view', $nomination);

        return view('admin.nominations.show', compact('nomination'));
    }

    /**
     * Approve nomination.
     */
    public function approve(WorkerNomination $nomination, Request $request)
    {
        $this->authorize('approve', $nomination);

        if (! $nomination->canBeApproved()) {
            return back()->withErrors('This nomination cannot be approved at this stage.');
        }

        $notes = $request->input('notes');

        $nomination->updateStatus(
            WorkerNominationStatus::Approved,
            $notes,
            Auth::id()
        );

        $nomination->refresh();

        // Send approval email to participant
        Notification::send($nomination->participant->user, new WorkerNominationApproved($nomination));

        // Create in-app notification for participant
        NotificationService::notify([
            'user_id' => $nomination->participant->user_id,
            'title' => 'Worker Nomination Approved',
            'message' => 'Your nomination for '.$nomination->worker_full_name.' has been approved!',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => [
                'nomination_id' => $nomination->id,
                'action' => 'approved',
            ],
            'action_url' => route('portal.participant.nominations.show', $nomination->id),
        ]);

        // Auto-invite worker using the onboarding workflow and move status to WorkerInvited
        try {
            $this->sendWorkerOnboardingInvitation($nomination);

            $nomination->updateStatus(
                WorkerNominationStatus::WorkerInvited,
                null,
                Auth::id()
            );

            Notification::send($nomination->participant->user, new WorkerInvitationSent($nomination));
            NotificationService::notify([
                'user_id' => $nomination->participant->user_id,
                'title' => 'Worker Invitation Sent',
                'message' => 'An AHHC onboarding invitation has been sent to '.$nomination->worker_full_name,
                'type' => 'info',
                'channel' => 'in_app',
                'data' => [
                    'nomination_id' => $nomination->id,
                    'action' => 'worker_invited',
                ],
                'action_url' => route('portal.participant.nominations.show', $nomination->id),
            ]);
        } catch (\Exception $e) {
            session()->flash('warning', 'Approval saved but onboarding invitation failed: '.$e->getMessage());
        }

        return back()->with('status', 'Nomination approved successfully.');
    }

    private function ensureWorkerOnboardingRecord(WorkerNomination $nomination): Worker
    {
        $worker = Worker::where('email', $nomination->worker_email)->first();
        $user = User::where('email', $nomination->worker_email)->first();

        if ($user && $user->role !== 'worker') {
            $user->role = 'worker';
            $user->save();
        }

        $token = Str::random(32);
        $expiresAt = now()->addDays(30);

        if (! $worker) {
            [$firstName, $lastName] = preg_split('/\s+/', trim($nomination->worker_full_name ?? ''), 2) + [null, null];

            return Worker::create([
                'user_id' => $user?->id,
                'worker_number' => 'W-'.strtoupper(Str::random(8)),
                'first_name' => $firstName ?? 'Worker',
                'last_name' => $lastName ?? 'Worker',
                'phone' => $nomination->worker_phone,
                'email' => $nomination->worker_email,
                'role_type' => $nomination->worker_type ?? 'worker',
                'status' => 'pending',
                'onboarding_stage' => 1,
                'onboarding_token' => $token,
                'onboarding_expires_at' => $expiresAt,
                'invited_by_id' => Auth::id(),
                'invited_at' => now(),
            ]);
        }

        $updates = [];

        if (! $worker->onboarding_token || $worker->onboarding_expires_at?->isPast()) {
            $updates['onboarding_token'] = $token;
            $updates['onboarding_expires_at'] = $expiresAt;
        }

        if ($worker->status !== 'pending') {
            $updates['status'] = 'pending';
        }

        if ($worker->onboarding_stage < 1) {
            $updates['onboarding_stage'] = 1;
        }

        if (! isset($worker->invited_by_id)) {
            $updates['invited_by_id'] = Auth::id();
            $updates['invited_at'] = now();
        }

        if (! empty($updates)) {
            $worker->update($updates);
            $worker->refresh();
        }

        return $worker;
    }

    private function sendWorkerOnboardingInvitation(WorkerNomination $nomination): Worker
    {
        $worker = $this->ensureWorkerOnboardingRecord($nomination);

        Mail::to($nomination->worker_email)->send(new WorkerOnboardingInvitation($worker));

        return $worker;
    }

    /**
     * Reject nomination.
     */
    public function reject(WorkerNomination $nomination, Request $request)
    {
        $this->authorize('reject', $nomination);

        if (! $nomination->canBeRejected()) {
            return back()->withErrors('This nomination cannot be rejected at this stage.');
        }

        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $nomination->updateStatus(
            WorkerNominationStatus::Rejected,
            $request->input('rejection_reason'),
            Auth::id()
        );

        // Send rejection email to participant
        Notification::send($nomination->participant->user, new WorkerNominationRejected($nomination));

        // Create in-app notification for participant
        NotificationService::notify([
            'user_id' => $nomination->participant->user_id,
            'title' => 'Worker Nomination Rejected',
            'message' => 'Your nomination for '.$nomination->worker_full_name.' could not be approved.',
            'type' => 'warning',
            'channel' => 'in_app',
            'data' => [
                'nomination_id' => $nomination->id,
                'action' => 'rejected',
                'reason' => $nomination->rejection_reason,
            ],
            'action_url' => route('portal.participant.nominations.show', $nomination->id),
        ]);

        return back()->with('status', 'Nomination rejected.');
    }

    /**
     * Send invitation to worker.
     */
    public function inviteWorker(WorkerNomination $nomination)
    {
        $this->authorize('inviteWorker', $nomination);

        if (! $nomination->canSendInvitation()) {
            return back()->withErrors('Worker can only be invited after approval.');
        }

        try {
            $this->sendWorkerOnboardingInvitation($nomination);

            $nomination->updateStatus(
                WorkerNominationStatus::WorkerInvited,
                null,
                Auth::id()
            );

            Notification::send($nomination->participant->user, new WorkerInvitationSent($nomination));

            NotificationService::notify([
                'user_id' => $nomination->participant->user_id,
                'title' => 'Worker Invitation Sent',
                'message' => 'An AHHC onboarding invitation has been sent to '.$nomination->worker_full_name,
                'type' => 'info',
                'channel' => 'in_app',
                'data' => [
                    'nomination_id' => $nomination->id,
                    'action' => 'worker_invited',
                ],
                'action_url' => route('portal.participant.nominations.show', $nomination->id),
            ]);

            return back()->with('status', 'Invitation sent to worker.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to send invitation: '.$e->getMessage());
        }
    }

    /**
     * Resend the onboarding invitation email for a nomination.
     * Allows admin to resend regardless of nomination approval state.
     */
    public function resendInvitation(WorkerNomination $nomination)
    {
        $this->authorize('inviteWorker', $nomination);

        try {
            $this->sendWorkerOnboardingInvitation($nomination);

            // Ensure nomination status reflects that an invitation was sent
            $nomination->updateStatus(
                WorkerNominationStatus::WorkerInvited,
                null,
                Auth::id()
            );

            NotificationService::notify([
                'user_id' => $nomination->participant->user_id,
                'title' => 'Worker Invitation Resent',
                'message' => 'An AHHC onboarding invitation has been resent to '.$nomination->worker_full_name,
                'type' => 'info',
                'channel' => 'in_app',
                'data' => [
                    'nomination_id' => $nomination->id,
                    'action' => 'worker_invited',
                ],
                'action_url' => route('portal.participant.nominations.show', $nomination->id),
            ]);

            // Record audit log for resend action
            AuditLogService::record('Worker Invitation Resent', $nomination, [], [
                'sent_to' => $nomination->worker_email,
                'sent_by' => Auth::id(),
            ]);

            return back()->with('status', 'Invitation resent to worker.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to resend invitation: '.$e->getMessage());
        }
    }

    /**
     * Update nomination status (general endpoint).
     */
    public function updateStatus(WorkerNomination $nomination, Request $request)
    {
        $this->authorize('update', $nomination);

        $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $newStatus = WorkerNominationStatus::tryFrom($request->input('status'));
            if (! $newStatus) {
                return back()->withErrors('Invalid status.');
            }

            $nomination->updateStatus(
                $newStatus,
                $request->input('notes'),
                Auth::id()
            );

            return back()->with('status', 'Status updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Update failed: '.$e->getMessage());
        }
    }

    /**
     * Activate the worker account created from a nomination and link it.
     */
    public function activate(WorkerNomination $nomination)
    {
        $this->authorize('approve', $nomination);

        // Only allow activation for nominations that have progressed to an invite/completion state
        if (! in_array($nomination->status, [WorkerNominationStatus::WorkerInvited, WorkerNominationStatus::CompliancePending, WorkerNominationStatus::PendingSignature, WorkerNominationStatus::Approved])) {
            return back()->withErrors('Nomination cannot be activated at this stage.');
        }

        // Find the user by the nominated worker email
        $user = User::where('email', $nomination->worker_email)->first();

        if (! $user) {
            return back()->withErrors('No user account found for the nominated worker email. The worker must register first.');
        }

        // Ensure the user is a worker role and active
        $userNeedsSave = false;

        if ($user->role !== 'worker') {
            $user->role = 'worker';
            $userNeedsSave = true;
        }

        if ($user->status !== 'active') {
            $user->status = 'active';
            $userNeedsSave = true;
        }

        if ($userNeedsSave) {
            $user->save();
        }

        // Create or update the worker profile and mark as active
        $worker = $user->worker ?? Worker::where('email', $nomination->worker_email)->first();

        if ($worker) {
            $updates = ['status' => 'active'];

            if ($worker->user_id !== $user->id) {
                $updates['user_id'] = $user->id;
            }

            if (! $worker->worker_number) {
                $updates['worker_number'] = 'W-'.$user->id;
            }

            $worker->update($updates);
        } else {
            $names = preg_split('/\s+/', trim($nomination->worker_full_name ?? ($user->name ?? '')), 2);
            $first = $names[0] ?? 'Worker';
            $last = $names[1] ?? 'Worker';

            $worker = Worker::create([
                'user_id' => $user->id,
                'worker_number' => 'W-'.$user->id,
                'first_name' => $first,
                'last_name' => $last,
                'phone' => $nomination->worker_phone ?? $user->phone,
                'email' => $user->email,
                'role_type' => $nomination->worker_type ?? 'worker',
                'status' => 'active',
                'invited_by_id' => Auth::id(),
                'invited_at' => now(),
            ]);
        }

        // Link nomination state to Active (force update regardless of transition rules)
        $nomination->status = WorkerNominationStatus::Active;
        $nomination->save();

        // Notify participant and worker
        NotificationService::notify([
            'user_id' => $nomination->participant->user_id,
            'title' => 'Worker Activated',
            'message' => $nomination->worker_full_name.' has been activated and is ready for assignment.',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => ['nomination_id' => $nomination->id],
            'action_url' => route('portal.participant.nominations.show', $nomination->id),
        ]);

        NotificationService::notify([
            'user_id' => $user->id,
            'title' => 'Your account is active',
            'message' => 'Your worker account has been activated by AHHC. You can now sign in and access the worker dashboard.',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => ['nomination_id' => $nomination->id],
            'action_url' => route('portal.worker.dashboard'),
        ]);

        return back()->with('status', 'Worker activated and linked to nomination.');
    }
}
