<?php

namespace App\Http\Controllers;

use App\Models\CareNote;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PreApprovalRequest;
use App\Models\Shift;
use App\Models\User;
use App\Notifications\ComplaintSubmitted;
use App\Services\BudgetService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ParticipantPortalController extends Controller
{
    public function showDocuments()
    {
        $user = Auth::user();

        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $documents = Document::query()
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->latest()
            ->get();

        return view('portal.participant.documents', compact('participant', 'documents'));
    }

    public function storeDocument(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,txt', 'max:10240'],
        ]);

        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participant->id,
            'document_type' => $validated['document_type'],
            'title' => $validated['title'],
            'storage_disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $user->id,
            'status' => 'uploaded',
            'is_sensitive' => true,
        ]);

        return redirect()->route('portal.dashboard')->with('status', 'Document uploaded successfully.');
    }

    public function downloadDocument(Document $document)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $document = Document::query()
            ->where('id', $document->id)
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->firstOrFail();

        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->storage_disk)->download($document->path, $document->title);
    }

    public function showPreApprovals()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $requests = PreApprovalRequest::query()
            ->where('participant_id', $participant->id)
            ->latest()
            ->get();

        return view('portal.participant.pre-approvals', compact('participant', 'requests'));
    }

    public function storePreApproval(Request $request)
    {
        $validated = $request->validate([
            'service_type' => ['required', 'string', 'max:100'],
            'purpose' => ['required', 'string', 'max:2000'],
            'requested_amount_cents' => ['required', 'integer', 'min:1'],
        ]);

        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'support_person_id' => $participant->assigned_support_person_id,
            'request_number' => 'PA-'.now()->format('YmdHis').'-'.$user->id,
            'service_type' => $validated['service_type'],
            'purpose' => $validated['purpose'],
            'requested_amount_cents' => $validated['requested_amount_cents'],
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        User::where('role', 'admin')->get()->each(function ($admin) use ($participant, $validated) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'participant_id' => $participant->id,
                'type' => 'info',
                'data' => [
                    'title' => 'Pre-approval request received',
                    'message' => "{$participant->first_name} submitted a new {$validated['service_type']} request.",
                    'url' => route('portal.admin.pre_approvals'),
                ],
            ]);
        });

        return redirect()->route('portal.dashboard')->with('status', 'Pre-approval request submitted successfully.');
    }

    public function showInvoices()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $invoices = Invoice::query()
            ->where('participant_id', $participant->id)
            ->latest()
            ->get();

        return view('portal.participant.invoices', compact('participant', 'invoices'));
    }

    public function storeInvoice(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => ['required', 'string', 'max:100'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'subtotal_cents' => ['required', 'integer', 'min:0'],
            'gst_cents' => ['required', 'integer', 'min:0'],
            'total_cents' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        try {
            Invoice::create([
                'participant_id' => $participant->id,
                'invoice_number' => $validated['invoice_number'],
                'status' => 'draft',
                'subtotal_cents' => $validated['subtotal_cents'],
                'gst_cents' => $validated['gst_cents'],
                'total_cents' => $validated['total_cents'],
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'notes' => $validated['notes'] ?? null,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if (str_contains($e->getMessage(), 'unique') || str_contains($e->getMessage(), 'UNIQUE')) {
                return back()->withErrors(['invoice_number' => 'An invoice with that number already exists. Please choose a different invoice number.'])->withInput();
            }

            throw $e;
        }

        return redirect()->route('portal.dashboard')->with('status', 'Invoice submitted successfully.');
    }

    public function showComplaints()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $complaints = Complaint::query()
            ->where('participant_id', $participant->id)
            ->latest()
            ->get();

        return view('portal.participant.complaints', compact('participant', 'complaints'));
    }

    public function storeComplaint(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'string', 'in:low,medium,high'],
            'description' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $complaint = Complaint::create([
            'participant_id' => $participant->id,
            'support_person_id' => $participant->assigned_support_person_id,
            'submitted_by_id' => $user->id,
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'description' => $validated['description'],
            'status' => 'open',
            'received_at' => now(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Send in-app notification to all admins and include the complaint review URL
        User::where('role', 'admin')->get()->each(function ($admin) use ($complaint, $participant) {
            NotificationService::notify([
                'user_id' => $admin->id,
                'recipient_id' => $admin->id,
                'participant_id' => $participant->id,
                'type' => 'complaint_submitted',
                'title' => 'Complaint Submitted',
                'message' => "Complaint submitted by {$participant->first_name} {$participant->last_name}.",
                'url' => route('portal.admin.complaints.show', $complaint),
                'data' => [
                    'complaint_id' => $complaint->id,
                    'action_url' => route('portal.admin.complaints.show', $complaint),
                ],
            ]);
        });

        return redirect()->route('portal.dashboard')->with('status', 'Complaint submitted successfully.');
    }

    public function showBudget()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)
            ->with(['preApprovalRequests', 'invoices.worker'])
            ->firstOrFail();

        $budgetService = new BudgetService;
        $budget = $budgetService->getOrCreateBudgetForParticipantQuarter($participant);
        $budgetMetrics = $budgetService->getBudgetMetrics($budget);

        $openingBalanceCents = (int) ($budgetMetrics['opening_balance'] ?? 0);
        $carryOverCents = (int) ($budgetMetrics['carry_over'] ?? 0);
        $committedCents = (int) ($budgetMetrics['committed'] ?? 0);
        $approvedCents = (int) ($budgetMetrics['approved'] ?? 0);
        $paidCents = (int) ($budgetMetrics['paid'] ?? 0);
        $limitBudgetCents = (int) ($budgetMetrics['total'] ?? 0);

        $usedBudgetCents = (int) ($budgetMetrics['used'] ?? ($approvedCents + $paidCents));

        $computedRemaining = $limitBudgetCents - $committedCents - $usedBudgetCents;
        if (isset($budgetMetrics['remaining']) && (int) round((float) $budgetMetrics['remaining']) !== $computedRemaining) {
            \Log::warning('Budget remaining mismatch (ParticipantPortalController::showBudget)', [
                'participant_id' => $participant->id ?? null,
                'budget_metrics_remaining' => $budgetMetrics['remaining'] ?? null,
                'computed_remaining' => $computedRemaining,
                'budget_metrics' => $budgetMetrics,
            ]);
        }
        $remainingBudgetCents = (int) $computedRemaining;

        $budgetPercent = $limitBudgetCents ? round(($usedBudgetCents / $limitBudgetCents) * 100, 1) : 0;
        $overBudget = $usedBudgetCents > $limitBudgetCents;
        $currentQuarterLabel = $this->formatFiscalQuarterLabel(now());

        $pendingPreApprovalsCents = $participant->preApprovalRequests->where('status', 'submitted')->sum('requested_amount_cents');
        $approvedPreApprovalsCents = $participant->preApprovalRequests->where('status', 'approved')->sum('requested_amount_cents');
        $submittedInvoicesCents = $participant->invoices->where('status', 'submitted')->sum('amount_cents');
        $approvedInvoicesCents = $participant->invoices->where('status', 'approved')->sum('amount_cents');
        $paidInvoicesCents = $participant->invoices->where('status', 'paid')->sum('amount_cents');

        $availableAfterPendingCents = max(0, $remainingBudgetCents - $pendingPreApprovalsCents);

        $invoiceApprovalCounts = $participant->invoices->groupBy('status')->map->count();

        $invoiceCategorySpend = $participant->invoices
            ->groupBy(function ($invoice) {
                return $invoice->worker?->role_type ?: 'Uncategorized';
            })
            ->map(fn ($group) => $group->sum('amount_cents'))
            ->sortDesc();

        $preApprovalCategorySpend = $participant->preApprovalRequests
            ->groupBy(fn ($request) => $request->service_type ?: 'Uncategorised')
            ->map(fn ($group) => $group->sum('requested_amount_cents'))
            ->sortDesc();

        $budgetCategorySpend = $budgetService->getBudgetCategorySpend($budget);
        $budgetAlerts = $budgetService->getBudgetAlerts($budget, $participant);

        $recentPreApprovals = $participant->preApprovalRequests
            ->whereIn('status', ['submitted', 'approved'])
            ->sortByDesc('submitted_at')
            ->take(5);

        $recentInvoices = $participant->invoices
            ->whereIn('status', ['submitted', 'approved', 'paid'])
            ->sortByDesc('invoice_date')
            ->take(5);

        return view('portal.participant.budget', compact(
            'participant',
            'openingBalanceCents',
            'carryOverCents',
            'committedCents',
            'approvedCents',
            'paidCents',
            'usedBudgetCents',
            'remainingBudgetCents',
            'limitBudgetCents',
            'budgetPercent',
            'overBudget',
            'currentQuarterLabel',
            'pendingPreApprovalsCents',
            'approvedPreApprovalsCents',
            'submittedInvoicesCents',
            'approvedInvoicesCents',
            'paidInvoicesCents',
            'availableAfterPendingCents',
            'invoiceApprovalCounts',
            'invoiceCategorySpend',
            'preApprovalCategorySpend',
            'budgetCategorySpend',
            'budgetAlerts',
            'recentPreApprovals',
            'recentInvoices'
        ));
    }

    public function showTeam()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->with(['assignments.worker', 'supportPerson'])->firstOrFail();

        // Get active worker assignments with detailed information
        $activeAssignments = $participant->assignments()
            ->with('worker')
            ->where('status', 'active')
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get inactive/past assignments for history
        $pastAssignments = $participant->assignments()
            ->with('worker')
            ->where('status', 'inactive')
            ->orderBy('end_date', 'desc')
            ->take(10)
            ->get();

        // Get supplier/service provider assignments (different from care workers)
        $suppliers = $activeAssignments->filter(function ($assignment) {
            $type = strtolower(trim((string) $assignment->assignment_type));

            return in_array($type, ['supplier', 'service provider', 'marketplace', 'mable', 'third-party', 'third party']);
        });

        $careWorkers = $activeAssignments->filter(function ($assignment) {
            $type = strtolower(trim((string) $assignment->assignment_type));

            return $type === '' || in_array($type, ['care worker', 'primary', 'secondary']);
        });

        // Get recent care notes from assigned workers
        $workerIds = $activeAssignments->pluck('worker.id')->toArray();
        $recentCareNotes = CareNote::where('participant_id', $participant->id)
            ->when(! empty($workerIds), function ($query) use ($workerIds) {
                $query->whereIn('worker_id', $workerIds);
            })
            ->latest()
            ->take(5)
            ->get();

        return view('portal.participant.team', compact(
            'participant',
            'activeAssignments',
            'pastAssignments',
            'careWorkers',
            'suppliers',
            'recentCareNotes'
        ));
    }

    public function showServices()
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)
            ->with(['assignments.worker', 'supportPerson'])
            ->firstOrFail();

        $activeAssignments = $participant->assignments()
            ->with('worker')
            ->where('status', 'active')
            ->orderBy('is_primary', 'desc')
            ->orderBy('start_date', 'asc')
            ->get();

        $recentAssignments = $participant->assignments()
            ->with('worker')
            ->orderBy('start_date', 'desc')
            ->take(10)
            ->get();

        $approvedServices = $activeAssignments->groupBy(function ($assignment) {
            return $assignment->assignment_type ? ucfirst($assignment->assignment_type) : 'Care Worker';
        });

        $upcomingShifts = Shift::where('participant_id', $participant->id)
            ->whereDate('shift_date', '>=', now()->toDateString())
            ->orderBy('shift_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('portal.participant.services', compact(
            'participant',
            'activeAssignments',
            'recentAssignments',
            'approvedServices',
            'upcomingShifts'
        ));
    }

    public function createShift(Request $request)
    {
        $user = Auth::user();
        $participant = Participant::where('user_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'worker_id' => ['required', 'exists:workers,id'],
            'service_type' => ['nullable', 'string', 'max:150'],
            'service_category' => ['nullable', 'string', 'max:150'],
            'shift_date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'in:scheduled,confirmed'],
        ]);

        $validated['participant_id'] = $participant->id;
        $validated['status'] = $validated['status'] ?? Shift::STATUS_SCHEDULED;

        $shift = Shift::create($validated);

        return redirect()->route('portal.participant.services')
            ->with('status', 'Shift created successfully.');
    }
}
