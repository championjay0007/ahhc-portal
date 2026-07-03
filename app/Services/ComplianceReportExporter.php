<?php

namespace App\Services;

use App\Models\Worker;
use App\Models\WorkerComplianceDocument;
use Illuminate\Support\Collection;

class ComplianceReportExporter
{
    /**
     * Export compliance report as CSV
     */
    public function exportAsCSV(string $reportType = 'all'): string
    {
        $documents = $this->getDocumentsForReport($reportType);

        $csv = $this->generateCSV($documents);

        return $csv;
    }

    /**
     * Generate CSV content
     */
    private function generateCSV(Collection $documents): string
    {
        $csv = "Worker Number,Worker Name,Document Type,Issue Date,Expiry Date,Status,Verified Date,Notes\n";

        foreach ($documents as $doc) {
            $csv .= implode(',', [
                $doc->worker->worker_number,
                '"'.$doc->worker->first_name.' '.$doc->worker->last_name.'"',
                $doc->document_type,
                $doc->issue_date?->toDateString() ?? '',
                $doc->expiry_date?->toDateString() ?? '',
                $doc->status,
                $doc->verified_at?->toDateString() ?? '',
                '"'.str_replace('"', '""', $doc->notes ?? '').'"',
            ])."\n";
        }

        return $csv;
    }

    /**
     * Export expiring documents report
     */
    public function exportExpiringReport(int $days = 30): string
    {
        $targetDate = now()->addDays($days);

        $documents = WorkerComplianceDocument::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->toDateString(), $targetDate->toDateString()])
            ->where('status', '!=', 'Expired')
            ->with('worker')
            ->orderBy('expiry_date')
            ->get();

        $csv = "Worker Number,Worker Name,Document Type,Expiry Date,Days Remaining,Status\n";

        foreach ($documents as $doc) {
            $csv .= implode(',', [
                $doc->worker->worker_number,
                '"'.$doc->worker->first_name.' '.$doc->worker->last_name.'"',
                $doc->document_type,
                $doc->expiry_date->toDateString(),
                $doc->daysUntilExpiry() ?? 'N/A',
                $doc->status,
            ])."\n";
        }

        return $csv;
    }

    /**
     * Export missing documents report
     */
    public function exportMissingReport(): string
    {
        $documents = WorkerComplianceDocument::where('status', 'Missing')
            ->with('worker')
            ->orderBy('worker_id')
            ->get();

        $csv = "Worker Number,Worker Name,Document Type,Status\n";

        foreach ($documents as $doc) {
            $csv .= implode(',', [
                $doc->worker->worker_number,
                '"'.$doc->worker->first_name.' '.$doc->worker->last_name.'"',
                $doc->document_type,
                $doc->status,
            ])."\n";
        }

        return $csv;
    }

    /**
     * Export worker compliance summary
     */
    public function exportWorkerComplianceSummary(): string
    {
        $workers = Worker::with('complianceDocuments')->get();

        $csv = "Worker Number,Worker Name,Total Documents,Active,Expiring Soon,Expired,Missing,Compliance Status\n";

        foreach ($workers as $worker) {
            $documents = $worker->complianceDocuments;
            $complianceStatus = $documents->where('status', 'Expired')->isEmpty() &&
                               $documents->where('status', 'Missing')->isEmpty()
                ? 'Compliant'
                : 'Non-Compliant';

            $csv .= implode(',', [
                $worker->worker_number,
                '"'.$worker->first_name.' '.$worker->last_name.'"',
                $documents->count(),
                $documents->where('status', 'Active')->count(),
                $documents->where('status', 'Expiring Soon')->count(),
                $documents->where('status', 'Expired')->count(),
                $documents->where('status', 'Missing')->count(),
                $complianceStatus,
            ])."\n";
        }

        return $csv;
    }

    /**
     * Export rejected documents report
     */
    public function exportRejectedReport(): string
    {
        $documents = WorkerComplianceDocument::where('status', 'Rejected')
            ->with('worker')
            ->orderByDesc('rejected_at')
            ->get();

        $csv = "Worker Number,Worker Name,Document Type,Rejection Reason,Rejected Date\n";

        foreach ($documents as $doc) {
            $csv .= implode(',', [
                $doc->worker->worker_number,
                '"'.$doc->worker->first_name.' '.$doc->worker->last_name.'"',
                $doc->document_type,
                '"'.str_replace('"', '""', $doc->rejection_reason ?? '').'"',
                $doc->rejected_at?->toDateString() ?? '',
            ])."\n";
        }

        return $csv;
    }

    /**
     * Get documents for report type
     */
    private function getDocumentsForReport(string $reportType): Collection
    {
        return match ($reportType) {
            'expiring' => $this->getExpiringDocuments(),
            'expired' => $this->getExpiredDocuments(),
            'missing' => $this->getMissingDocuments(),
            'rejected' => $this->getRejectedDocuments(),
            default => WorkerComplianceDocument::with('worker')->get(),
        };
    }

    /**
     * Get expiring documents
     */
    private function getExpiringDocuments(): Collection
    {
        $targetDate = now()->addDays(30);

        return WorkerComplianceDocument::whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->toDateString(), $targetDate->toDateString()])
            ->where('status', '!=', 'Expired')
            ->with('worker')
            ->get();
    }

    /**
     * Get expired documents
     */
    private function getExpiredDocuments(): Collection
    {
        return WorkerComplianceDocument::where('status', 'Expired')
            ->with('worker')
            ->get();
    }

    /**
     * Get missing documents
     */
    private function getMissingDocuments(): Collection
    {
        return WorkerComplianceDocument::where('status', 'Missing')
            ->with('worker')
            ->get();
    }

    /**
     * Get rejected documents
     */
    private function getRejectedDocuments(): Collection
    {
        return WorkerComplianceDocument::where('status', 'Rejected')
            ->with('worker')
            ->get();
    }

    /**
     * Generate timestamped filename
     */
    public function generateFilename(string $reportType = 'compliance', string $format = 'csv'): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');

        return "compliance_report_{$reportType}_{$timestamp}.{$format}";
    }
}
