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

        // BUDGET DATA FOR PARTICIPANTS
        $budgetData = Participant::select('id', 'first_name', 'budget_limit_cents', 'current_budget_used_cents')
            ->orderByDesc('budget_limit_cents')
            ->get()
            ->map(function ($participant) {
                return [
                    'name' => $participant->first_name,
                    'budget' => $participant->budget_limit_cents / 100,
                    'used' => $participant->current_budget_used_cents / 100,
                    'remaining' => ($participant->budget_limit_cents - $participant->current_budget_used_cents) / 100,
                ];
            })
            ->take(5); // Top 5 for dashboard display

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
            'recentActivity'
        ));
    }
}
