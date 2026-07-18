<?php

namespace App\Http\Controllers;

use App\Enums\ComplianceStatus;
use App\Enums\WorkerDeclarationType;
use App\Enums\WorkerNominationStatus;
use App\Models\Participant;
use App\Models\ParticipantAssignment;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Models\WorkerComplianceType;
use App\Models\WorkerDeclaration;
use App\Models\WorkerNomination;
use App\Models\WorkerServiceApproval;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use App\Services\TemplateMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AdminWorkerOnboardingController extends Controller
{
    /**
     * List all workers with their onboarding status
     */
    public function index()
    {
        $workers = Worker::with('user', 'invitedBy', 'stage3Reviewer', 'stage5Approver')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.worker_onboarding.index', [
            'workers' => $workers,
        ]);
    }

    /**
     * Show worker onboarding details and management
     */
    public function show(Worker $worker)
    {
        $worker->load([
            'user',
            'invitedBy',
            'stage2Reviewer',
            'stage3Reviewer',
            'stage5Approver',
            'stage6Assignor',
            'complianceDocuments',
            'declarations',
            'serviceApprovals',
            'assignments.participant',
        ]);

        $stage = $worker->getCurrentStage();

        $nominatedParticipantIds = WorkerNomination::where('worker_email', $worker->email)
            ->whereIn('status', [
                WorkerNominationStatus::Approved->value,
                WorkerNominationStatus::WorkerInvited->value,
                WorkerNominationStatus::CompliancePending->value,
                WorkerNominationStatus::PendingSignature->value,
                WorkerNominationStatus::Active->value,
                WorkerNominationStatus::Assigned->value,
            ])
            ->pluck('participant_id')
            ->unique()
            ->toArray();

        $participants = Participant::where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->sortByDesc(fn ($participant) => in_array($participant->id, $nominatedParticipantIds))
            ->values();

        $selectedParticipantIds = array_unique(array_merge(
            $worker->assignments()->where('status', 'active')->pluck('participant_id')->toArray(),
            $nominatedParticipantIds
        ));

        return view('admin.worker_onboarding.show', [
            'worker' => $worker,
            'stage' => $stage,
            'declarations' => WorkerDeclarationType::all(),
            'participants' => $participants,
            'nominatedParticipantIds' => $nominatedParticipantIds,
            'selectedParticipantIds' => $selectedParticipantIds,
        ]);
    }

    /**
     * Invite a new worker (Stage 1)
     */
    public function inviteWorker(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:workers,email|unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'role_type' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, &$worker) {
            $token = Str::random(32);

            $worker = Worker::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'role_type' => $validated['role_type'],
                'worker_number' => 'W-'.Str::random(8),
                'onboarding_stage' => 1,
                'onboarding_token' => $token,
                'onboarding_expires_at' => now()->addDays(30),
                'invited_by_id' => Auth::id(),
                'invited_at' => now(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        // Send invitation email through the admin-managed template system, with the existing mailable as fallback.
        try {
            TemplateMailer::send(
                $worker->email,
                'worker-onboarding-invitation',
                [
                    'name' => trim($worker->first_name.' '.$worker->last_name),
                    'first_name' => $worker->first_name,
                    'last_name' => $worker->last_name,
                    'email' => $worker->email,
                    'phone' => $worker->phone,
                    'worker_number' => $worker->worker_number,
                    'onboarding_url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
                    'expires_at' => optional($worker->onboarding_expires_at)->format('M d, Y') ?? now()->addDays(30)->format('M d, Y'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'AHHC Portal - Worker Onboarding Invitation',
                view('mail.worker_onboarding_invitation', ['worker' => $worker, 'onboardingUrl' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]), 'expiresAt' => $worker->onboarding_expires_at])->render(),
                strip_tags(view('mail.worker_onboarding_invitation', ['worker' => $worker, 'onboardingUrl' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]), 'expiresAt' => $worker->onboarding_expires_at])->render()),
                'Worker Onboarding Invitation',
                'Onboarding'
            );
        } catch (\Throwable $e) {
            // TemplateMailer already handles fallback delivery. Keep this as best-effort only.
        }

        return redirect()->route('admin.worker_onboarding.show', $worker)
            ->with('success', 'Worker invited successfully. Invitation sent to '.$worker->email);
    }

    /**
     * Move worker to Stage 2 (Compliance Upload)
     */
    public function advanceToStage2(Worker $worker)
    {
        if ($worker->onboarding_stage !== 1 || ! $worker->user || ! $worker->user->mfa_enabled) {
            return back()->withErrors(['Worker must complete MFA setup first.']);
        }

        $worker->update([
            'stage_1_completed_at' => now(),
            'onboarding_stage' => 2,
        ]);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Onboarding Updated: Stage 2',
            'message' => 'Your worker onboarding has moved to Stage 2. Please upload your compliance documents to continue.',
            'type' => 'info',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
            ],
        ]);

        return back()->with('success', 'Worker advanced to Stage 2 (Compliance Upload).');
    }

    /**
     * Approve Stage 2 compliance documents and move to Stage 3
     */
    public function approveStage2(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 2) {
            return back()->withErrors(['Worker must be in Stage 2.']);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Ensure required compliance types exist and are not missing/rejected
        WorkerComplianceType::ensureDefaults();

        $requiredTypes = WorkerComplianceType::where('is_required', true)->pluck('name')->toArray();
        $missing = [];

        foreach ($requiredTypes as $typeName) {
            $doc = $worker->complianceDocuments()->where('document_type', $typeName)->first();

            if (! $doc) {
                $missing[] = $typeName;

                continue;
            }

            if (in_array($doc->status, [ComplianceStatus::MISSING->value, ComplianceStatus::REJECTED->value], true)) {
                $missing[] = $typeName;
            }
        }

        if (! empty($missing)) {
            return back()->withErrors(['documents' => 'Required compliance documents missing or rejected: '.implode(', ', $missing)]);
        }

        $worker->update([
            'stage_2_completed_at' => now(),
            'stage_2_reviewer_id' => Auth::id(),
            'onboarding_stage' => 3,
            'stage_3_submitted_at' => now(),
            'notes' => $validated['notes'] ?? $worker->notes,
        ]);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Stage 2 Approved',
            'message' => 'Your compliance documents have been approved. You can now continue with document review and declarations.',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
            ],
        ]);

        return redirect()->route('admin.worker_onboarding.show', $worker)
            ->with('success', 'Stage 2 approved. Worker advanced to Stage 3 (Document Review).');
    }

    /**
     * Reject Stage 2 and request resubmission
     */
    public function rejectStage2(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 2) {
            return back()->withErrors(['Worker must be in Stage 2.']);
        }

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $worker->update([
            'stage_2_submitted_at' => null,
            'notes' => $worker->notes."\n\nStage 2 Rejection Reason: ".$validated['rejection_reason'],
        ]);

        // Clear compliance documents to require resubmission
        $worker->complianceDocuments()->update(['status' => 'rejected']);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Stage 2 Rejected',
            'message' => 'Your compliance documents were rejected. Please review the requested changes and resubmit them.',
            'type' => 'warning',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
                'reason' => $validated['rejection_reason'],
            ],
        ]);

        return redirect()->route('admin.worker_onboarding.show', $worker)
            ->with('success', 'Stage 2 rejected. Worker notified to resubmit documents.');
    }

    /**
     * Approve Stage 3 document review and move to Stage 4
     */
    public function approveStage3(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 3) {
            return back()->withErrors(['Worker must be in Stage 3.']);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Create Stage 4 declarations
        $this->createDeclarationsForWorker($worker);

        $worker->update([
            'stage_3_completed_at' => now(),
            'stage_3_reviewer_id' => Auth::id(),
            'onboarding_stage' => 4,
            'stage_4_submitted_at' => now(),
            'notes' => $validated['notes'] ?? $worker->notes,
        ]);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Stage 3 Approved',
            'message' => 'Your documents have passed review. Please sign the declarations to continue onboarding.',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
            ],
        ]);

        return redirect()->route('admin.worker_onboarding.show', $worker)
            ->with('success', 'Stage 3 approved. Worker advanced to Stage 4 (Sign Declarations).');
    }

    /**
     * Batch verify or reject multiple compliance documents during Stage 3 review.
     * Accepts payload: documents => [ { id, action, reason? }, ... ], auto_approve => bool
     */
    public function verifyStage3Batch(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 3) {
            return back()->withErrors(['Worker must be in Stage 3.']);
        }

        $validated = $request->validate([
            'documents' => ['required', 'array'],
            'documents.*.id' => ['required', 'integer', 'exists:worker_compliance_documents,id'],
            'documents.*.action' => ['required', 'in:active,reject'],
            'documents.*.reason' => ['nullable', 'string', 'max:1000'],
            'auto_approve' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($validated['documents'] as $docPayload) {
            $doc = WorkerComplianceDocument::find($docPayload['id']);
            if (! $doc || $doc->worker_id !== $worker->id) {
                continue;
            }

            if ($docPayload['action'] === 'active') {
                $doc->markAsVerified(auth()->user());
            } else {
                $reason = $docPayload['reason'] ?? 'Rejected by reviewer';
                $doc->markAsRejected(auth()->user(), $reason);
            }
        }

        // Optionally auto-approve stage 3 if requested and all required types are active
        if (! empty($validated['auto_approve']) && $validated['auto_approve']) {
            WorkerComplianceType::ensureDefaults();
            $requiredTypes = WorkerComplianceType::where('is_required', true)->pluck('name')->toArray();
            $allActive = true;

            foreach ($requiredTypes as $typeName) {
                $d = $worker->complianceDocuments()->where('document_type', $typeName)->first();
                if (! $d || $d->status !== ComplianceStatus::ACTIVE->value) {
                    $allActive = false;
                    break;
                }
            }

            if ($allActive) {
                // create declarations and advance stage (reuse approveStage3 logic)
                $this->createDeclarationsForWorker($worker);

                $worker->update([
                    'stage_3_completed_at' => now(),
                    'stage_3_reviewer_id' => Auth::id(),
                    'onboarding_stage' => 4,
                    'stage_4_submitted_at' => now(),
                    'notes' => $validated['notes'] ?? $worker->notes,
                ]);

                NotificationService::notify([
                    'user_id' => $worker->user_id,
                    'title' => 'Stage 3 Approved',
                    'message' => 'Your documents have passed review. Please sign the declarations to continue onboarding.',
                    'type' => 'success',
                    'channel' => 'in_app',
                    'data' => [
                        'worker_id' => $worker->id,
                        'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
                    ],
                ]);

                return redirect()->route('admin.worker_onboarding.show', $worker)
                    ->with('success', 'Documents verified and Stage 3 auto-approved. Worker advanced to Stage 4.');
            }
        }

        return back()->with('success', 'Documents processed successfully.');
    }

    /**
     * Approve Stage 4 declarations and move to Stage 5
     */
    public function approveStage4(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 4) {
            return back()->withErrors(['Worker must be in Stage 4.']);
        }

        // Check that all declarations are signed
        if (! $worker->getAllDeclarationsSignedForStage4()) {
            return back()->withErrors(['All declarations must be signed before approval.']);
        }

        $worker->update([
            'stage_4_completed_at' => now(),
            'onboarding_stage' => 5,
            'stage_5_submitted_at' => now(),
        ]);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Stage 4 Approved',
            'message' => 'Your declarations have been approved. AHHC will now assign service categories for your work.',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
            ],
        ]);

        $adminUsers = User::whereIn('role', ['admin', 'system_admin'])->get();
        foreach ($adminUsers as $adminUser) {
            NotificationService::notify([
                'user_id' => $adminUser->id,
                'title' => 'Worker Needs Stage 5 Service Approval',
                'message' => sprintf('%s %s is now in Stage 5 and requires approved service categories to continue onboarding.', $worker->first_name, $worker->last_name),
                'type' => 'info',
                'channel' => 'in_app',
                'data' => [
                    'worker_id' => $worker->id,
                    'url' => route('admin.worker_onboarding.show', $worker),
                ],
            ]);
        }

        return redirect()->route('admin.worker_onboarding.show', $worker)
            ->with('success', 'Stage 4 approved. Worker advanced to Stage 5 (Service Approval).');
    }

    /**
     * Add approved service categories for Stage 5
     */
    public function addServiceApproval(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 5) {
            return back()->withErrors(['Worker must be in Stage 5.']);
        }

        $validated = $request->validate([
            'service_category' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'approval_end_date' => ['nullable', 'date', 'after:today'],
        ]);

        WorkerServiceApproval::create([
            'worker_id' => $worker->id,
            'service_category' => $validated['service_category'],
            'description' => $validated['description'],
            'status' => 'approved',
            'approved_by_id' => Auth::id(),
            'approved_at' => now(),
            'approval_start_date' => now()->toDateString(),
            'approval_end_date' => $validated['approval_end_date'] ?? null,
        ]);

        return back()->with('success', 'Service category approved for worker.');
    }

    /**
     * Approve Stage 5 and move to Stage 6
     */
    public function approveStage5(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 5) {
            return back()->withErrors(['Worker must be in Stage 5.']);
        }

        if (! $worker->hasApprovedServices()) {
            return back()->withErrors(['At least one service category must be approved.']);
        }

        $worker->update([
            'stage_5_completed_at' => now(),
            'stage_5_approver_id' => Auth::id(),
            'onboarding_stage' => 6,
            'stage_6_assigned_at' => now(),
            'stage_6_assignor_id' => Auth::id(),
        ]);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Onboarding Complete: Stage 6',
            'message' => 'Your onboarding is complete and you are now ready for participant assignment.',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
            ],
        ]);

        $adminUsers = User::whereIn('role', ['admin', 'system_admin'])->get();
        foreach ($adminUsers as $adminUser) {
            NotificationService::notify([
                'user_id' => $adminUser->id,
                'title' => 'Worker Onboarding Complete',
                'message' => sprintf('%s %s has completed onboarding and is ready for assignment.', $worker->first_name, $worker->last_name),
                'type' => 'success',
                'channel' => 'in_app',
                'data' => [
                    'worker_id' => $worker->id,
                    'url' => route('admin.worker_onboarding.show', $worker),
                ],
            ]);
        }

        return redirect()->route('admin.worker_onboarding.show', $worker)
            ->with('success', 'Stage 5 approved. Worker is ready for participant assignment.');
    }

    /**
     * Assign participants during Stage 6.
     */
    public function assignStage6Participants(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 6) {
            return back()->withErrors(['participant_ids' => 'Worker must be in Stage 6 before participants can be assigned.']);
        }

        $validated = $request->validate([
            'participant_ids' => ['nullable', 'array'],
            'participant_ids.*' => ['integer', 'distinct', Rule::exists('participants', 'id')->where(fn ($query) => $query->where('status', 'active'))],
        ]);

        $participantIds = collect($validated['participant_ids'] ?? [])
            ->filter()
            ->unique()
            ->values()
            ->all();

        $validParticipantIds = Participant::whereIn('id', $participantIds)
            ->where('status', 'active')
            ->pluck('id')
            ->all();

        $invalidIds = array_diff($participantIds, $validParticipantIds);
        if (! empty($invalidIds)) {
            return back()->withInput()->withErrors(['participant_ids' => 'One or more selected participants are inactive or invalid.']);
        }

        DB::transaction(function () use ($worker, $participantIds) {
            $currentAssignedIds = $worker->assignments()->where('status', 'active')->pluck('participant_id')->toArray();
            $toKeep = collect($participantIds)->intersect($currentAssignedIds)->all();
            $toAdd = array_diff($participantIds, $currentAssignedIds);
            $toRemove = array_diff($currentAssignedIds, $participantIds);

            if (! empty($toRemove)) {
                ParticipantAssignment::where('worker_id', $worker->id)
                    ->whereIn('participant_id', $toRemove)
                    ->where('status', 'active')
                    ->update([
                        'status' => 'inactive',
                        'end_date' => now()->toDateString(),
                    ]);
            }

            $assignType = empty($currentAssignedIds) ? 'primary' : 'secondary';
            $usePrimary = empty($currentAssignedIds);

            foreach ($toAdd as $participantId) {
                if (ParticipantAssignment::where('participant_id', $participantId)
                    ->where('worker_id', $worker->id)
                    ->where('status', 'active')
                    ->exists()) {
                    continue;
                }

                ParticipantAssignment::create([
                    'participant_id' => $participantId,
                    'worker_id' => $worker->id,
                    'start_date' => now()->toDateString(),
                    'status' => 'active',
                    'assignment_type' => $usePrimary ? 'primary' : 'secondary',
                    'is_primary' => $usePrimary,
                ]);

                $usePrimary = false;
            }
        });

        return back()->with('success', 'Worker participant assignments updated successfully.');
    }

    /**
     * Activate the worker after Stage 6 assignment is complete.
     */
    public function activateWorker(Request $request, Worker $worker)
    {
        if ($worker->onboarding_stage !== 6) {
            return back()->withErrors(['worker' => 'Worker must be in Stage 6 before activation.']);
        }

        if ($worker->status === 'active') {
            return back()->with('success', 'Worker is already active.');
        }

        $worker->update(['status' => 'active']);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Worker Account Activated',
            'message' => 'Your worker onboarding is complete and your account is now active.',
            'type' => 'success',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.portal.dashboard'),
            ],
        ]);

        return back()->with('success', 'Worker activated successfully.');
    }

    /**
     * Reject a worker (any stage)
     */
    public function rejectWorker(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000'],
        ]);

        $worker->update([
            'status' => 'rejected',
            'notes' => $worker->notes."\n\nRejection Reason: ".$validated['rejection_reason'],
        ]);

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'title' => 'Worker Onboarding Rejected',
            'message' => 'Your worker onboarding has been rejected by AHHC. Reason: '.$validated['rejection_reason'],
            'type' => 'warning',
            'channel' => 'in_app',
            'data' => [
                'worker_id' => $worker->id,
                'url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
            ],
        ]);

        $adminUsers = User::whereIn('role', ['admin', 'system_admin'])->get();
        foreach ($adminUsers as $adminUser) {
            NotificationService::notify([
                'user_id' => $adminUser->id,
                'title' => 'Worker Onboarding Rejected',
                'message' => sprintf('%s %s onboarding was rejected by %s.', $worker->first_name, $worker->last_name, Auth::user()->name),
                'type' => 'warning',
                'channel' => 'in_app',
                'data' => [
                    'worker_id' => $worker->id,
                    'url' => route('admin.worker_onboarding.show', $worker),
                ],
            ]);
        }

        return redirect()->route('admin.worker_onboarding.index')
            ->with('success', 'Worker onboarding rejected.');
    }

    /**
     * Resend the onboarding invitation email to the worker's email address.
     */
    public function resendInvitation(Request $request, Worker $worker)
    {
        // Queue the onboarding invitation to the worker's email
        try {
            $html = view('mail.worker_onboarding_invitation', ['worker' => $worker, 'onboardingUrl' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]), 'expiresAt' => $worker->onboarding_expires_at])->render();
            TemplateMailer::send(
                $worker->email,
                'worker-onboarding-invitation',
                [
                    'name' => trim($worker->first_name.' '.$worker->last_name),
                    'first_name' => $worker->first_name,
                    'last_name' => $worker->last_name,
                    'email' => $worker->email,
                    'phone' => $worker->phone,
                    'worker_number' => $worker->worker_number,
                    'onboarding_url' => route('worker.onboarding.show', ['token' => $worker->onboarding_token]),
                    'expires_at' => optional($worker->onboarding_expires_at)->format('M d, Y') ?? now()->addDays(30)->format('M d, Y'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'AHHC Portal - Worker Onboarding Invitation',
                $html,
                strip_tags($html),
                'Worker Onboarding Invitation',
                'Onboarding'
            );

            NotificationService::notify([
                'user_id' => Auth::id(),
                'title' => 'Invitation Resent',
                'message' => 'Onboarding invitation resent to '.$worker->email,
                'type' => 'info',
                'channel' => 'in_app',
            ]);

            // Audit log for resend
            AuditLogService::record('Worker Invitation Resent', $worker, [], [
                'sent_to' => $worker->email,
                'sent_by' => Auth::id(),
            ]);

            return back()->with('success', 'Invitation resent to '.$worker->email);
        } catch (\Exception $e) {
            return back()->with('warning', 'Failed to resend invitation: '.$e->getMessage());
        }
    }

    /**
     * Create standard declarations for a worker
     */
    private function createDeclarationsForWorker(Worker $worker): void
    {
        foreach (WorkerDeclarationType::all() as $declarationType) {
            WorkerDeclaration::firstOrCreate(
                [
                    'worker_id' => $worker->id,
                    'declaration_type' => $declarationType,
                ],
                [
                    'declaration_text' => $declarationType->defaultText(),
                    'agreed' => false,
                ]
            );
        }
    }
}
