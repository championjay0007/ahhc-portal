<?php

namespace App\Http\Controllers;

use App\Enums\ExportFormat;
use App\Enums\ReportType;
use App\Models\ReportExportLog;
use App\Services\ExportService;
use App\Services\ReportDashboardService;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ReportDashboardService $dashboardService
    ) {}

    /**
     * Get reports center dashboard
     */
    public function dashboard(): JsonResponse
    {
        $stats = $this->dashboardService->getDashboardStats();
        $history = $this->dashboardService->getExportHistory(10);
        $reportStats = $this->dashboardService->getReportStats();
        $formatStats = $this->dashboardService->getFormatStats();

        return response()->json([
            'stats' => $stats,
            'history' => $history,
            'report_stats' => $reportStats,
            'format_stats' => $formatStats,
        ]);
    }

    /**
     * Get available reports
     */
    public function availableReports(): JsonResponse
    {
        return response()->json([
            'report_types' => ReportType::options(),
            'export_formats' => ExportFormat::options(),
        ]);
    }

    /**
     * Generate and export report
     */
    public function exportReport(Request $request)
    {
        $validated = $request->validate([
            'report_type' => ['required', Rule::in(array_keys(ReportType::options()))],
            'export_format' => ['required', Rule::in(array_keys(ExportFormat::options()))],
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'participant_id' => 'nullable|integer|exists:participants,id',
            'worker_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable|string',
        ]);

        // Check authorization
        if (auth()->user()?->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Build filters
        $filters = array_filter([
            'participant_id' => $validated['participant_id'] ?? null,
            'worker_id' => $validated['worker_id'] ?? null,
            'care_manager_id' => $validated['worker_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        // Get report data
        $reportData = $this->reportService->getReportData(
            $validated['report_type'],
            $filters
        );

        // Export to requested format
        $exporter = ExportService::withMetadata(
            auth()->user()->name,
            $filters
        );

        $content = match ($validated['export_format']) {
            'csv' => $exporter->toCSV($reportData, $validated['report_type']),
            'excel' => $exporter->toExcel($reportData, $validated['report_type']),
            'pdf' => $exporter->toPDF($reportData, $validated['report_type']),
            default => '',
        };

        // Log the export
        $this->logExport(
            $validated['report_type'],
            $validated['export_format'],
            $reportData->count(),
            $filters,
            strlen($content)
        );

        // Generate filename
        $filename = $this->generateFilename(
            $validated['report_type'],
            $validated['export_format']
        );

        // Return file
        return response($content)
            ->header('Content-Type', ExportFormat::mimeType(ExportFormat::from($validated['export_format'])))
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get export history
     */
    public function exportHistory(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 20);
        $history = $this->dashboardService->getExportHistory($limit);

        return response()->json(['history' => $history]);
    }

    /**
     * Get report statistics
     */
    public function reportStatistics(): JsonResponse
    {
        $reportStats = $this->dashboardService->getReportStats();
        $formatStats = $this->dashboardService->getFormatStats();
        $userActivity = $this->dashboardService->getUserActivity();

        return response()->json([
            'report_stats' => $reportStats,
            'format_stats' => $formatStats,
            'user_activity' => $userActivity,
        ]);
    }

    /**
     * Preview report data
     */
    public function previewReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'report_type' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'participant_id' => 'nullable|integer|exists:participants,id',
            'worker_id' => 'nullable|integer|exists:users,id',
            'status' => 'nullable|string',
        ]);

        // Build filters
        $filters = array_filter([
            'participant_id' => $validated['participant_id'] ?? null,
            'worker_id' => $validated['worker_id'] ?? null,
            'care_manager_id' => $validated['worker_id'] ?? null,
            'status' => $validated['status'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ]);

        // Get report data
        $reportData = $this->reportService->getReportData(
            $validated['report_type'],
            $filters
        );

        return response()->json([
            'report_type' => $validated['report_type'],
            'record_count' => $reportData->count(),
            'preview_data' => $reportData->take(5),
            'all_data' => $reportData,
        ]);
    }

    /**
     * Log the export
     */
    private function logExport(string $reportType, string $format, int $recordCount, array $filters, int $fileSize): void
    {
        ReportExportLog::create([
            'user_id' => auth()->id(),
            'report_type' => $reportType,
            'export_format' => $format,
            'filters' => $filters,
            'record_count' => $recordCount,
            'file_size' => $fileSize,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'exported_at' => now(),
        ]);
    }

    /**
     * Generate filename for export
     */
    private function generateFilename(string $reportType, string $format): string
    {
        $sanitized = preg_replace('/[^a-z0-9]+/', '_', strtolower($reportType));
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = ExportFormat::fileExtension(ExportFormat::from($format));

        return "{$sanitized}_{$timestamp}.{$extension}";
    }
}
