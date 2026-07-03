<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePreApprovalRequest;
use App\Http\Requests\UpdatePreApprovalRequest;
use App\Models\Participant;
use App\Models\PreApprovalAttachment;
use App\Models\PreApprovalComment;
use App\Models\PreApprovalRequest;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PreApprovalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $requests = PreApprovalRequest::query()
            ->where('participant_id', $participant->id)
            ->with(['attachments', 'comments.commenter'])
            ->latest()
            ->get();

        $workers = $participant->assignments()->with('worker')->get()->pluck('worker')->filter();

        return view('portal.participant.pre-approvals', compact('participant', 'requests', 'workers'));
    }

    public function storeForParticipant(StorePreApprovalRequest $request)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $quoteFile = $request->file('quote');
        $quotePath = $quoteFile->store('pre-approval-quotes', 'local');

        $serviceType = $request->input('service_type') ?: $request->input('service_category');
        $serviceCategory = $request->input('service_category') ?: $request->input('service_type');
        $purpose = $request->input('purpose') ?: $request->input('description');
        $requestedAmountCents = $request->input('requested_amount_cents') !== null
            ? (int) $request->input('requested_amount_cents')
            : (int) round($request->input('requested_amount') * 100);

        $preApproval = PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'support_person_id' => $participant->assigned_support_person_id,
            'worker_id' => $request->input('worker_id'),
            'supplier_id' => $request->input('supplier_id'),
            'request_number' => 'PA-'.now()->format('YmdHis').'-'.$user->id,
            'service_type' => $serviceType,
            'service_category' => $serviceCategory,
            'purpose' => $purpose,
            'description' => $purpose,
            'requested_amount_cents' => $requestedAmountCents,
            'estimated_amount_cents' => $requestedAmountCents,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'expiry_date' => $request->input('expiry_date'),
            'quote_file_path' => $quotePath,
            'status' => PreApprovalRequest::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        if ($quotePath) {
            PreApprovalAttachment::create([
                'pre_approval_request_id' => $preApproval->id,
                'uploaded_by_id' => $user->id,
                'title' => 'Initial quote',
                'file_path' => $quotePath,
                'mime_type' => $quoteFile->getClientMimeType(),
                'size_bytes' => $quoteFile->getSize(),
                'notes' => 'Original quote submitted with request.',
            ]);
        }

        AuditLogService::record('Pre-Approval Submission', $preApproval, [], [
            'request_number' => $preApproval->request_number,
            'participant_id' => $participant->id,
            'status' => $preApproval->status,
            'requested_amount_cents' => $preApproval->requested_amount_cents,
        ]);

        User::where('role', 'admin')->get()->each(function ($admin) use ($participant) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'participant_id' => $participant->id,
                'type' => 'info',
                'data' => [
                    'title' => 'Pre-approval request received',
                    'message' => "{$participant->first_name} submitted a new pre-approval request.",
                    'url' => route('portal.admin.pre_approvals'),
                ],
            ]);
        });

        return redirect()->route('portal.dashboard')->with('status', 'Pre-approval request submitted successfully.');
    }

    public function downloadQuote(PreApprovalRequest $preApprovalRequest)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($preApprovalRequest->participant_id !== $participant->id || ! $preApprovalRequest->quote_file_path) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($preApprovalRequest->quote_file_path)) {
            abort(404);
        }

        $filename = basename($preApprovalRequest->quote_file_path);

        return Storage::disk('local')->download(
            $preApprovalRequest->quote_file_path,
            sprintf('%s-quote-%s', $preApprovalRequest->request_number, $filename)
        );
    }

    public function downloadAttachment(PreApprovalRequest $preApprovalRequest, PreApprovalAttachment $attachment)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($preApprovalRequest->participant_id !== $participant->id || $attachment->pre_approval_request_id !== $preApprovalRequest->id) {
            abort(403);
        }

        if (! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404);
        }

        $filename = basename($attachment->file_path);

        return Storage::disk('local')->download(
            $attachment->file_path,
            sprintf('%s-attachment-%s', $preApprovalRequest->request_number, $filename)
        );
    }

    public function updateForParticipant(UpdatePreApprovalRequest $request, PreApprovalRequest $preApprovalRequest)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        if ($preApprovalRequest->participant_id !== $participant->id) {
            abort(403);
        }

        if (! in_array($preApprovalRequest->status, [
            PreApprovalRequest::STATUS_SUBMITTED,
            PreApprovalRequest::STATUS_INFO_REQUESTED,
            PreApprovalRequest::STATUS_DRAFT,
        ], true)) {
            return back()->withErrors(['pre_approval' => 'Only submitted or information-requested requests can be updated.']);
        }

        $updateData = [];

        if ($request->filled('description')) {
            $updateData['description'] = $request->input('description');
        }

        if ($request->filled('requested_amount') || $request->filled('requested_amount_cents')) {
            $updateData['requested_amount_cents'] = $request->input('requested_amount_cents') !== null
                ? (int) $request->input('requested_amount_cents')
                : (int) round($request->input('requested_amount') * 100);
            $updateData['estimated_amount_cents'] = $updateData['requested_amount_cents'];
        }

        if ($preApprovalRequest->status === PreApprovalRequest::STATUS_INFO_REQUESTED) {
            $updateData['status'] = PreApprovalRequest::STATUS_SUBMITTED;
            $updateData['submitted_at'] = now();
        }

        if ($request->file('quote')) {
            $quoteFile = $request->file('quote');
            $quotePath = $quoteFile->store('pre-approval-quotes', 'local');
            $updateData['quote_file_path'] = $quotePath;

            PreApprovalAttachment::create([
                'pre_approval_request_id' => $preApprovalRequest->id,
                'uploaded_by_id' => $user->id,
                'title' => 'Updated quote or supporting document',
                'file_path' => $quotePath,
                'mime_type' => $quoteFile->getClientMimeType(),
                'size_bytes' => $quoteFile->getSize(),
                'notes' => 'Participant uploaded a new supporting document.',
            ]);
        }

        if (! empty($updateData)) {
            $preApprovalRequest->update($updateData);
        }

        if ($request->filled('participant_note')) {
            PreApprovalComment::create([
                'pre_approval_request_id' => $preApprovalRequest->id,
                'commented_by_id' => $user->id,
                'comment_type' => 'participant_response',
                'message' => $request->input('participant_note'),
            ]);
        }

        AuditLogService::record('Pre-Approval Updated', $preApprovalRequest, [], [
            'request_number' => $preApprovalRequest->request_number,
            'participant_id' => $participant->id,
            'status' => $preApprovalRequest->status,
        ]);

        User::where('role', 'admin')->get()->each(function ($admin) use ($participant, $preApprovalRequest) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'participant_id' => $participant->id,
                'type' => 'info',
                'data' => [
                    'title' => 'Pre-approval request updated',
                    'message' => "{$participant->first_name} updated pre-approval request {$preApprovalRequest->request_number}.",
                    'url' => route('portal.admin.pre_approvals.show', $preApprovalRequest),
                ],
            ]);
        });

        return back()->with('status', 'Pre-approval request updated successfully.');
    }
}
