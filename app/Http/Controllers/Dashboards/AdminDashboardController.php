<?php

namespace App\Http\Controllers\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\Enquiry;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PortalNotification;
use App\Models\PreApprovalRequest;
use App\Models\Budget;
use Illuminate\Support\Facades\Schema;
use App\Models\Shift;
use App\Models\Worker;
use App\Services\MessageService;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // PARTICIPANTS & ONBOARDING
        $participantsCount = Participant::count();
        $participantsOnboarding = Participant::where('status', 'onboarding')->count();
        $newEnquiriesCount = Enquiry::where('status', Enquiry::STATUS_NEW)->count();

        // WORKERS & APPROVALS
        $workersCount = Worker::count();
        $workersPending = Worker::whereIn('status', ['pending', 'pending_approval'])->count();

        // DOCUMENTS & SIGNATURES
        $pendingDocuments = Document::where('status', '!=', 'signed')->count();

        // INVOICES
        $submittedInvoices = Invoice::where('status', 'submitted')->count();
        $invoicePendingAmount = Invoice::where('status', 'submitted')
            ->sum('amount_cents') / 100; // Convert to dollars

        // INCIDENTS & COMPLAINTS
        $riskAlerts = Incident::where('status', 'open')->count() +
                  Complaint::where('status', 'open')->count();

        $openIncidents = Incident::where('status', 'open')->count();
        $highSeverityIncidents = Incident::where('severity', 'high')->count();

        $unreadMessages = MessageService::getUnreadCount(auth()->id());
        $unreadNotificationsCount = PortalNotification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        $todaysShifts = Shift::whereDate('shift_date', now()->toDateString())->count();
        $upcomingShifts = Shift::whereDate('shift_date', '>', now()->toDateString())->count();
        $missedShifts = Shift::whereDate('shift_date', '<', now()->toDateString())
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->count();
        $unconfirmedShifts = Shift::where('status', Shift::STATUS_SCHEDULED)->count();

        // PRE-APPROVALS
        $pendingApprovals = PreApprovalRequest::where('status', 'pending')->count();

        // BUDGET DATA FOR PARTICIPANTS (including committed amounts)
        $budgetService = new \App\Services\BudgetService();
        $participants = Participant::latest()->take(5)->get();
        $budgetData = $participants->map(function ($participant) use ($budgetService) {
            try {
                $budget = $budgetService->getOrCreateBudgetForParticipantQuarter($participant);
                $metrics = $budgetService->getBudgetMetrics($budget);
            } catch (\Throwable $_) {
                $metrics = [
                    'total_available' => (int) ($participant->budget_limit_cents ?? 0),
                    'used' => (int) ($participant->current_budget_used_cents ?? 0),
                    'committed' => 0,
                    'remaining' => (int) (($participant->budget_limit_cents ?? 0) - ($participant->current_budget_used_cents ?? 0)),
                ];
            }

            // Ensure values are integers (cents) then convert to dollars for the view
            $totalAvailable = (int) ($metrics['total_available'] ?? 0);
            $usedCents = (int) ($metrics['used'] ?? 0);
            $committedCents = (int) ($metrics['committed'] ?? 0);
            $remainingCents = (int) ($metrics['remaining'] ?? ($totalAvailable - $usedCents));

            return [
                'name' => trim(($participant->first_name ?? '') . ' ' . ($participant->last_name ?? '')) ?: ($participant->name ?? 'Participant'),
                'budget' => $totalAvailable / 100,
                'used' => $usedCents / 100,
                'committed' => $committedCents / 100,
                'remaining' => $remainingCents / 100,
                'utilization_percent' => isset($metrics['utilization_percent']) ? $metrics['utilization_percent'] : null,
            ];
        });

        // Total committed across budgets (if stored in cents column)
        $totalCommittedCents = 0;
        if (Schema::hasTable('budgets') && Schema::hasColumn('budgets', 'committed_cents')) {
            $totalCommittedCents = (int) Budget::sum('committed_cents');
        }
        if (empty($totalCommittedCents)) {
            try {
                $totalCommittedCents = (int) \App\Models\Invoice::whereNotNull('committed_amount_cents')->sum('committed_amount_cents');
            } catch (\Throwable $_) {
                // leave as zero
            }
        }

        // RECENT ACTIVITY
        $recentActivity = AuditLog::with('user')->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'participantsCount',
            'participantsOnboarding',
            'newEnquiriesCount',
            'workersCount',
            'workersPending',
            'pendingDocuments',
            'submittedInvoices',
            'invoicePendingAmount',
            'riskAlerts',
            'openIncidents',
            'highSeverityIncidents',
            'unreadMessages',
            'unreadNotificationsCount',
            'todaysShifts',
            'upcomingShifts',
            'missedShifts',
            'unconfirmedShifts',
            'pendingApprovals',
            'budgetData',
            'recentActivity',
            'totalCommittedCents'
        ));
    }
}
