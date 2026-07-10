<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Assessment;
use App\Models\Budget;
use App\Models\CareNote;
use App\Models\Document;
use App\Models\EmailTemplate;
use App\Models\Enquiry;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\MessageTemplate;
use App\Models\OnboardingSubmission;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use App\Models\ParticipantAssignment;
use App\Models\PreApprovalRequest;
use App\Models\SupportConversation;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerNomination;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class AdminSearchController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));

        $results = [];

        if ($search !== '') {
            $results = array_merge(
                $this->searchParticipants($search),
                $this->searchWorkers($search),
                $this->searchInvoices($search),
                $this->searchDocuments($search),
                $this->searchEnquiries($search),
                $this->searchIncidents($search),
                $this->searchUsers($search),
                $this->searchApplications($search),
                $this->searchOnboardingSubmissions($search),
                $this->searchAgreements($search),
                $this->searchSupportTickets($search),
                $this->searchSupportConversations($search),
                $this->searchMessageTemplates($search),
                $this->searchEmailTemplates($search),
                $this->searchPreApprovalRequests($search),
                $this->searchAssessments($search),
                $this->searchWorkerNominations($search),
                $this->searchParticipantAssignments($search),
                $this->searchCareNotes($search),
                $this->searchBudgets($search)
            );
        }

        return view('admin.search.results', compact('search', 'results'));
    }

    protected function searchParticipants(string $search): array
    {
        return Participant::select(['id', 'first_name', 'last_name', 'participant_number', 'email', 'phone'])
            ->where(function ($query) use ($search) {
                $query->where('participant_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($participant) => [
                'type' => 'Participant',
                'title' => $participant->first_name . ' ' . $participant->last_name,
                'subtitle' => $participant->participant_number,
                'description' => $participant->email . ' · ' . $participant->phone,
                'url' => route('portal.admin.participants.show', $participant),
            ])
            ->toArray();
    }

    protected function searchWorkers(string $search): array
    {
        return Worker::select(['id', 'first_name', 'last_name', 'worker_number', 'email', 'phone'])
            ->where(function ($query) use ($search) {
                $query->where('worker_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($worker) => [
                'type' => 'Worker',
                'title' => $worker->first_name . ' ' . $worker->last_name,
                'subtitle' => $worker->worker_number,
                'description' => $worker->email . ' · ' . $worker->phone,
                'url' => route('portal.admin.workers.edit', $worker),
            ])
            ->toArray();
    }

    protected function searchInvoices(string $search): array
    {
        return Invoice::select(['id', 'invoice_number', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($invoice) => [
                'type' => 'Invoice',
                'title' => $invoice->invoice_number,
                'subtitle' => ucfirst($invoice->status),
                'description' => '',
                'url' => route('portal.admin.invoices.show', $invoice),
            ])
            ->toArray();
    }

    protected function searchDocuments(string $search): array
    {
        return Document::select(['id', 'title', 'document_type', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('document_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($document) => [
                'type' => 'Document',
                'title' => $document->title,
                'subtitle' => $document->document_type,
                'description' => ucfirst($document->status),
                'url' => route('portal.admin.documents.show', $document),
            ])
            ->toArray();
    }

    protected function searchEnquiries(string $search): array
    {
        return Enquiry::select(['id', 'name', 'email', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($enquiry) => [
                'type' => 'Enquiry',
                'title' => $enquiry->name,
                'subtitle' => $enquiry->email,
                'description' => ucfirst($enquiry->status),
                'url' => route('portal.admin.enquiries.show', $enquiry),
            ])
            ->toArray();
    }

    protected function searchIncidents(string $search): array
    {
        return Incident::select(['id', 'incident_type', 'status', 'description'])
            ->where(function ($query) use ($search) {
                $query->where('incident_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($incident) => [
                'type' => 'Incident',
                'title' => ucfirst($incident->incident_type),
                'subtitle' => ucfirst($incident->status),
                'description' => Str::limit($incident->description, 80),
                'url' => $this->routeUrl('portal.admin.incidents.show', $incident),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchUsers(string $search): array
    {
        return User::select(['id', 'name', 'email', 'phone', 'role', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($user) => [
                'type' => 'User',
                'title' => $user->name,
                'subtitle' => $user->email,
                'description' => ucfirst($user->role) . ' · ' . ucfirst($user->status),
                'url' => $this->routeUrl('portal.admin.users.show', $user),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchApplications(string $search): array
    {
        return ParticipantApplication::select(['id', 'first_name', 'last_name', 'email', 'phone', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($application) => [
                'type' => 'Application',
                'title' => $application->first_name . ' ' . $application->last_name,
                'subtitle' => $application->email,
                'description' => ucfirst($application->status),
                'url' => $this->routeUrl('admin.applications.show', $application),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchOnboardingSubmissions(string $search): array
    {
        return OnboardingSubmission::with('participant')
            ->select(['id', 'participant_id', 'status', 'admin_comments'])
            ->where(function ($query) use ($search) {
                $query->where('status', 'like', "%{$search}%")
                    ->orWhere('admin_comments', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($submission) => [
                'type' => 'Onboarding',
                'title' => $submission->participant?->name ?? 'Submission #' . $submission->id,
                'subtitle' => 'Submission #' . $submission->id,
                'description' => ucfirst($submission->status),
                'url' => $this->routeUrl('admin.onboarding.show', $submission),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchAgreements(string $search): array
    {
        return Agreement::select(['id', 'title', 'description', 'version'])
            ->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($agreement) => [
                'type' => 'Agreement',
                'title' => $agreement->title,
                'subtitle' => 'Version ' . $agreement->version,
                'description' => Str::limit($agreement->description, 80),
                'url' => $this->routeUrl('admin.agreements.edit', $agreement),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchSupportTickets(string $search): array
    {
        return SupportTicket::with('user')
            ->select(['id', 'user_id', 'subject', 'description', 'status', 'category'])
            ->where(function ($query) use ($search) {
                $query->where('subject', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->limit(10)
            ->get()
            ->map(fn($ticket) => [
                'type' => 'Support Ticket',
                'title' => $ticket->subject,
                'subtitle' => $ticket->user?->name ?? 'Ticket #' . $ticket->id,
                'description' => ucfirst($ticket->status) . ' · ' . $ticket->category,
                'url' => $this->routeUrl('portal.admin.support.ticket.show', $ticket),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchSupportConversations(string $search): array
    {
        return SupportConversation::select(['id', 'subject', 'submitted_name', 'submitted_email', 'status', 'priority'])
            ->where(function ($query) use ($search) {
                $query->where('subject', 'like', "%{$search}%")
                    ->orWhere('submitted_name', 'like', "%{$search}%")
                    ->orWhere('submitted_email', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('priority', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($conversation) => [
                'type' => 'Support Conversation',
                'title' => $conversation->subject,
                'subtitle' => $conversation->submitted_name ?: $conversation->submitted_email,
                'description' => ucfirst($conversation->status) . ' · ' . ucfirst($conversation->priority),
                'url' => $this->routeUrl('portal.admin.support.conversation.show', $conversation),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchMessageTemplates(string $search): array
    {
        return MessageTemplate::select(['id', 'name', 'subject', 'body', 'type', 'category'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($template) => [
                'type' => 'Message Template',
                'title' => $template->name,
                'subtitle' => $template->type,
                'description' => Str::limit($template->subject, 80),
                'url' => $this->routeUrl('portal.admin.messages.templates.edit', $template),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchEmailTemplates(string $search): array
    {
        return EmailTemplate::select(['id', 'name', 'slug', 'subject', 'category'])
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($template) => [
                'type' => 'Email Template',
                'title' => $template->name,
                'subtitle' => $template->slug,
                'description' => Str::limit($template->subject, 80),
                'url' => $this->routeUrl('portal.admin.messages.email_templates.edit', $template),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchPreApprovalRequests(string $search): array
    {
        return PreApprovalRequest::select(['id', 'request_number', 'service_type', 'service_category', 'purpose', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('request_number', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('service_category', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($request) => [
                'type' => 'Pre-Approval',
                'title' => $request->request_number,
                'subtitle' => $request->service_type,
                'description' => ucfirst($request->status),
                'url' => $this->routeUrl('portal.admin.pre_approvals.show', $request),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchAssessments(string $search): array
    {
        return Assessment::with('participant')
            ->select(['id', 'participant_id', 'status', 'enquiry_source', 'support_person_name', 'support_person_email', 'support_person_phone', 'overall_decision'])
            ->where(function ($query) use ($search) {
                $query->where('status', 'like', "%{$search}%")
                    ->orWhere('enquiry_source', 'like', "%{$search}%")
                    ->orWhere('support_person_name', 'like', "%{$search}%")
                    ->orWhere('support_person_email', 'like', "%{$search}%")
                    ->orWhere('support_person_phone', 'like', "%{$search}%")
                    ->orWhere('overall_decision', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($assessment) => [
                'type' => 'Assessment',
                'title' => 'Assessment #' . $assessment->id,
                'subtitle' => $assessment->participant?->first_name . ' ' . $assessment->participant?->last_name,
                'description' => ucfirst($assessment->status) . ' · ' . ucfirst($assessment->overall_decision),
                'url' => $this->routeUrl('admin.assessments.show', $assessment),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchBudgets(string $search): array
    {
        return Budget::with('participant')
            ->where(function ($query) use ($search) {
                $query->where('assignment_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('quarter_start', 'like', "%{$search}%")
                    ->orWhere('quarter_end', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($budget) => [
                'type' => 'Budget',
                'title' => 'Budget #' . $budget->id,
                'subtitle' => $budget->participant?->first_name . ' ' . $budget->participant?->last_name,
                'description' => 'Quarter ' . ($budget->quarter_start?->format('Y-m-d') ?? 'N/A') . ' → ' . ($budget->quarter_end?->format('Y-m-d') ?? 'N/A'),
                'url' => $this->routeUrl('portal.admin.budgets'),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchWorkerNominations(string $search): array
    {
        return WorkerNomination::select(['id', 'worker_full_name', 'worker_email', 'worker_phone', 'service_type', 'status'])
            ->where(function ($query) use ($search) {
                $query->where('worker_full_name', 'like', "%{$search}%")
                    ->orWhere('worker_email', 'like', "%{$search}%")
                    ->orWhere('worker_phone', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(fn($nomination) => [
                'type' => 'Nomination',
                'title' => $nomination->worker_full_name,
                'subtitle' => $nomination->worker_email,
                'description' => ucfirst($nomination->status),
                'url' => $this->routeUrl('portal.admin.nominations.show', $nomination),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchParticipantAssignments(string $search): array
    {
        return ParticipantAssignment::with(['participant', 'worker'])
            ->where(function ($query) use ($search) {
                $query->where('assignment_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('participant', fn($query) => $query->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"))
                    ->orWhereHas('worker', fn($query) => $query->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            })
            ->limit(10)
            ->get()
            ->map(fn($assignment) => [
                'type' => 'Assignment',
                'title' => $assignment->participant?->first_name . ' ' . $assignment->participant?->last_name,
                'subtitle' => $assignment->worker?->first_name . ' ' . $assignment->worker?->last_name,
                'description' => ucfirst($assignment->assignment_type) . ' · ' . ucfirst($assignment->status),
                'url' => $this->routeUrl('portal.admin.assignments.show', $assignment),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function searchCareNotes(string $search): array
    {
        return CareNote::with(['participant', 'worker'])
            ->where(function ($query) use ($search) {
                $query->where('tasks_completed', 'like', "%{$search}%")
                    ->orWhere('observations', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('participant', fn($query) => $query->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%"));
            })
            ->limit(10)
            ->get()
            ->map(fn($note) => [
                'type' => 'Care Note',
                'title' => $note->participant?->first_name . ' ' . $note->participant?->last_name,
                'subtitle' => $note->service_type,
                'description' => ucfirst($note->status),
                'url' => $this->routeUrl('portal.admin.care_notes.show', $note),
            ])
            ->filter(fn($result) => ! empty($result['url']))
            ->toArray();
    }

    protected function routeUrl(string $routeName, $parameter = null): ?string
    {
        if (! Route::has($routeName)) {
            return null;
        }

        try {
            return $parameter !== null
                ? route($routeName, $parameter)
                : route($routeName);
        } catch (\Exception $e) {
            return null;
        }
    }
}
