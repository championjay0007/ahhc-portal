<?php

namespace App\Http\Controllers;

use App\Enums\ComplianceDocumentType;
use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use App\Services\ComplianceDashboardService;
use App\Services\ComplianceReportExporter;
use App\Services\ComplianceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ComplianceController extends Controller
{
    public function __construct(
        private ComplianceService $complianceService,
        private ComplianceDashboardService $dashboardService,
        private ComplianceReportExporter $reportExporter
    ) {}

    /**
     * Get all compliance documents
     */
    public function index(Request $request): JsonResponse
    {
        $query = WorkerComplianceDocument::with('worker', 'verifiedBy');

        if ($request->has('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }

        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->paginate($request->get('per_page', 15));

        return response()->json($documents);
    }

    /**
     * Get a specific compliance document
     */
    public function show(Request $request, WorkerComplianceDocument $document): mixed
    {
        $document->load('worker', 'verifiedBy');

        if ($request->wantsJson()) {
            return response()->json($document);
        }

        return view('admin.compliance.document', [
            'document' => $document,
        ]);
    }

    /**
     * Store a new compliance document
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'document_type' => 'required|string',
            'document_path' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $documentType = ComplianceDocumentType::from($validated['document_type']);

        $document = $this->complianceService->createOrUpdateDocument(
            Worker::findOrFail($validated['worker_id']),
            $documentType,
            $validated['document_path'] ?? null,
            isset($validated['issue_date']) ? now()->parse($validated['issue_date']) : null,
            isset($validated['expiry_date']) ? now()->parse($validated['expiry_date']) : null,
            $validated['notes'] ?? null
        );

        return response()->json($document, 201);
    }

    /**
     * Update a compliance document
     */
    public function update(Request $request, WorkerComplianceDocument $document)
    {
        $validated = $request->validate([
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'document_path' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $document->update($validated);
        $this->complianceService->updateDocumentStatus($document);

        if ($request->wantsJson()) {
            return response()->json($document);
        }

        return redirect()->route('portal.admin.compliance.documents.show', $document)
            ->with('status', 'Compliance document updated successfully');
    }

    /**
     * Delete a compliance document
     */
    public function destroy(Request $request, WorkerComplianceDocument $document)
    {
        $this->complianceService->deleteDocument($document);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Document deleted successfully']);
        }

        return redirect()->route('portal.admin.compliance.dashboard')
            ->with('status', 'Document deleted successfully');
    }

    /**
     * Upload compliance document file
     */
    public function uploadFile(Request $request, WorkerComplianceDocument $document)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        // Delete old file if exists
        if ($document->document_path && Storage::disk('private')->exists($document->document_path)) {
            Storage::disk('private')->delete($document->document_path);
        }

        $path = $request->file('file')->store('compliance_documents/'.$document->worker_id, 'private');

        $document->update(['document_path' => $path]);

        if ($request->wantsJson()) {
            return response()->json(['path' => $path, 'document' => $document]);
        }

        return redirect()->route('portal.admin.compliance.documents.show', $document)
            ->with('status', 'Document file uploaded successfully');
    }

    /**
     * Preview compliance document file inline for admins.
     */
    public function previewFile(WorkerComplianceDocument $document)
    {
        if (! $document->document_path || ! Storage::disk('private')->exists($document->document_path)) {
            abort(404, 'File not found');
        }

        return response()->file(Storage::disk('private')->path($document->document_path), [
            'Content-Type' => Storage::disk('private')->mimeType($document->document_path),
            'Content-Disposition' => 'inline; filename="'.basename($document->document_path).'"',
        ]);
    }

    /**
     * Get compliance document file
     */
    public function downloadFile(WorkerComplianceDocument $document): StreamedResponse
    {
        if (! $document->document_path || ! Storage::disk('private')->exists($document->document_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('private')->download($document->document_path);
    }

    /**
     * Verify a compliance document
     */
    public function verify(Request $request, WorkerComplianceDocument $document)
    {
        $this->authorize('verify', $document);

        $document->markAsVerified(auth()->user());

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Document verified', 'document' => $document]);
        }

        return redirect()->route('portal.admin.compliance.documents.show', $document)
            ->with('status', 'Document verified successfully');
    }

    /**
     * Reject a compliance document
     */
    public function reject(Request $request, WorkerComplianceDocument $document)
    {
        $this->authorize('reject', $document);

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $document->markAsRejected(auth()->user(), $validated['reason']);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Document rejected', 'document' => $document]);
        }

        return redirect()->route('portal.admin.compliance.documents.show', $document)
            ->with('status', 'Document rejected successfully');
    }

    /**
     * Show the admin compliance dashboard
     */
    public function dashboard(): mixed
    {
        if (request()->wantsJson()) {
            $stats = $this->dashboardService->getDashboardStats();

            return response()->json($stats);
        }

        return view('admin.compliance.dashboard');
    }

    /**
     * Get dashboard statistics
     */
    public function dashboardStats(): JsonResponse
    {
        $stats = $this->dashboardService->getDashboardStats();

        return response()->json($stats);
    }

    /**
     * Get compliance report
     */
    public function report(Request $request): JsonResponse
    {
        $reportType = $request->get('type', 'all');

        $report = match ($reportType) {
            'expiring' => $this->dashboardService->getExpiringDocuments(),
            'expired' => $this->dashboardService->getExpiredDocuments(),
            'missing' => $this->dashboardService->getMissingDocuments(),
            'rejected' => $this->dashboardService->getRejectedDocuments(),
            'workers_with_issues' => $this->dashboardService->getWorkersWithIssues(),
            'by_type' => $this->dashboardService->getComplianceByDocumentType(),
            default => [
                'expiring' => $this->dashboardService->getExpiringDocuments(),
                'expired' => $this->dashboardService->getExpiredDocuments(),
                'missing' => $this->dashboardService->getMissingDocuments(),
            ],
        };

        return response()->json($report);
    }

    /**
     * Export compliance report
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'all');

        $csv = match ($type) {
            'expiring' => $this->reportExporter->exportExpiringReport(),
            'missing' => $this->reportExporter->exportMissingReport(),
            'worker_summary' => $this->reportExporter->exportWorkerComplianceSummary(),
            'rejected' => $this->reportExporter->exportRejectedReport(),
            default => $this->reportExporter->exportAsCSV(),
        };

        $filename = $this->reportExporter->generateFilename($type);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get worker compliance details
     */
    public function workerCompliance(Request $request, Worker $worker): mixed
    {
        $details = $this->dashboardService->getWorkerComplianceDetails($worker);
        $alerts = $this->complianceService->getWorkerAlerts($worker);

        if ($request->wantsJson()) {
            return response()->json(array_merge($details, [
                'alerts' => $alerts->map(function ($alert) {
                    return [
                        'id' => $alert->id,
                        'alert_type' => $alert->alert_type,
                        'alert_level' => $alert->alert_level,
                        'message' => $alert->message,
                        'document_type' => $alert->document_type,
                        'sent_at' => $alert->sent_at?->toDateTimeString(),
                    ];
                })->values()->toArray(),
            ]));
        }

        return view('admin.compliance.worker', [
            'worker' => $worker,
            'details' => $details,
            'alerts' => $alerts,
        ]);
    }

    /**
     * Check if worker can be assigned
     */
    public function checkAssignability(Worker $worker): JsonResponse
    {
        $canAssign = $this->complianceService->canWorkerBeAssigned($worker);
        $blockingReason = $this->complianceService->getAssignmentBlockingReason($worker);

        return response()->json([
            'can_assign' => $canAssign,
            'blocking_reason' => $blockingReason,
            'critical_issues' => $this->complianceService->getWorkerCriticalIssues($worker),
        ]);
    }

    /**
     * Initialize worker compliance documents
     */
    public function initializeWorker(Request $request, Worker $worker)
    {
        $this->complianceService->initializeWorkerCompliance($worker);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Compliance documents initialized',
                'documents' => $worker->complianceDocuments,
            ]);
        }

        return redirect()->route('portal.admin.compliance.workers.show', $worker)
            ->with('status', 'Worker compliance documents initialized');
    }

    /**
     * Scan all workers for compliance
     */
    public function scanCompliance(): JsonResponse
    {
        $results = $this->complianceService->scanAllWorkerCompliance();

        return response()->json($results);
    }

    /**
     * Get compliance document types
     */
    public function getDocumentTypes(): JsonResponse
    {
        return response()->json(ComplianceDocumentType::options());
    }

    /**
     * Get workers needing attention
     */
    public function workersNeedingAttention(): JsonResponse
    {
        $workers = $this->complianceService->getWorkersNeedingAttention();

        return response()->json($workers->map(function ($worker) {
            return [
                'id' => $worker->id,
                'name' => $worker->first_name.' '.$worker->last_name,
                'worker_number' => $worker->worker_number,
                'issues_count' => $worker->complianceDocuments
                    ->whereIn('status', ['Expired', 'Expiring Soon', 'Missing'])
                    ->count(),
                'issues' => $worker->complianceDocuments
                    ->whereIn('status', ['Expired', 'Expiring Soon', 'Missing'])
                    ->map(fn ($doc) => ['type' => $doc->document_type, 'status' => $doc->status])
                    ->values(),
            ];
        }));
    }
}
