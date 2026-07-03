<?php

namespace App\Services;

use App\Models\ReportExportLog;

class ReportDashboardService
{
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'total_exports' => ReportExportLog::count(),
            'exports_this_month' => ReportExportLog::whereBetween('exported_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->count(),
            'exports_this_week' => ReportExportLog::whereBetween('exported_at', [
                now()->startOfWeek(),
                now()->endOfWeek(),
            ])->count(),
            'total_records_exported' => ReportExportLog::sum('record_count') ?? 0,
            'most_used_format' => $this->getMostUsedFormat(),
            'most_generated_report' => $this->getMostGeneratedReport(),
            'top_exporters' => $this->getTopExporters(),
        ];
    }

    /**
     * Get export history
     */
    public function getExportHistory(int $limit = 20): array
    {
        $logs = ReportExportLog::with('user')
            ->recentFirst()
            ->limit($limit)
            ->get();

        return $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'report_type' => $log->report_type,
                'export_format' => $log->export_format,
                'user' => $log->user?->name,
                'record_count' => $log->record_count,
                'file_size' => $this->formatFileSize($log->file_size),
                'filters' => $log->getFiltersSummary(),
                'exported_at' => $log->exported_at->toDateTimeString(),
            ];
        })->toArray();
    }

    /**
     * Get report statistics
     */
    public function getReportStats(): array
    {
        $stats = ReportExportLog::select('report_type')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(record_count) as total_records')
            ->groupBy('report_type')
            ->orderByDesc('count')
            ->get();

        return $stats->map(function ($stat) {
            return [
                'report_type' => $stat->report_type,
                'exports_count' => $stat->count,
                'total_records' => $stat->total_records ?? 0,
            ];
        })->toArray();
    }

    /**
     * Get format usage statistics
     */
    public function getFormatStats(): array
    {
        $stats = ReportExportLog::select('export_format')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(file_size) as total_size')
            ->groupBy('export_format')
            ->orderByDesc('count')
            ->get();

        return $stats->map(function ($stat) {
            return [
                'format' => $stat->export_format,
                'exports' => $stat->count,
                'total_size' => $this->formatFileSize($stat->total_size),
            ];
        })->toArray();
    }

    /**
     * Get user export activity
     */
    public function getUserActivity(int $limit = 10): array
    {
        $activity = ReportExportLog::select('user_id')
            ->with('user')
            ->selectRaw('COUNT(*) as export_count')
            ->selectRaw('SUM(record_count) as records_exported')
            ->groupBy('user_id')
            ->orderByDesc('export_count')
            ->limit($limit)
            ->get();

        return $activity->map(function ($item) {
            return [
                'user' => $item->user?->name,
                'exports' => $item->export_count,
                'records_exported' => $item->records_exported ?? 0,
            ];
        })->toArray();
    }

    /**
     * Get most used format
     */
    private function getMostUsedFormat(): string
    {
        $format = ReportExportLog::select('export_format')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('export_format')
            ->orderByDesc('count')
            ->first();

        return $format?->export_format ?? 'N/A';
    }

    /**
     * Get most generated report
     */
    private function getMostGeneratedReport(): string
    {
        $report = ReportExportLog::select('report_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('report_type')
            ->orderByDesc('count')
            ->first();

        return $report?->report_type ?? 'N/A';
    }

    /**
     * Get top exporters
     */
    private function getTopExporters(): array
    {
        $users = ReportExportLog::select('user_id')
            ->with('user')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('user_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return $users->map(function ($user) {
            return [
                'name' => $user->user?->name ?? 'Unknown',
                'exports' => $user->count,
            ];
        })->toArray();
    }

    /**
     * Format file size for display
     */
    private function formatFileSize(?int $bytes): string
    {
        if (! $bytes) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
