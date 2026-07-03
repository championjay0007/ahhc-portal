<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\CareNote;
use App\Models\Complaint;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\MonthlyCareReview;
use App\Models\PreApprovalRequest;
use App\Models\WorkerComplianceDocument;
use Illuminate\Database\Eloquent\Collection;

class ReportService
{
    /**
     * Get participant budget report
     */
    public function getParticipantBudgetReport(array $filters = []): Collection
    {
        $query = Budget::with('participant', 'transactions');

        if (! empty($filters['participant_id'])) {
            $query->where('participant_id', $filters['participant_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('quarter_start_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('quarter_end_date', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($budget) {
            $categorySpend = $budget->transactions
                ->groupBy('reference_type')
                ->mapWithKeys(fn ($group, $type) => [
                    $type ?? 'other' => number_format($group->sum('amount_cents') / 100, 2),
                ])
                ->toArray();

            $ledger = $budget->transactions->map(fn ($transaction) => sprintf(
                '%s | %s | %s | %s',
                $transaction->created_at?->toDateString() ?? 'N/A',
                ucfirst($transaction->type),
                $this->formatCurrency($transaction->amount_cents),
                $transaction->description,
            ))->toArray();

            return [
                'participant' => $budget->participant->first_name.' '.$budget->participant->last_name,
                'participant_id' => $budget->participant_id,
                'quarter_start_date' => $budget->quarter_start_date?->toDateString(),
                'quarter_end_date' => $budget->quarter_end_date?->toDateString(),
                'opening_balance' => $this->formatCurrency($budget->opening_balance_cents),
                'carry_over' => $this->formatCurrency($budget->carry_over_cents),
                'committed' => $this->formatCurrency($budget->committed_cents),
                'approved' => $this->formatCurrency($budget->approved_spend_cents),
                'paid' => $this->formatCurrency($budget->paid_spend_cents),
                'remaining' => $this->formatCurrency($this->calculateRemaining($budget)),
                'total_available' => $this->formatCurrency($this->calculateTotalAvailable($budget)),
                'category_spend' => $categorySpend,
                'ledger' => $ledger,
                'status' => 'active',
                'created_date' => $budget->created_at->toDateString(),
            ];
        });
    }

    protected function formatCurrency(int $amountCents): string
    {
        return number_format($amountCents / 100, 2);
    }

    /**
     * Get invoice report
     */
    public function getInvoiceReport(array $filters = []): Collection
    {
        $query = Invoice::with('participant', 'worker');

        if (! empty($filters['participant_id'])) {
            $query->where('participant_id', $filters['participant_id']);
        }

        if (! empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($invoice) {
            return [
                'invoice_id' => $invoice->id,
                'participant' => $invoice->participant->first_name.' '.$invoice->participant->last_name,
                'worker' => $invoice->worker->name ?? 'N/A',
                'amount' => $invoice->amount,
                'status' => $invoice->status,
                'invoice_date' => $invoice->invoice_date?->toDateString(),
                'due_date' => $invoice->due_date?->toDateString(),
                'description' => $invoice->description,
                'created_date' => $invoice->created_at->toDateString(),
            ];
        });
    }

    /**
     * Get care notes report
     */
    public function getCareNotesReport(array $filters = []): Collection
    {
        $query = CareNote::with('participant', 'worker');

        if (! empty($filters['participant_id'])) {
            $query->where('participant_id', $filters['participant_id']);
        }

        if (! empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($note) {
            return [
                'note_id' => $note->id,
                'participant' => $note->participant->first_name.' '.$note->participant->last_name,
                'worker' => $note->worker->name ?? 'N/A',
                'note_type' => $note->note_type ?? 'General',
                'note_content' => substr($note->note_content, 0, 100).(strlen($note->note_content) > 100 ? '...' : ''),
                'created_by' => $note->worker->name ?? 'Unknown',
                'created_date' => $note->created_at->toDateString(),
                'created_time' => $note->created_at->format('H:i:s'),
            ];
        });
    }

    /**
     * Get incident report
     */
    public function getIncidentReport(array $filters = []): Collection
    {
        $query = Incident::with('participant', 'reporter');

        if (! empty($filters['participant_id'])) {
            $query->where('participant_id', $filters['participant_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->where('incident_date', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('incident_date', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($incident) {
            return [
                'incident_id' => $incident->id,
                'participant' => $incident->participant->first_name.' '.$incident->participant->last_name,
                'type' => $incident->type ?? 'General',
                'severity' => $incident->severity ?? 'Low',
                'status' => $incident->status,
                'incident_date' => $incident->incident_date?->toDateString(),
                'description' => substr($incident->description, 0, 100).(strlen($incident->description) > 100 ? '...' : ''),
                'reported_by' => $incident->reporter?->name ?? 'Unknown',
                'created_date' => $incident->created_at->toDateString(),
            ];
        });
    }

    /**
     * Get incident / complaint report
     */
    public function getIncidentComplaintReport(array $filters = []): Collection
    {
        $incidentQuery = Incident::with('participant', 'reporter');
        $complaintQuery = Complaint::with('participant', 'submitter');

        if (! empty($filters['participant_id'])) {
            $incidentQuery->where('participant_id', $filters['participant_id']);
            $complaintQuery->where('participant_id', $filters['participant_id']);
        }

        if (! empty($filters['status'])) {
            $incidentQuery->where('status', $filters['status']);
            $complaintQuery->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $incidentQuery->where('occurred_at', '>=', $filters['start_date']);
            $complaintQuery->where('received_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $incidentQuery->where('occurred_at', '<=', $filters['end_date']);
            $complaintQuery->where('received_at', '<=', $filters['end_date']);
        }

        $incidents = $incidentQuery->get()->map(function ($incident) {
            return [
                'issue_id' => $incident->id,
                'issue_type' => 'Incident',
                'category' => $incident->type ?? 'General',
                'severity' => $incident->severity ?? 'Low',
                'status' => $incident->status,
                'action_taken' => $incident->action_taken ?? 'Pending',
                'outcome' => $incident->status,
                'reported_by' => $incident->reporter?->name ?? 'Unknown',
                'participant' => $incident->participant->first_name.' '.$incident->participant->last_name,
                'occurred_date' => $incident->occurred_at?->toDateString() ?? $incident->incident_date?->toDateString(),
                'description' => substr($incident->description ?? '', 0, 120).(strlen($incident->description ?? '') > 120 ? '...' : ''),
            ];
        });

        $complaints = $complaintQuery->get()->map(function ($complaint) {
            return [
                'issue_id' => $complaint->id,
                'issue_type' => 'Complaint',
                'category' => $complaint->category ?? 'General',
                'severity' => $complaint->priority ?? 'Normal',
                'status' => $complaint->status,
                'action_taken' => $complaint->notes ?? 'Pending',
                'outcome' => $complaint->resolved_at ? 'Resolved' : 'Open',
                'reported_by' => $complaint->submitter?->name ?? 'Unknown',
                'participant' => $complaint->participant->first_name.' '.$complaint->participant->last_name,
                'occurred_date' => $complaint->received_at?->toDateString(),
                'description' => substr($complaint->description ?? '', 0, 120).(strlen($complaint->description ?? '') > 120 ? '...' : ''),
            ];
        });

        return $incidents->concat($complaints)->sortByDesc('occurred_date')->values();
    }

    /**
     * Get compliance report
     */
    public function getComplianceReport(array $filters = []): Collection
    {
        $query = WorkerComplianceDocument::with('worker');

        if (! empty($filters['worker_id'])) {
            $query->where('worker_id', $filters['worker_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($doc) {
            $expired = $doc->expiry_date && $doc->expiry_date->isPast();

            return [
                'document_id' => $doc->id,
                'worker' => $doc->worker->name,
                'document_type' => $doc->document_type,
                'status' => ucfirst($doc->status),
                'approved_or_blocked' => $doc->status === 'blocked' ? 'Blocked' : ucfirst($doc->status),
                'expiry_date' => $doc->expiry_date?->toDateString(),
                'days_until_expiry' => $doc->expiry_date ? now()->diffInDays($doc->expiry_date, false) : 'N/A',
                'missing_checks' => $doc->expiry_date ? ($expired ? 'Yes' : 'No') : 'Yes',
                'is_blocked' => $doc->status === 'blocked' ? 'Yes' : 'No',
                'verified_by' => $doc->verified_by_name ?? 'Pending',
                'created_date' => $doc->created_at->toDateString(),
            ];
        });
    }

    /**
     * Get pre-approval request report
     */
    public function getPreApprovalReport(array $filters = []): Collection
    {
        $query = PreApprovalRequest::with('participant', 'worker', 'approver');

        if (! empty($filters['participant_id'])) {
            $query->where('participant_id', $filters['participant_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($request) {
            return [
                'request_id' => $request->id,
                'participant' => $request->participant->first_name.' '.$request->participant->last_name,
                'amount' => $request->requested_amount_cents ? $request->requested_amount_cents / 100 : 0,
                'status' => $request->status,
                'reason' => substr($request->description ?? $request->purpose ?? '', 0, 100).(strlen($request->description ?? $request->purpose ?? '') > 100 ? '...' : ''),
                'requested_by' => $request->worker?->name ?? $request->supportPerson?->name ?? 'Unknown',
                'approved_by' => $request->approver?->name ?? 'Pending',
                'requested_date' => $request->submitted_at?->toDateString() ?? $request->created_at->toDateString(),
            ];
        });
    }

    /**
     * Get monthly care review report
     */
    public function getCareReviewReport(array $filters = []): Collection
    {
        $query = MonthlyCareReview::with('participant', 'careManager', 'completedBy');

        if (! empty($filters['participant_id'])) {
            $query->where('participant_id', $filters['participant_id']);
        }

        if (! empty($filters['care_manager_id'])) {
            $query->where('care_manager_id', $filters['care_manager_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($review) {
            return [
                'review_id' => $review->id,
                'participant' => $review->participant->first_name.' '.$review->participant->last_name,
                'care_manager' => $review->careManager->name,
                'review_type' => $review->review_type,
                'status' => $review->status,
                'next_review_date' => $review->next_review_date?->toDateString(),
                'completed_date' => $review->completed_at?->toDateString(),
                'completed_by' => $review->completedBy?->name ?? 'Pending',
                'has_concerns' => ! empty($review->concerns) ? 'Yes' : 'No',
                'has_actions' => ! empty($review->actions_required) ? 'Yes' : 'No',
                'created_date' => $review->created_at->toDateString(),
            ];
        });
    }

    /**
     * Get audit log report
     */
    public function getAuditLogReport(array $filters = []): Collection
    {
        $query = AuditLog::query();

        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        return $query->get()->map(function ($log) {
            return [
                'log_id' => $log->id,
                'action' => $log->action,
                'subject_type' => $log->subject_type ?? 'Unknown',
                'subject_id' => $log->subject_id ?? 'N/A',
                'user' => $log->user?->name ?? 'Unknown',
                'description' => $log->description,
                'ip_address' => $log->ip_address,
                'created_date' => $log->created_at->toDateString(),
                'created_time' => $log->created_at->format('H:i:s'),
            ];
        });
    }

    /**
     * Get all report data
     */
    public function getReportData(string $reportType, array $filters = []): Collection
    {
        return match ($reportType) {
            'Participant Budget' => $this->getParticipantBudgetReport($filters),
            'Invoice' => $this->getInvoiceReport($filters),
            'Care Notes' => $this->getCareNotesReport($filters),
            'Incident' => $this->getIncidentReport($filters),
            'Incident / Complaint' => $this->getIncidentComplaintReport($filters),
            'Compliance' => $this->getComplianceReport($filters),
            'Pre-Approval' => $this->getPreApprovalReport($filters),
            'Care Review' => $this->getCareReviewReport($filters),
            'Audit Log' => $this->getAuditLogReport($filters),
            default => collect([]),
        };
    }
}
