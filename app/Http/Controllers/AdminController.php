<?php

namespace App\Http\Controllers;

use App\Enums\ExportFormat;
use App\Http\Requests\ApprovePreApprovalRequest;
use App\Http\Requests\RejectPreApprovalRequest;
use App\Mail\ParticipantOnboardingInvitation;
use App\Mail\PortalTestEmail;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Budget;
use App\Models\CareNote;
use App\Models\Complaint;
use App\Models\Document;
use App\Models\DocumentSignature;
use App\Models\DocumentVersion;
use App\Models\Incident;
use App\Models\Invoice;
use App\Models\MonthlyCareReview;
use App\Models\OnboardingProgress;
use App\Models\Participant;
use App\Models\ParticipantAssignment;
use App\Models\PortalSetting;
use App\Models\PreApprovalAttachment;
use App\Models\PreApprovalComment;
use App\Models\PreApprovalRequest;
use App\Models\SupportPerson;
use App\Models\User;
use App\Models\Worker;
use App\Services\AuditLogService;
use App\Services\BudgetService;
use App\Services\NotificationCenterService;
use App\Services\NotificationService;
use App\Services\OnboardingAgreementService;
use App\Services\RiskScoringService;
use App\Services\SignatureRequestService;
use App\Services\TemplateMailer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Minishlink\WebPush\VAPID;

class AdminController extends Controller
{
    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    public function index()
    {
        $participantsCount = Participant::count();
        $workersCount = Worker::count();
        $pendingApprovals = PreApprovalRequest::where('status', 'submitted')->count();
        $submittedInvoices = Invoice::where('status', 'submitted')->count();
        $openComplaints = Complaint::where('status', 'open')->count();
        $followUpIncidents = Incident::where('status', 'open')->orWhere('follow_up_required', true)->count();
        $missingCareNotes = Participant::whereDoesntHave('careNotes', function ($query) {
            $query->where('shift_date', '>=', now()->subDays(7));
        })->count();
        $expiringCompliance = Worker::where(function ($query) {
            $query->where('compliance_expiry_at', '<=', now()->addDays(30))
                ->orWhere('background_check_expiry_at', '<=', now()->addDays(30));
        })->count();
        $recentActivity = AuditLog::with('user')->latest()->take(5)->get();
        $pendingDocuments = Document::where('status', 'uploaded')->count();

        return view('admin.dashboard', compact(
            'participantsCount',
            'workersCount',
            'pendingApprovals',
            'submittedInvoices',
            'openComplaints',
            'followUpIncidents',
            'missingCareNotes',
            'expiringCompliance',
            'pendingDocuments',
            'recentActivity'
        ));
    }

    public function participants(Request $request)
    {
        $query = Participant::with(['user', 'assignments.worker'])
            ->orderBy('id', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('participant_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $participants = $query->paginate(20)->withQueryString();
        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.participants', compact('participants', 'workers'));
    }

    public function showParticipant(Participant $participant)
    {
        $participant->load(['user', 'assignments.worker', 'careNotes', 'incidents', 'invoices', 'preApprovalRequests', 'complaints', 'monthlyReviews', 'riskScores', 'participantStatusHistories', 'participantStatusHistories.changedBy', 'supportPerson']);

        $riskScoringService = new RiskScoringService;
        $currentRisk = $riskScoringService->recordScore($participant, auth()->id());
        $riskHistory = $participant->riskScores()->latest('calculated_at')->take(10)->get();

        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();
        $pendingDocumentsCount = Document::where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->where('status', '!=', 'signed')
            ->count();
        $documentTypes = $this->documentTypeOptions();

        $onboardingProgress = OnboardingProgress::where('participant_id', $participant->id)->first();
        $documents = Document::where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->orderByDesc('created_at')
            ->get();

        $requiredAgreements = array_values(OnboardingAgreementService::requiredAgreements());
        $signedAgreements = Document::where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->where('status', 'signed')
            ->whereIn('document_type', $requiredAgreements)
            ->pluck('document_type')
            ->unique();
        $missingAgreements = collect($requiredAgreements)->diff($signedAgreements)->values();

        $budget = $this->budgetService->getOrCreateBudgetForParticipantQuarter($participant);
        $budgetMetrics = $this->budgetService->getBudgetMetrics($budget);
        $budgetTransactions = $budget->transactions()->orderByDesc('created_at')->get();
        $budgetCategories = $this->budgetService->getBudgetCategorySpend($budget);
        $budgetAlerts = $this->budgetService->getBudgetAlerts($budget, $participant);
        $auditEntries = AuditLog::where('subject_type', Participant::class)
            ->where('subject_id', $participant->id)
            ->latest()
            ->take(10)
            ->get();
        $exportFormats = ExportFormat::options();

        return view('admin.participant', compact(
            'participant',
            'workers',
            'pendingDocumentsCount',
            'documentTypes',
            'budget',
            'budgetMetrics',
            'budgetTransactions',
            'budgetCategories',
            'budgetAlerts',
            'auditEntries',
            'exportFormats',
            'currentRisk',
            'riskHistory',
            'onboardingProgress',
            'documents',
            'requiredAgreements',
            'signedAgreements',
            'missingAgreements'
        ));
    }

    public function budgets(Request $request)
    {
        $budgetService = new BudgetService;
        $currentQuarter = now();
        $currentQuarterPeriod = $budgetService->getQuarterPeriodForDate($currentQuarter);

        $query = Participant::query()
            ->select('id', 'first_name', 'last_name', 'participant_number', 'status', 'email', 'phone')
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('participant_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $participants = $query->paginate(20)->withQueryString();

        $participants->getCollection()->transform(function (Participant $participant) use ($budgetService, $currentQuarterPeriod) {
            $budget = Budget::firstOrCreate([
                'participant_id' => $participant->id,
                'quarter_start_date' => $currentQuarterPeriod['quarter_start_date'],
                'quarter_end_date' => $currentQuarterPeriod['quarter_end_date'],
            ], [
                'opening_balance_cents' => 0,
                'carry_over_cents' => 0,
                'committed_cents' => 0,
                'approved_spend_cents' => 0,
                'paid_spend_cents' => 0,
            ]);

            $participant->current_budget = $budgetService->calculateTotalAvailable($budget);
            $participant->committed = $budget->committed_cents;
            $participant->approved_spend = $budget->approved_spend_cents;
            $participant->paid_spend = $budget->paid_spend_cents;
            $participant->remaining_budget = $budgetService->calculateRemaining($budget);
            $participant->utilization = $participant->current_budget ? min(100, round((($participant->committed + $participant->approved_spend) / $participant->current_budget) * 100, 1)) : 0;

            return $participant;
        });

        $budgetQuery = Budget::where('quarter_start_date', $currentQuarterPeriod['quarter_start_date'])
            ->where('quarter_end_date', $currentQuarterPeriod['quarter_end_date']);

        $totalBudget = $budgetQuery->sum(DB::raw('opening_balance_cents + carry_over_cents'));
        $totalUsed = $budgetQuery->sum(DB::raw('committed_cents + approved_spend_cents'));
        $totalRemaining = max(0, $totalBudget - $totalUsed);
        $overBudgetCount = $budgetQuery->whereRaw('committed_cents + approved_spend_cents > opening_balance_cents + carry_over_cents')->count();

        $currentQuarterLabel = $this->formatFiscalQuarterLabel($currentQuarter);

        return view('admin.budgets', compact('participants', 'totalBudget', 'totalUsed', 'totalRemaining', 'overBudgetCount', 'currentQuarterLabel'));
    }

    protected function moneyDollarsToCents($amount)
    {
        return (int) round($amount * 100);
    }

    public function createParticipant()
    {
        $supportPeople = SupportPerson::orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.participants.create', compact('supportPeople'));
    }

    public function storeParticipant(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => [Rule::requiredIf(fn () => $request->input('status') !== 'onboarding'), 'nullable', 'string', 'min:8', 'confirmed'],
            'participant_number' => ['nullable', 'string', 'max:50', 'unique:participants,participant_number'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'preferred_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(Participant::statusOptions())],
            'care_plan_start_date' => ['nullable', 'date'],
            'care_plan_end_date' => ['nullable', 'date', 'after_or_equal:care_plan_start_date'],
            'primary_language' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:50'],
            'medical_alerts' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'consent_to_share' => ['sometimes', 'boolean'],
            'budget_limit_dollars' => ['nullable', 'numeric', 'min:0'],
            'current_budget_used_dollars' => ['nullable', 'numeric', 'min:0'],
            'assigned_support_person_id' => ['nullable', 'integer', 'exists:support_people,id'],
        ]);

        $isOnboarding = $validated['status'] === 'onboarding';
        $userPassword = $isOnboarding ? Str::random(32) : $validated['password'];

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'participant',
            'status' => $isOnboarding ? 'inactive' : $validated['status'],
            'mfa_enabled' => false,
            'password' => Hash::make($userPassword),
            'password_changed_at' => $isOnboarding ? null : now(),
        ]);

        $participant = Participant::create([
            'user_id' => $user->id,
            'participant_number' => $validated['participant_number'] ?? 'P-'.$user->id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'preferred_name' => $validated['preferred_name'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'status' => $validated['status'],
            'onboarding_token' => $isOnboarding ? Str::uuid() : null,
            'onboarding_expires_at' => $isOnboarding ? now()->addDays(14) : null,
            'care_plan_start_date' => $validated['care_plan_start_date'] ?? null,
            'care_plan_end_date' => $validated['care_plan_end_date'] ?? null,
            'primary_language' => $validated['primary_language'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postcode' => $validated['postcode'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'medical_alerts' => $validated['medical_alerts'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'consent_to_share' => $request->boolean('consent_to_share'),
            'budget_limit_cents' => $validated['budget_limit_dollars'] !== null ? $this->moneyDollarsToCents($validated['budget_limit_dollars']) : 0,
            'current_budget_used_cents' => $validated['current_budget_used_dollars'] !== null ? $this->moneyDollarsToCents($validated['current_budget_used_dollars']) : 0,
            'assigned_support_person_id' => $validated['assigned_support_person_id'] ?? null,
            'created_by_id' => auth()->id(),
            'updated_by_id' => auth()->id(),
        ]);

        if ($isOnboarding) {
            $html = view('mail.participant-onboarding-invitation', ['participant' => $participant])->render();
            try {
                TemplateMailer::send(
                    $user->email,
                    'participant-onboarding-invitation',
                    [
                        'name' => trim($participant->first_name.' '.$participant->last_name),
                        'first_name' => $participant->first_name,
                        'last_name' => $participant->last_name,
                        'email' => $participant->email,
                        'onboarding_url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
                        'expires_at' => optional($participant->onboarding_expires_at)->format('d M Y H:i') ?? now()->addDays(30)->format('d M Y H:i'),
                        'organization' => config('app.name', 'AHHC Portal'),
                    ],
                    'Complete your AHHC portal onboarding',
                    $html,
                    strip_tags($html),
                    'Participant Onboarding Invitation',
                    'Onboarding'
                );
            } catch (\Throwable $e) {
                Mail::to($user->email)->send(new ParticipantOnboardingInvitation($participant));
            }
        }

        NotificationService::notify([
            'user_id' => $user->id,
            'participant_id' => $participant->id,
            'type' => 'success',
            'data' => [
                'title' => 'Participant account created',
                'message' => $isOnboarding ? 'Your participant account has been created and an onboarding invitation has been sent.' : 'Your participant account has been created by AHHC admin. Sign in to complete onboarding.',
                'url' => route('portal.dashboard'),
            ],
        ]);

        return redirect()->route('portal.admin.participants')->with('status', 'Participant created successfully.');
    }

    public function editParticipant(Participant $participant)
    {
        $supportPeople = SupportPerson::orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.participants.edit', compact('participant', 'supportPeople'));
    }

    public function updateParticipant(Request $request, Participant $participant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$participant->user_id],
            'phone' => ['nullable', 'string', 'max:50'],
            'participant_number' => ['required', 'string', 'max:50', 'unique:participants,participant_number,'.$participant->id],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'preferred_name' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
            'status' => ['required', 'string', Rule::in(Participant::statusOptions())],
            'care_plan_start_date' => ['nullable', 'date'],
            'care_plan_end_date' => ['nullable', 'date', 'after_or_equal:care_plan_start_date'],
            'primary_language' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:50'],
            'medical_alerts' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'consent_to_share' => ['sometimes', 'boolean'],
            'budget_limit_dollars' => ['nullable', 'numeric', 'min:0'],
            'current_budget_used_dollars' => ['nullable', 'numeric', 'min:0'],
            'assigned_support_person_id' => ['nullable', 'integer', 'exists:support_people,id'],
        ]);

        if ($participant->status === Participant::STATUS_ONBOARDING && $validated['status'] === Participant::STATUS_ACTIVE) {
            $activationErrors = $this->validateParticipantActivationRequirements($participant);
            if (! empty($activationErrors)) {
                return back()->withErrors($activationErrors)->withInput();
            }
        }

        $user = $participant->user;
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'] === 'onboarding' ? 'inactive' : $validated['status'],
        ]);

        $participantUpdates = [
            'participant_number' => $validated['participant_number'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'preferred_name' => $validated['preferred_name'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'status' => $validated['status'],
            'care_plan_start_date' => $validated['care_plan_start_date'] ?? null,
            'care_plan_end_date' => $validated['care_plan_end_date'] ?? null,
            'primary_language' => $validated['primary_language'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'postcode' => $validated['postcode'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'medical_alerts' => $validated['medical_alerts'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'consent_to_share' => $request->boolean('consent_to_share'),
            'budget_limit_cents' => $validated['budget_limit_dollars'] !== null ? $this->moneyDollarsToCents($validated['budget_limit_dollars']) : 0,
            'current_budget_used_cents' => $validated['current_budget_used_dollars'] !== null ? $this->moneyDollarsToCents($validated['current_budget_used_dollars']) : 0,
            'assigned_support_person_id' => $validated['assigned_support_person_id'] ?? null,
            'updated_by_id' => auth()->id(),
        ];

        if ($validated['status'] === 'onboarding') {
            $participantUpdates['onboarding_token'] = $participant->onboarding_token ?: Str::uuid();
            $participantUpdates['onboarding_expires_at'] = $participant->onboarding_expires_at && $participant->onboarding_expires_at->isFuture()
                ? $participant->onboarding_expires_at
                : now()->addDays(14);
        } else {
            $participantUpdates['onboarding_token'] = null;
            $participantUpdates['onboarding_expires_at'] = null;
        }

        $participant->update($participantUpdates);

        if ($validated['status'] === 'onboarding') {
            $html = view('mail.participant-onboarding-invitation', ['participant' => $participant])->render();
            try {
                TemplateMailer::send(
                    $user->email,
                    'participant-onboarding-invitation',
                    [
                        'name' => trim($participant->first_name.' '.$participant->last_name),
                        'first_name' => $participant->first_name,
                        'last_name' => $participant->last_name,
                        'email' => $participant->email,
                        'onboarding_url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
                        'expires_at' => optional($participant->onboarding_expires_at)->format('d M Y H:i') ?? now()->addDays(30)->format('d M Y H:i'),
                        'organization' => config('app.name', 'AHHC Portal'),
                    ],
                    'Complete your AHHC portal onboarding',
                    $html,
                    strip_tags($html),
                    'Participant Onboarding Invitation',
                    'Onboarding'
                );
            } catch (\Throwable $e) {
                Mail::to($user->email)->send(new ParticipantOnboardingInvitation($participant));
            }
        }

        NotificationService::notify([
            'user_id' => $participant->user_id,
            'participant_id' => $participant->id,
            'type' => 'info',
            'data' => [
                'title' => 'Profile updated',
                'message' => 'Your participant profile was updated by AHHC admin. Review your dashboard for the latest details.',
                'url' => route('portal.dashboard'),
            ],
        ]);

        return redirect()->route('portal.admin.participants.show', $participant)->with('status', 'Participant updated successfully.');
    }

    protected function validateParticipantActivationRequirements(Participant $participant): array
    {
        $errors = [];

        $requiredCategories = Document::mandatoryParticipantDocumentCategories();
        $uploadedCategories = Document::query()
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->pluck('document_type')
            ->map(fn ($type) => Document::denormalizeParticipantDocumentCategory($type))
            ->unique();

        $uploadedRequiredCount = $uploadedCategories->filter(fn ($type) => in_array($type, $requiredCategories, true))->count();
        $hasOnboardingDocument = $uploadedCategories->contains(fn ($type) => strtolower($type) === 'onboarding document');

        if ($uploadedRequiredCount === 0 && ! $hasOnboardingDocument) {
            $msg = 'At least one onboarding document is required before activation. Upload any one of: '.implode(', ', $requiredCategories).'.';
            $errors['documents'] = $msg;
            $errors['status'] = ($errors['status'] ?? 'Participant onboarding must be completed before the account can be activated.').' '.$msg;
        }

        $requiredAgreements = array_values(OnboardingAgreementService::requiredAgreements());
        $signedAgreements = Document::query()
            ->where('owner_type', Participant::class)
            ->where('owner_id', $participant->id)
            ->where('status', 'signed')
            ->whereIn('document_type', $requiredAgreements)
            ->pluck('document_type')
            ->unique();

        $missingAgreements = collect($requiredAgreements)->diff($signedAgreements)->values();
        if ($missingAgreements->isNotEmpty()) {
            $msg = 'Required signed onboarding agreements are missing: '.$missingAgreements->implode(', ');
            $errors['agreements'] = $msg;
            $errors['status'] = ($errors['status'] ?? 'Participant onboarding must be completed before the account can be activated.').' '.$msg;
        }

        return $errors;
    }

    public function destroyParticipant(Participant $participant)
    {
        $participant->delete();

        return redirect()->route('portal.admin.participants')->with('status', 'Participant deleted successfully.');
    }

    public function resendParticipantOnboardingInvitation(Participant $participant)
    {
        if (! in_array($participant->status, ['onboarding', 'changes_requested'], true)) {
            return back()->withErrors('Invitation can only be sent for onboarding participants.');
        }

        if (! $participant->onboarding_token || ! $participant->onboarding_expires_at || $participant->onboarding_expires_at->isPast()) {
            $participant->onboarding_token = Str::uuid();
            $participant->onboarding_expires_at = now()->addDays(14);
            $participant->save();
        }

        try {
            $html = view('mail.participant-onboarding-invitation', ['participant' => $participant])->render();
            TemplateMailer::send(
                $participant->email,
                'participant-onboarding-invitation',
                [
                    'name' => $participant->first_name.' '.$participant->last_name,
                    'first_name' => $participant->first_name,
                    'last_name' => $participant->last_name,
                    'email' => $participant->email,
                    'onboarding_url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
                    'expires_at' => optional($participant->onboarding_expires_at)->format('d M Y H:i') ?? now()->addDays(30)->format('d M Y H:i'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'Complete your AHHC portal onboarding',
                $html,
                strip_tags($html),
                'Participant Onboarding Invitation',
                'Onboarding'
            );
        } catch (\Throwable $e) {
            Mail::to($participant->email)->send(new ParticipantOnboardingInvitation($participant));
        }

        if ($participant->user) {
            NotificationCenterService::send('portal_invitation', $participant->user->id, [
                'participant_id' => $participant->id,
                'url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
                'message' => 'Your onboarding invitation has been resent. Please use the link to resume your onboarding.',
            ]);
        }

        return back()->with('status', 'Onboarding invitation resent successfully.');
    }

    public function approveParticipant(Request $request, Participant $participant)
    {
        if (! in_array(auth()->user()->role, ['admin', 'system_admin'], true)) {
            abort(403);
        }

        if ($participant->status !== Participant::STATUS_PENDING_ADMIN_REVIEW) {
            return back()->withErrors('Participant approval is only allowed for pending admin review records.');
        }

        $errors = $this->validateParticipantActivationRequirements($participant);
        if (! empty($errors)) {
            return back()->withErrors($errors);
        }

        $participant->update(['status' => Participant::STATUS_ACTIVE]);

        if ($participant->user) {
            $participant->user->update(['status' => 'active']);
        }

        // Ensure assessment record exists and is set to active status for middleware gate
        $assessment = $participant->assessment;
        if (! $assessment) {
            // Create Assessment if it doesn't exist (for participants created via simple flow)
            $assessment = Assessment::create([
                'participant_id' => $participant->id,
                'created_by_user_id' => auth()->id(),
                'status' => Assessment::STATUS_PORTAL_ACTIVATED,
                'activated_at' => now(),
                'activated_by_user_id' => auth()->id(),
            ]);
        } else {
            // Update existing assessment status
            $assessment->update([
                'status' => Assessment::STATUS_PORTAL_ACTIVATED,
                'activated_at' => now(),
                'activated_by_user_id' => auth()->id(),
            ]);
        }

        AuditLogService::record('Participant Approved', $participant, [], [
            'from' => Participant::STATUS_PENDING_ADMIN_REVIEW,
            'to' => Participant::STATUS_ACTIVE,
            'approved_by' => auth()->id(),
        ], auth()->id());

        if ($participant->user) {
            NotificationCenterService::send('portal_invitation', $participant->user->id, [
                'title' => 'Account activated',
                'message' => 'Your participant account is now active. You can log in to access the portal.',
                'participant_id' => $participant->id,
                'url' => route('portal.dashboard'),
            ]);
        }

        return back()->with('status', 'Participant approved and progressed to the next stage.');
    }

    public function deactivateParticipant(Request $request, Participant $participant)
    {
        if (! in_array(auth()->user()->role, ['admin', 'system_admin'], true)) {
            abort(403);
        }

        if ($participant->status !== Participant::STATUS_ACTIVE) {
            return back()->withErrors('Only active participants can be deactivated.');
        }

        $previousStatus = $participant->status;
        $participant->update(['status' => Participant::STATUS_INACTIVE]);

        if ($participant->user) {
            $participant->user->update(['status' => 'inactive']);
        }

        AuditLogService::record('Participant Deactivated', $participant, [], [
            'from' => $previousStatus,
            'to' => Participant::STATUS_INACTIVE,
            'deactivated_by' => auth()->id(),
        ], auth()->id());

        if ($participant->user) {
            NotificationCenterService::send('portal_invitation', $participant->user->id, [
                'title' => 'Account deactivated',
                'message' => 'Your participant account has been deactivated by an administrator.',
                'participant_id' => $participant->id,
                'url' => route('portal.login'),
            ]);
        }

        return back()->with('status', 'Participant has been deactivated successfully.');
    }

    public function rejectParticipant(Request $request, Participant $participant)
    {
        if (! in_array(auth()->user()->role, ['admin', 'system_admin'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $previous = $participant->status;
        $participant->update([
            'status' => Participant::STATUS_ONBOARDING,
            'onboarding_token' => $participant->onboarding_token ?: Str::uuid(),
            'onboarding_expires_at' => $participant->onboarding_expires_at && $participant->onboarding_expires_at->isFuture()
                ? $participant->onboarding_expires_at
                : now()->addDays(14),
        ]);

        if ($participant->user) {
            $participant->user->update(['status' => 'inactive']);
        }

        AuditLogService::record('Participant Rejected', $participant, [], [
            'from' => $previous,
            'to' => Participant::STATUS_ONBOARDING,
            'rejected_by' => auth()->id(),
            'reason' => $validated['reason'] ?? null,
        ], auth()->id());

        if ($participant->user) {
            NotificationCenterService::send('onboarding_resubmitted', $participant->user->id, [
                'title' => 'Application rejected',
                'message' => 'Your application was rejected.'.(! empty($validated['reason']) ? ' Reason: '.$validated['reason'] : ''),
                'participant_id' => $participant->id,
                'url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
            ]);
        }

        return back()->with('status', 'Participant application rejected and returned to onboarding with feedback.');
    }

    public function requestParticipantChanges(Request $request, Participant $participant)
    {
        if (! in_array(auth()->user()->role, ['admin', 'system_admin'], true)) {
            abort(403);
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $previous = $participant->status;
        $participant->update([
            'status' => Participant::STATUS_ONBOARDING,
            'onboarding_token' => $participant->onboarding_token ?: Str::uuid(),
            'onboarding_expires_at' => $participant->onboarding_expires_at && $participant->onboarding_expires_at->isFuture()
                ? $participant->onboarding_expires_at
                : now()->addDays(14),
        ]);

        if ($participant->user) {
            $participant->user->update(['status' => 'inactive']);
        }

        AuditLogService::record('Participant Changes Requested', $participant, [], [
            'from' => $previous,
            'to' => Participant::STATUS_ONBOARDING,
            'requested_by' => auth()->id(),
            'notes' => $validated['notes'] ?? null,
        ], auth()->id());

        if ($participant->user) {
            NotificationCenterService::send('onboarding_resubmitted', $participant->user->id, [
                'title' => 'Changes requested',
                'message' => 'An admin has requested changes to your application. Please review and resubmit.',
                'participant_id' => $participant->id,
                'url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
            ]);
        }

        return back()->with('status', 'Requested changes and returned application to onboarding step.');
    }

    public function users(Request $request)
    {
        $query = User::query()->orderBy('id', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function showUser(User $user)
    {
        $user->load(['participant', 'worker', 'auditLogs']);

        return view('admin.user', compact('user'));
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'force_dashboard' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
            'force_dashboard' => $request->boolean('force_dashboard'),
            'password' => $validated['password'] ?? $user->password,
        ]);

        if ($user->participant) {
            $user->participant->update([
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $user->participant->phone,
            ]);
        }

        if ($user->worker) {
            $user->worker->update([
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? $user->worker->phone,
            ]);
        }

        return redirect()->route('portal.admin.users.show', $user)->with('status', 'User updated successfully.');
    }

    public function destroyUser(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        DB::transaction(function () use ($user) {
            $referencesToNull = [
                ['table' => 'participants', 'columns' => ['created_by_id', 'updated_by_id']],
                ['table' => 'care_notes', 'columns' => ['created_by_id', 'approved_by_id']],
                ['table' => 'support_tickets', 'columns' => ['resolved_by']],
                ['table' => 'monthly_care_reviews', 'columns' => ['completed_by_id']],
                ['table' => 'worker_compliance_documents', 'columns' => ['verified_by_id']],
            ];

            foreach ($referencesToNull as $reference) {
                foreach ($reference['columns'] as $column) {
                    DB::table($reference['table'])
                        ->where($column, $user->id)
                        ->update([$column => null]);
                }
            }

            $user->delete();
        });

        return redirect()->route('portal.admin.users')->with('status', 'User deleted successfully.');
    }

    public function updateUserStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $user->update(['status' => $request->input('status')]);

        if (in_array($user->role, ['participant', 'worker'], true)) {
            NotificationService::notify([
                'user_id' => $user->id,
                'type' => 'warning',
                'data' => [
                    'title' => 'Account status changed',
                    'message' => "Your account status is now {$user->status}.",
                    'url' => $user->role === 'worker' ? route('portal.worker.dashboard') : route('portal.dashboard'),
                ],
            ]);
        }

        return back()->with('status', 'User status updated.');
    }

    public function forceDashboardLogin(Request $request, User $user)
    {
        $request->validate([
            'confirm' => ['required', 'accepted'],
        ]);
        // Mark user to bypass onboarding and activate account immediately
        $userUpdates = [
            'force_dashboard' => true,
            'status' => 'active',
        ];

        // Clear any previously generated force-login tokens
        if (isset($user->force_dashboard_token)) {
            $userUpdates['force_dashboard_token'] = null;
            $userUpdates['force_dashboard_token_expires_at'] = null;
        }

        $user->update($userUpdates);

        // If user has a participant profile, fully activate and clear onboarding
        if ($user->participant) {
            $participant = $user->participant;
            $participant->update([
                'status' => Participant::STATUS_ACTIVE,
                'onboarding_token' => null,
                'onboarding_expires_at' => null,
            ]);

            // Ensure assessment exists and is active so middleware allows dashboard access
            $assessment = $participant->assessment;
            if (! $assessment) {
                Assessment::create([
                    'participant_id' => $participant->id,
                    'created_by_user_id' => auth()->id(),
                    'status' => Assessment::STATUS_PORTAL_ACTIVATED,
                    'activated_at' => now(),
                    'activated_by_user_id' => auth()->id(),
                ]);
            } else {
                $assessment->update([
                    'status' => Assessment::STATUS_PORTAL_ACTIVATED,
                    'activated_at' => now(),
                    'activated_by_user_id' => auth()->id(),
                ]);
            }
        }

        // If user has a worker profile, mark onboarding complete and activate
        if ($user->worker) {
            $worker = $user->worker;
            $worker->update([
                'status' => 'active',
                'onboarding_stage' => max(6, (int) ($worker->onboarding_stage ?? 0)),
                'onboarding_token' => null,
                'onboarding_expires_at' => null,
            ]);
        }

        // Send activation email to the user
        try {
            $dashboardUrl = route('portal.dashboard');
            if ($user->role === 'worker') {
                $dashboardUrl = route('portal.worker.dashboard');
            } elseif ($user->role === 'admin') {
                $dashboardUrl = route('portal.admin.dashboard');
            }

            $html = view('mail.account-activated', [
                'name' => $user->name,
                'dashboard_url' => $dashboardUrl,
                'login_url' => route('portal.login'),
                'organization' => config('app.name', 'AHHC Portal'),
            ])->render();

            TemplateMailer::send(
                $user->email,
                'account-activated',
                [
                    'name' => $user->name,
                    'dashboard_url' => $dashboardUrl,
                    'login_url' => route('portal.login'),
                    'organization' => config('app.name', 'AHHC Portal'),
                ],
                'Your account is active — '.config('app.name', 'AHHC Portal'),
                $html,
                strip_tags($html),
                'Account Activated',
                'Account'
            );
        } catch (\Throwable $e) {
            try {
                Mail::to($user->email)->send(new PortalTestEmail);
            } catch (\Throwable $_) {
                // swallow: email best-effort
            }
        }

        return back()->with('status', 'User activated and permitted to access the dashboard immediately.');
    }

    public function settings()
    {
        $settings = $this->loadSettings();
        $manifest = $this->loadManifest();

        return view('admin.settings', compact('settings', 'manifest'));
    }

    public function legalDocuments()
    {
        $settings = $this->loadSettings();

        return view('admin.legal', compact('settings'));
    }

    public function updateLegalDocuments(Request $request)
    {
        $validated = $request->validate([
            'privacy_policy' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,html,htm', 'max:10240'],
            'terms_of_service' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,html,htm', 'max:10240'],
        ]);

        $this->saveUploadedSetting($request, 'privacy_policy', 'privacy_policy_path');
        $this->saveUploadedSetting($request, 'terms_of_service', 'terms_of_service_path');

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['message' => 'Legal documents updated.']);
        }

        return back()->with('status', 'Legal documents updated.');
    }

    public function updateSettings(Request $request)
    {
        $existingSettings = $this->loadSettings();

        $validated = $request->validate([
            'website_name' => ['nullable', 'string', 'max:100'],
            'website_subtitle' => ['nullable', 'string', 'max:150'],
            'website_description' => ['nullable', 'string', 'max:1000'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'dashboard_primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'dashboard_secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'email_sender_name' => ['nullable', 'string', 'max:100'],
            'email_sender_address' => ['nullable', 'email', 'max:255'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:100'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'default_user_role' => ['nullable', 'string', 'in:participant,worker,admin'],
            'require_mfa' => ['nullable', 'boolean'],
            'pwa_enabled' => ['nullable', 'boolean'],
            'report_export_emails' => ['nullable', 'boolean'],
            'incident_alerts' => ['nullable', 'boolean'],
            'email_template_source' => ['nullable', 'string', 'in:code,database'],
            'tawk_to_property_id' => ['nullable', 'string', 'max:255'],
            'tawk_to_widget_id' => ['nullable', 'string', 'max:255'],
            'vapid_public_key' => ['nullable', 'string', 'max:500'],
            'vapid_private_key' => ['nullable', 'string', 'max:500'],
            'vapid_subject' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'pwa_icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:4096'],
            'privacy_policy' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,html,htm', 'max:10240'],
            'terms_of_service' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt,html,htm', 'max:10240'],
        ]);

        $normalized = [
            'website_name' => $validated['website_name'] ?? $existingSettings['website_name'] ?? 'AHHC Portal',
            'website_subtitle' => $validated['website_subtitle'] ?? $existingSettings['website_subtitle'] ?? null,
            'website_description' => $validated['website_description'] ?? $existingSettings['website_description'] ?? null,
            'primary_color' => $validated['primary_color'] ?? $existingSettings['primary_color'] ?? null,
            'secondary_color' => $validated['secondary_color'] ?? $existingSettings['secondary_color'] ?? null,
            'dashboard_primary_color' => $validated['dashboard_primary_color'] ?? $existingSettings['dashboard_primary_color'] ?? '#0E3863',
            'dashboard_secondary_color' => $validated['dashboard_secondary_color'] ?? $existingSettings['dashboard_secondary_color'] ?? '#1699A1',
            'email_sender_name' => $validated['email_sender_name'] ?? $existingSettings['email_sender_name'] ?? null,
            'email_sender_address' => $validated['email_sender_address'] ?? $existingSettings['email_sender_address'] ?? null,
            'smtp_host' => $validated['smtp_host'] ?? $existingSettings['smtp_host'] ?? null,
            'smtp_port' => $validated['smtp_port'] ?? $existingSettings['smtp_port'] ?? null,
            'smtp_encryption' => $validated['smtp_encryption'] ?? $existingSettings['smtp_encryption'] ?? null,
            'smtp_username' => $validated['smtp_username'] ?? $existingSettings['smtp_username'] ?? null,
            'smtp_password' => $validated['smtp_password'] ?? $existingSettings['smtp_password'] ?? null,
            'organization_name' => $validated['organization_name'] ?? $existingSettings['organization_name'] ?? 'AHHC Portal',
            'support_email' => $validated['support_email'] ?? $existingSettings['support_email'] ?? 'support@example.com',
            'default_user_role' => $validated['default_user_role'] ?? $existingSettings['default_user_role'] ?? 'participant',
            'require_mfa' => (bool) ($request->boolean('require_mfa')),
            'report_export_emails' => (bool) ($request->boolean('report_export_emails')),
            'pwa_enabled' => (bool) ($request->boolean('pwa_enabled')),
            'incident_alerts' => (bool) ($request->boolean('incident_alerts')),
            'email_template_source' => $validated['email_template_source'] ?? $existingSettings['email_template_source'] ?? 'database',
            'tawk_to_property_id' => $validated['tawk_to_property_id'] ?? $existingSettings['tawk_to_property_id'] ?? null,
            'tawk_to_widget_id' => $validated['tawk_to_widget_id'] ?? $existingSettings['tawk_to_widget_id'] ?? null,
            'vapid_public_key' => $validated['vapid_public_key'] ?? $existingSettings['vapid_public_key'] ?? null,
            'vapid_private_key' => $validated['vapid_private_key'] ?? $existingSettings['vapid_private_key'] ?? null,
            'vapid_subject' => $validated['vapid_subject'] ?? $existingSettings['vapid_subject'] ?? null,
        ];

        foreach ($normalized as $key => $value) {
            PortalSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value === null ? '' : $value]
            );
        }

        $this->saveUploadedSetting($request, 'logo', 'logo_path');
        $this->saveUploadedSetting($request, 'favicon', 'favicon_path');
        $this->saveUploadedSetting($request, 'pwa_icon', 'pwa_icon_path');

        $settings = $this->loadSettings();
        $this->updatePwaManifest($settings);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['message' => 'Settings updated.']);
        }

        return back()->with('status', 'Settings updated.');
    }

    protected function updatePwaManifest(array $settings): void
    {
        $manifestPath = public_path('manifest.json');

        if (! file_exists($manifestPath)) {
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (! is_array($manifest)) {
            return;
        }

        $manifest['name'] = $settings['website_name'] ?? $manifest['name'];
        $manifest['short_name'] = $settings['website_name'] ?? $manifest['short_name'];
        $manifest['description'] = $settings['website_description'] ?? $manifest['description'];
        $manifest['theme_color'] = $settings['primary_color'] ?? $manifest['theme_color'];
        $manifest['background_color'] = $settings['primary_color'] ?? $manifest['background_color'];

        if (! empty($settings['pwa_icon_path'])) {
            $iconUrl = '/storage/'.ltrim($settings['pwa_icon_path'], '/');
            $extension = strtolower(pathinfo($settings['pwa_icon_path'], PATHINFO_EXTENSION));
            $mimeType = match ($extension) {
                'svg' => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                default => 'image/png',
            };

            $manifest['icons'] = [
                [
                    'src' => $iconUrl,
                    'sizes' => '192x192',
                    'type' => $mimeType,
                ],
                [
                    'src' => $iconUrl,
                    'sizes' => '512x512',
                    'type' => $mimeType,
                ],
                [
                    'src' => $iconUrl,
                    'sizes' => '180x180',
                    'type' => $mimeType,
                ],
            ];
        }

        file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public function clearCache()
    {
        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            \Artisan::call('route:clear');
            \Artisan::call('view:clear');

            return back()->with('status', 'Application cache cleared successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to clear cache: '.$e->getMessage());
        }
    }

    public function generateVapidKeys(Request $request)
    {
        $validated = $request->validate([
            'vapid_subject' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $vapidKeys = $this->createVapidKeys();
            $publicKey = $vapidKeys['publicKey'];
            $privateKey = $vapidKeys['privateKey'];
            $subject = trim((string) ($validated['vapid_subject'] ?? '')) ?: 'mailto:hello@example.com';

            PortalSetting::updateOrCreate(
                ['key' => 'vapid_public_key'],
                ['value' => $publicKey]
            );
            PortalSetting::updateOrCreate(
                ['key' => 'vapid_private_key'],
                ['value' => $privateKey]
            );
            PortalSetting::updateOrCreate(
                ['key' => 'vapid_subject'],
                ['value' => $subject]
            );

            $envPath = base_path('.env');
            if (file_exists($envPath)) {
                $envContent = file_get_contents($envPath);

                if (str_contains($envContent, 'VAPID_PUBLIC_KEY=')) {
                    $envContent = preg_replace('/VAPID_PUBLIC_KEY=.*/i', 'VAPID_PUBLIC_KEY='.$publicKey, $envContent);
                } else {
                    $envContent .= "\nVAPID_PUBLIC_KEY=".$publicKey;
                }

                if (str_contains($envContent, 'VAPID_PRIVATE_KEY=')) {
                    $envContent = preg_replace('/VAPID_PRIVATE_KEY=.*/i', 'VAPID_PRIVATE_KEY='.$privateKey, $envContent);
                } else {
                    $envContent .= "\nVAPID_PRIVATE_KEY=".$privateKey;
                }

                if (str_contains($envContent, 'VAPID_SUBJECT=')) {
                    $envContent = preg_replace('/VAPID_SUBJECT=.*/i', 'VAPID_SUBJECT='.$subject, $envContent);
                } else {
                    $envContent .= "\nVAPID_SUBJECT=".$subject;
                }

                file_put_contents($envPath, $envContent);
            }

            return back()->with('status', 'VAPID keys generated successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to generate VAPID keys: '.$e->getMessage());
        }
    }

    protected function createVapidKeys(): array
    {
        try {
            return VAPID::createVapidKeys();
        } catch (\Throwable $e) {
            return [
                'publicKey' => $this->generateFallbackVapidKey(),
                'privateKey' => $this->generateFallbackVapidKey(),
            ];
        }
    }

    protected function generateFallbackVapidKey(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    public function sendTestEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        $settings = $this->loadSettings();
        $html = view('emails.portal_test', ['settings' => $settings])->render();

        try {
            TemplateMailer::send(
                $validated['test_email'],
                'portal-test-email',
                [
                    'organization' => $settings['website_name'] ?? config('app.name', 'Portal'),
                ],
                ($settings['website_name'] ?? config('app.name', 'Portal')).' — Test Email',
                $html,
                strip_tags($html),
                'Portal Test Email',
                'System'
            );
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send test email: '.$e->getMessage());
        }

        return back()->with('status', 'Test email sent to '.$validated['test_email']);
    }

    protected function loadSettings(): array
    {
        $defaults = [
            'website_name' => 'AHHC Portal',
            'website_subtitle' => 'Self-service participant and worker portal',
            'website_description' => 'Manage participants, workers, invoices, approvals, documents, and compliance in one secure portal.',
            'primary_color' => '#0d6efd',
            'secondary_color' => '#6610f2',
            'email_sender_name' => 'AHHC Support',
            'email_sender_address' => 'support@example.com',
            'smtp_host' => null,
            'smtp_port' => null,
            'smtp_encryption' => null,
            'smtp_username' => null,
            'smtp_password' => null,
            'logo_path' => null,
            'favicon_path' => null,
            'organization_name' => 'AHHC Portal',
            'support_email' => 'support@example.com',
            'default_user_role' => 'participant',
            'require_mfa' => false,
            'report_export_emails' => false,
            'incident_alerts' => true,
            'email_template_source' => 'database',
            'pwa_enabled' => false,
            'pwa_icon_path' => null,
            'tawk_to_property_id' => null,
            'tawk_to_widget_id' => null,
            'privacy_policy_path' => null,
            'terms_of_service_path' => null,
        ];

        $stored = PortalSetting::query()->pluck('value', 'key')->all();

        return array_replace($defaults, $stored);
    }

    protected function saveUploadedSetting(Request $request, string $field, string $settingKey): void
    {
        if (! $request->hasFile($field)) {
            return;
        }

        $path = $request->file($field)->store('portal/settings', 'public');

        PortalSetting::updateOrCreate(
            ['key' => $settingKey],
            ['value' => $path]
        );
    }

    protected function loadManifest(): array
    {
        $manifestPath = public_path('manifest.json');

        if (! file_exists($manifestPath)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($manifestPath), true);

        return is_array($decoded) ? $decoded : [];
    }

    public function activity(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // filters
        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($from = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // export CSV
        if ($request->input('export') === 'csv') {
            $filename = 'audit_logs_'.now()->format('YmdHis').'.csv';

            $callback = function () use ($query) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['id', 'created_at', 'user_id', 'user_name', 'action', 'model_type', 'model_id', 'ip_address', 'user_agent', 'changes']);

                foreach ($query->orderBy('id')->cursor() as $log) {
                    fputcsv($out, [
                        $log->id,
                        optional($log->created_at)->toDateTimeString(),
                        $log->user_id,
                        optional($log->user)->name,
                        $log->action,
                        $log->model_type,
                        $log->model_id,
                        $log->ip_address,
                        $log->user_agent,
                        json_encode($log->changes ?? []),
                    ]);
                }

                fclose($out);
            };

            return response()->stream($callback, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        $activities = $query->paginate(20)->withQueryString();

        $users = User::orderBy('name')->get();
        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.activity', compact('activities', 'users', 'actions'));
    }

    public function workers(Request $request)
    {
        $query = Worker::with(['user', 'assignments.participant'])
            ->orderBy('id', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('worker_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $workers = $query->paginate(20)->withQueryString();

        return view('admin.workers', compact('workers'));
    }

    public function showWorker(Worker $worker)
    {
        $worker->load(['assignments.participant', 'careNotes', 'incidents', 'invoices']);

        $pendingDocumentsCount = Document::where('owner_type', Worker::class)
            ->where('owner_id', $worker->id)
            ->where('status', '!=', 'signed')
            ->count();
        $documentTypes = $this->documentTypeOptions();

        return view('admin.worker', compact('worker', 'pendingDocumentsCount', 'documentTypes'));
    }

    public function createWorker()
    {
        return view('admin.workers.create');
    }

    public function storeWorker(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'availability' => ['nullable', 'string', 'max:255'],
            'compliance_expiry_at' => ['nullable', 'date'],
            'background_check_expiry_at' => ['nullable', 'date'],
            'vehicle_type' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated, &$worker) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => 'worker',
                'status' => $validated['status'],
                'mfa_enabled' => false,
                'password' => $validated['password'],
                'password_changed_at' => now(),
            ]);

            [$firstName, $lastName] = $this->splitFullName($validated['name']);

            $worker = Worker::create([
                'user_id' => $user->id,
                'worker_number' => 'W-'.$user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'role_type' => 'worker',
                'status' => $validated['status'],
                'qualification' => $validated['qualification'] ?? null,
                'availability' => $validated['availability'] ?? null,
                'compliance_expiry_at' => $validated['compliance_expiry_at'] ?? null,
                'background_check_expiry_at' => $validated['background_check_expiry_at'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'worker_id' => $worker->id,
            'type' => 'success',
            'data' => [
                'title' => 'Worker account created',
                'message' => 'Your worker account has been created by AHHC admin. Please sign in to access your dashboard.',
                'url' => route('portal.worker.dashboard'),
            ],
        ]);

        return redirect()->route('portal.admin.workers')->with('status', 'Worker created successfully.');
    }

    public function editWorker(Worker $worker)
    {
        return view('admin.workers.edit', compact('worker'));
    }

    public function updateWorker(Request $request, Worker $worker)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($worker->user_id)],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'qualification' => ['nullable', 'string', 'max:255'],
            'availability' => ['nullable', 'string', 'max:255'],
            'compliance_expiry_at' => ['nullable', 'date'],
            'background_check_expiry_at' => ['nullable', 'date'],
            'vehicle_type' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated, $worker) {
            $worker->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'password' => $validated['password'] ?: $worker->user->password,
            ]);

            [$firstName, $lastName] = $this->splitFullName($validated['name']);

            $worker->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'],
                'status' => $validated['status'],
                'qualification' => $validated['qualification'] ?? null,
                'availability' => $validated['availability'] ?? null,
                'compliance_expiry_at' => $validated['compliance_expiry_at'] ?? null,
                'background_check_expiry_at' => $validated['background_check_expiry_at'] ?? null,
                'vehicle_type' => $validated['vehicle_type'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        NotificationService::notify([
            'user_id' => $worker->user_id,
            'worker_id' => $worker->id,
            'type' => 'info',
            'data' => [
                'title' => 'Profile updated',
                'message' => 'Your worker profile was updated by AHHC admin. Please review your dashboard for the latest details.',
                'url' => route('portal.worker.dashboard'),
            ],
        ]);

        return redirect()->route('portal.admin.workers.show', $worker)->with('status', 'Worker updated successfully.');
    }

    public function destroyWorker(Worker $worker)
    {
        DB::transaction(function () use ($worker) {
            if ($worker->user) {
                if ($worker->user->participant) {
                    $worker->user->participant->agreements()->detach();
                }
                $worker->user->delete();
            } else {
                $worker->delete();
            }
        });

        return redirect()->route('portal.admin.workers')->with('status', 'Worker removed successfully.');
    }

    public function assignments(Request $request)
    {
        $query = ParticipantAssignment::with(['participant', 'worker', 'supportPerson'])
            ->orderBy('start_date', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('assignment_type', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('participant', function ($participantQuery) use ($search) {
                        $participantQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('participant_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('worker', function ($workerQuery) use ($search) {
                        $workerQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('worker_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->input('assignment_type')) {
            $query->where('assignment_type', $type);
        }

        $assignments = $query->paginate(20)->withQueryString();

        return view('admin.assignments.index', compact('assignments'));
    }

    public function createAssignment()
    {
        $participants = Participant::orderBy('first_name')->orderBy('last_name')->get();
        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();
        $supportPeople = SupportPerson::orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.assignments.create', compact('participants', 'workers', 'supportPeople'));
    }

    public function storeAssignment(Request $request)
    {
        $data = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
            'worker_id' => ['required', 'integer', 'exists:workers,id'],
            'support_person_id' => ['nullable', 'integer', 'exists:support_people,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignment_type' => ['required', 'string', 'in:primary,secondary,temporary'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if (ParticipantAssignment::where('participant_id', $data['participant_id'])
            ->where('worker_id', $data['worker_id'])
            ->where('status', 'active')
            ->exists()) {
            return back()->withInput()->withErrors(['worker_id' => 'This worker already has an active assignment for this participant.']);
        }

        if (! empty($data['is_primary'])) {
            ParticipantAssignment::where('participant_id', $data['participant_id'])
                ->where('is_primary', true)
                ->update(['is_primary' => false]);
        }

        ParticipantAssignment::create([
            'participant_id' => $data['participant_id'],
            'worker_id' => $data['worker_id'],
            'support_person_id' => $data['support_person_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'assignment_type' => $data['assignment_type'],
            'status' => $data['status'],
            'is_primary' => ! empty($data['is_primary']),
        ]);

        return redirect()->route('portal.admin.assignments')->with('status', 'Assignment created successfully.');
    }

    public function showAssignment(ParticipantAssignment $assignment)
    {
        $assignment->load(['participant', 'worker', 'supportPerson']);

        return view('admin.assignments.show', compact('assignment'));
    }

    public function editAssignment(ParticipantAssignment $assignment)
    {
        $assignment->load(['participant', 'worker', 'supportPerson']);
        $participants = Participant::orderBy('first_name')->orderBy('last_name')->get();
        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();
        $supportPeople = SupportPerson::orderBy('first_name')->orderBy('last_name')->get();

        return view('admin.assignments.edit', compact('assignment', 'participants', 'workers', 'supportPeople'));
    }

    public function updateAssignment(Request $request, ParticipantAssignment $assignment)
    {
        $data = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
            'worker_id' => ['required', 'integer', 'exists:workers,id'],
            'support_person_id' => ['nullable', 'integer', 'exists:support_people,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignment_type' => ['required', 'string', 'in:primary,secondary,temporary'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'is_primary' => ['sometimes', 'boolean'],
        ]);

        if (ParticipantAssignment::where('participant_id', $data['participant_id'])
            ->where('worker_id', $data['worker_id'])
            ->where('status', 'active')
            ->where('id', '!=', $assignment->id)
            ->exists()) {
            return back()->withInput()->withErrors(['worker_id' => 'That worker already has an active assignment for this participant.']);
        }

        if (! empty($data['is_primary'])) {
            ParticipantAssignment::where('participant_id', $data['participant_id'])
                ->where('is_primary', true)
                ->where('id', '!=', $assignment->id)
                ->update(['is_primary' => false]);
        }

        $assignment->update([
            'participant_id' => $data['participant_id'],
            'worker_id' => $data['worker_id'],
            'support_person_id' => $data['support_person_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'assignment_type' => $data['assignment_type'],
            'status' => $data['status'],
            'is_primary' => ! empty($data['is_primary']),
        ]);

        return redirect()->route('portal.admin.assignments.show', $assignment)->with('status', 'Assignment updated successfully.');
    }

    public function destroyAssignment(ParticipantAssignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('portal.admin.assignments')->with('status', 'Assignment removed successfully.');
    }

    protected function splitFullName(string $fullName): array
    {
        $trimmedName = trim($fullName);

        if ($trimmedName === '') {
            return ['Worker', 'Profile'];
        }

        $parts = preg_split('/\s+/', $trimmedName);
        $firstName = $parts[0] ?? 'Worker';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Worker';

        return [$firstName, $lastName];
    }

    public function assignWorker(Request $request)
    {
        $data = $request->validate([
            'participant_id' => ['required', 'integer', 'exists:participants,id'],
            'worker_id' => ['required', 'integer', 'exists:workers,id'],
        ]);

        $alreadyAssigned = ParticipantAssignment::where('participant_id', $data['participant_id'])
            ->where('worker_id', $data['worker_id'])
            ->where('status', 'active')
            ->exists();

        if ($alreadyAssigned) {
            return back()->withErrors(['worker_id' => 'This worker is already assigned to the participant.']);
        }

        ParticipantAssignment::create([
            'participant_id' => $data['participant_id'],
            'worker_id' => $data['worker_id'],
            'start_date' => now()->toDateString(),
            'status' => 'active',
            'is_primary' => false,
        ]);

        return back()->with('status', 'Worker assigned successfully.');
    }

    public function preApprovals(Request $request)
    {
        $query = PreApprovalRequest::with('participant')->orderBy('submitted_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('request_number', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhereHas('participant', function ($participantQuery) use ($search) {
                        $participantQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->withQueryString();

        return view('admin.pre_approvals', compact('requests'));
    }

    public function showPreApproval(PreApprovalRequest $preApprovalRequest)
    {
        $preApprovalRequest->load(['participant', 'participant.user', 'supportPerson', 'approver', 'worker', 'supplier', 'attachments', 'comments.commenter']);

        $carePlanWarnings = [];
        $participant = $preApprovalRequest->participant;

        if ($participant) {
            if ($participant->care_plan_start_date && $preApprovalRequest->start_date && $preApprovalRequest->start_date->lt($participant->care_plan_start_date)) {
                $carePlanWarnings[] = 'Requested start date is earlier than the participant care plan start date.';
            }
            if ($participant->care_plan_end_date && $preApprovalRequest->end_date && $preApprovalRequest->end_date->gt($participant->care_plan_end_date)) {
                $carePlanWarnings[] = 'Requested end date is later than the participant care plan end date.';
            }
        }

        $budgetDate = Carbon::parse($preApprovalRequest->start_date ?? $preApprovalRequest->submitted_at ?? now());
        $budget = $this->budgetService->getOrCreateBudgetForParticipantQuarter($preApprovalRequest->participant, $budgetDate);
        $budgetMetrics = $this->budgetService->getBudgetMetrics($budget);
        $budgetAvailable = $this->budgetService->canCommit($budget, $preApprovalRequest->requested_amount_cents ?? 0);

        $workerComplianceNotes = [];
        if ($preApprovalRequest->worker) {
            if ($preApprovalRequest->worker->compliance_expiry_at && $preApprovalRequest->worker->compliance_expiry_at->isPast()) {
                $workerComplianceNotes[] = 'Selected worker compliance has expired.';
            }
            if ($preApprovalRequest->worker->background_check_expiry_at && $preApprovalRequest->worker->background_check_expiry_at->isPast()) {
                $workerComplianceNotes[] = 'Selected worker background check has expired.';
            }
        }

        return view('admin.pre_approval', [
            'request' => $preApprovalRequest,
            'budgetMetrics' => $budgetMetrics,
            'budgetAvailable' => $budgetAvailable,
            'carePlanWarnings' => $carePlanWarnings,
            'workerComplianceNotes' => $workerComplianceNotes,
        ]);
    }

    public function downloadPreApprovalQuote(PreApprovalRequest $preApprovalRequest)
    {
        if (! $preApprovalRequest->quote_file_path || ! Storage::disk('local')->exists($preApprovalRequest->quote_file_path)) {
            abort(404);
        }

        $filename = basename($preApprovalRequest->quote_file_path);

        return Storage::disk('local')->download(
            $preApprovalRequest->quote_file_path,
            sprintf('%s-quote-%s', $preApprovalRequest->request_number, $filename)
        );
    }

    public function downloadPreApprovalAttachment(PreApprovalRequest $preApprovalRequest, PreApprovalAttachment $attachment)
    {
        if ($attachment->pre_approval_request_id !== $preApprovalRequest->id || ! Storage::disk('local')->exists($attachment->file_path)) {
            abort(404);
        }

        $filename = basename($attachment->file_path);

        return Storage::disk('local')->download(
            $attachment->file_path,
            sprintf('%s-attachment-%s', $preApprovalRequest->request_number, $filename)
        );
    }

    public function cancelPreApproval(Request $request, PreApprovalRequest $preApprovalRequest)
    {
        $this->authorize('reject', $preApprovalRequest);

        DB::transaction(function () use ($preApprovalRequest) {
            if ($preApprovalRequest->status === 'approved' || $preApprovalRequest->status === 'approved_with_conditions') {
                $this->budgetService->releasePreApproval($preApprovalRequest);
            }

            $preApprovalRequest->update([
                'status' => 'cancelled',
                'decision_reason' => 'Cancelled by admin',
                'admin_id' => auth()->id(),
                'approved_by_id' => auth()->id(),
                'approved_at' => now(),
            ]);

            AuditLogService::record('Pre-Approval Cancelled', $preApprovalRequest, [], [
                'request_number' => $preApprovalRequest->request_number,
                'participant_id' => $preApprovalRequest->participant_id,
                'cancelled_by' => auth()->id(),
            ]);

            PreApprovalComment::create([
                'pre_approval_request_id' => $preApprovalRequest->id,
                'commented_by_id' => auth()->id(),
                'comment_type' => 'cancellation',
                'message' => 'Pre-approval approval was cancelled by admin.',
            ]);
        });

        if ($preApprovalRequest->participant && $preApprovalRequest->participant->user) {
            NotificationService::notify([
                'user_id' => $preApprovalRequest->participant->user->id,
                'type' => 'warning',
                'data' => [
                    'title' => 'Pre-approval cancelled',
                    'message' => "Your pre-approval request {$preApprovalRequest->request_number} has been cancelled.",
                    'url' => route('portal.participant.pre_approvals.index'),
                ],
            ]);
        }

        return back()->with('status', 'Pre-approval cancelled.');
    }

    public function careNotes(Request $request)
    {
        $query = CareNote::with(['participant', 'worker', 'creator', 'approver'])->orderBy('shift_date', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('tasks_completed', 'like', "%{$search}%")
                    ->orWhere('observations', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhereHas('participant', function ($participantQuery) use ($search) {
                        $participantQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $careNotes = $query->paginate(20)->withQueryString();

        return view('admin.care_notes', compact('careNotes'));
    }

    public function showCareNote(CareNote $careNote)
    {
        $careNote->load(['participant', 'worker', 'creator', 'approver']);

        return view('admin.care_note', compact('careNote'));
    }

    public function incidents(Request $request)
    {
        $query = Incident::with(['participant', 'worker', 'reporter'])->orderBy('created_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('incident_type', 'like', "%{$search}%")
                    ->orWhereHas('participant', function ($p) use ($search) {
                        $p->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $incidents = $query->paginate(20)->withQueryString();

        return view('admin.incidents', compact('incidents'));
    }

    public function showIncident(Incident $incident)
    {
        $incident->load(['participant', 'worker', 'reporter']);

        return view('admin.incident', compact('incident'));
    }

    public function updateIncidentStatus(Request $request, Incident $incident)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,investigating,closed'],
            'action_taken' => ['nullable', 'string', 'max:2000'],
        ]);

        $incident->update([
            'status' => $validated['status'],
            'action_taken' => $validated['action_taken'] ?? $incident->action_taken,
        ]);

        return back()->with('status', 'Incident updated.');
    }

    public function approveCareNote(CareNote $careNote)
    {
        if ($careNote->status !== 'approved') {
            $careNote->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by_id' => auth()->id(),
            ]);
            AuditLogService::record('Care Note Approved', $careNote, [], [
                'participant_id' => $careNote->participant_id,
                'worker_id' => $careNote->worker_id,
                'approved_by' => auth()->id(),
            ]);
        }

        return back()->with('status', 'Care note approved.');
    }

    public function approvePreApproval(ApprovePreApprovalRequest $request, PreApprovalRequest $preApprovalRequest)
    {
        $this->authorize('approve', $preApprovalRequest);

        $wasApproved = false;

        if (! in_array($preApprovalRequest->status, ['approved', 'approved_with_conditions'])) {
            $approvedAmount = $request->input('committed_amount') !== null
                ? (int) round($request->input('committed_amount') * 100)
                : ($preApprovalRequest->committed_amount_cents ?? $preApprovalRequest->requested_amount_cents);

            $status = $request->input('decision_type') === 'approve_with_conditions'
                ? 'approved_with_conditions'
                : 'approved';

            $decisionReason = $request->input('condition_notes');

            try {
                DB::transaction(function () use ($preApprovalRequest, $approvedAmount, $status, $decisionReason) {
                    $preApprovalRequest->update([
                        'status' => $status,
                        'committed_amount_cents' => $approvedAmount,
                        'admin_id' => auth()->id(),
                        'approved_by_id' => auth()->id(),
                        'approved_at' => now(),
                        'decision_reason' => $decisionReason,
                    ]);

                    AuditLogService::record('Pre-Approval Approved', $preApprovalRequest, [], [
                        'request_number' => $preApprovalRequest->request_number,
                        'participant_id' => $preApprovalRequest->participant_id,
                        'approved_amount_cents' => $approvedAmount,
                        'status' => $status,
                        'approved_by' => auth()->id(),
                        'condition_notes' => $decisionReason,
                    ]);

                    PreApprovalComment::create([
                        'pre_approval_request_id' => $preApprovalRequest->id,
                        'commented_by_id' => auth()->id(),
                        'comment_type' => $status === PreApprovalRequest::STATUS_APPROVED_WITH_CONDITIONS ? 'approval_with_conditions' : 'approval',
                        'message' => $decisionReason ?: 'Pre-approval approved.',
                    ]);

                    if ($preApprovalRequest->participant) {
                        $this->budgetService->commitPreApproval($preApprovalRequest, $approvedAmount);
                    }
                });

                $wasApproved = true;
            } catch (\RuntimeException $exception) {
                return back()->withErrors(['budget' => $exception->getMessage()]);
            }
        }

        if ($wasApproved && $preApprovalRequest->participant && $preApprovalRequest->participant->user) {
            NotificationService::notify([
                'user_id' => $preApprovalRequest->participant->user->id,
                'type' => 'success',
                'data' => [
                    'title' => 'Pre-approval approved',
                    'message' => "Your pre-approval request {$preApprovalRequest->request_number} is approved.",
                    'url' => route('portal.participant.pre_approvals.index'),
                ],
            ]);
        }

        return back()->with('status', 'Pre-approval approved.');
    }

    public function rejectPreApproval(RejectPreApprovalRequest $request, PreApprovalRequest $preApprovalRequest)
    {
        $this->authorize('reject', $preApprovalRequest);

        DB::transaction(function () use ($preApprovalRequest, $request) {
            if (in_array($preApprovalRequest->status, ['approved', 'approved_with_conditions'])) {
                $this->budgetService->releasePreApproval($preApprovalRequest);
            }

            $preApprovalRequest->update([
                'status' => 'rejected',
                'decision_reason' => $request->input('decision_reason'),
                'admin_id' => auth()->id(),
                'approved_by_id' => auth()->id(),
                'approved_at' => now(),
            ]);

            AuditLogService::record('Pre-Approval Rejected', $preApprovalRequest, [], [
                'request_number' => $preApprovalRequest->request_number,
                'participant_id' => $preApprovalRequest->participant_id,
                'reason' => $request->input('decision_reason'),
                'rejected_by' => auth()->id(),
            ]);

            PreApprovalComment::create([
                'pre_approval_request_id' => $preApprovalRequest->id,
                'commented_by_id' => auth()->id(),
                'comment_type' => 'rejection',
                'message' => $request->input('decision_reason'),
            ]);
        });

        if ($preApprovalRequest->participant && $preApprovalRequest->participant->user) {
            NotificationService::notify([
                'user_id' => $preApprovalRequest->participant->user->id,
                'type' => 'danger',
                'data' => [
                    'title' => 'Pre-approval declined',
                    'message' => "Your pre-approval request {$preApprovalRequest->request_number} has been declined.",
                    'url' => route('portal.participant.pre_approvals.index'),
                ],
            ]);
        }

        return back()->with('status', 'Pre-approval rejected.');
    }

    public function requestPreApprovalInfo(Request $request, PreApprovalRequest $preApprovalRequest)
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:2000'],
        ]);

        $preApprovalRequest->update([
            'status' => 'info_requested',
            'review_notes' => $validated['review_notes'],
            'admin_id' => auth()->id(),
        ]);

        AuditLogService::record('Pre-Approval Information Requested', $preApprovalRequest, [], [
            'request_number' => $preApprovalRequest->request_number,
            'participant_id' => $preApprovalRequest->participant_id,
            'review_notes' => $validated['review_notes'],
            'requested_by' => auth()->id(),
        ]);

        PreApprovalComment::create([
            'pre_approval_request_id' => $preApprovalRequest->id,
            'commented_by_id' => auth()->id(),
            'comment_type' => 'info_requested',
            'message' => $validated['review_notes'],
        ]);

        if ($preApprovalRequest->participant && $preApprovalRequest->participant->user) {
            NotificationService::notify([
                'user_id' => $preApprovalRequest->participant->user->id,
                'type' => 'warning',
                'data' => [
                    'title' => 'More information requested',
                    'message' => "More information has been requested for pre-approval {$preApprovalRequest->request_number}.",
                    'url' => route('portal.participant.pre_approvals.index'),
                ],
            ]);
        }

        return back()->with('status', 'Information request sent.');
    }

    public function invoices(Request $request)
    {
        $query = Invoice::with(['participant', 'worker'])->orderBy('invoice_date', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('participant', function ($participantQuery) use ($search) {
                        $participantQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $invoices = $query->paginate(20)->withQueryString();

        return view('admin.invoices', compact('invoices'));
    }

    public function showInvoice(Invoice $invoice)
    {
        $invoice->load(['participant', 'worker', 'approver', 'preApprovalRequest']);

        return view('admin.invoice', compact('invoice'));
    }

    public function reviewInvoice(Invoice $invoice)
    {
        if ($invoice->status === 'approved') {
            return back()->with('status', 'Invoice is already approved.');
        }

        if ($invoice->status !== 'submitted') {
            return back()->withErrors(['invoice' => 'Only submitted invoices can be approved.']);
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by_id' => auth()->id(),
            ]);

            AuditLogService::record('Invoice Approved', $invoice, [], [
                'invoice_number' => $invoice->invoice_number,
                'participant_id' => $invoice->participant_id,
                'approved_by' => auth()->id(),
            ]);

            if ($invoice->participant && $invoice->pre_approval_id) {
                $this->budgetService->approveInvoice($invoice);
            }

            if ($invoice->participant && $invoice->participant->user_id) {
                NotificationCenterService::send('invoice_approved', $invoice->participant->user_id, [
                    'participant_id' => $invoice->participant_id,
                    'invoice_id' => $invoice->id,
                    'message' => 'Your invoice '.$invoice->invoice_number.' has been approved and added to your budget review.',
                    'url' => route('portal.participant.invoices.index'),
                ]);
            }
        });

        return back()->with('status', 'Invoice approved.');
    }

    public function rejectInvoice(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->withErrors(['budget' => 'Paid invoices cannot be rejected.']);
        }

        DB::transaction(function () use ($invoice) {
            if ($invoice->status === 'approved' && $invoice->participant && $invoice->pre_approval_id) {
                $this->budgetService->releaseInvoice($invoice);
            }

            $invoice->update([
                'status' => 'rejected',
                'approved_at' => now(),
                'approved_by_id' => auth()->id(),
            ]);
            AuditLogService::record('Invoice Rejected', $invoice, [], [
                'invoice_number' => $invoice->invoice_number,
                'participant_id' => $invoice->participant_id,
                'rejected_by' => auth()->id(),
            ]);

            if ($invoice->participant && $invoice->participant->user_id) {
                NotificationCenterService::send('invoice_rejected', $invoice->participant->user_id, [
                    'participant_id' => $invoice->participant_id,
                    'invoice_id' => $invoice->id,
                    'message' => 'Your invoice '.$invoice->invoice_number.' was not approved and has been returned for review.',
                    'url' => route('portal.participant.invoices.index'),
                ]);
            }
        });

        return back()->with('status', 'Invoice rejected.');
    }

    public function payInvoice(Invoice $invoice)
    {
        if ($invoice->status !== 'approved') {
            return back()->withErrors(['budget' => 'Only approved invoices can be paid.']);
        }

        // pre-check budget availability to avoid runtime exceptions
        if ($invoice->participant) {
            $budgetDate = Carbon::parse($invoice->invoice_date ?? now());
            $budget = $this->budgetService->getOrCreateBudgetForParticipantQuarter($invoice->participant, $budgetDate);

            if ($invoice->amount_cents > $budget->approved_spend_cents) {
                return back()->withErrors(['budget' => 'Not enough approved spend available to pay this invoice.']);
            }
        }

        DB::transaction(function () use ($invoice) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($invoice->participant) {
                $this->budgetService->payInvoice($invoice);
            }
        });

        AuditLogService::record('Invoice Paid', $invoice, [], [
            'invoice_number' => $invoice->invoice_number,
            'participant_id' => $invoice->participant_id,
            'paid_by' => auth()->id(),
            'paid_at' => $invoice->paid_at,
        ]);

        return back()->with('status', 'Invoice marked as paid.');
    }

    public function downloadInvoiceAttachment(Invoice $invoice)
    {
        $path = $invoice->invoice_file_path ?: $invoice->attachment_path;

        if (! $path || ! Storage::disk($invoice->attachment_disk)->exists($path)) {
            abort(404);
        }

        $filename = $invoice->invoice_number.'.'.pathinfo($path, PATHINFO_EXTENSION);

        AuditLogService::record('Invoice Attachment Download', $invoice, [], [
            'invoice_number' => $invoice->invoice_number,
            'participant_id' => $invoice->participant_id,
            'downloaded_by' => auth()->id(),
        ]);

        return Storage::disk($invoice->attachment_disk)->download($path, $filename);
    }

    public function exportBudgetPdf(Budget $budget)
    {
        $this->authorize('view', $budget);

        AuditLogService::record('Budget PDF Export', $budget, [], [
            'participant_id' => $budget->participant_id,
            'quarter_start' => $budget->quarter_start_date,
            'exported_by' => auth()->id(),
        ]);

        return $this->budgetService->exportBudgetToPdf($budget);
    }

    public function documents(Request $request)
    {
        $query = Document::with(['owner', 'uploader', 'signatures.signedBy'])->latest();

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('document_type', 'like', "%{$search}%")
                ->orWhere('status', 'like', "%{$search}%");
        }

        $documents = $query->paginate(20)->withQueryString();

        return view('admin.documents', compact('documents'));
    }

    public function showDocument(Document $document)
    {
        $document->load(['owner', 'uploader', 'signatures.signedBy']);

        return view('admin.document', compact('document'));
    }

    protected function documentTypeOptions(): array
    {
        return [
            'agreement' => 'Agreement',
            'handbook' => 'Handbook',
            'privacy_consent' => 'Privacy & Consent',
            'worker_declaration' => 'Worker Declaration',
            'suitability_assessment' => 'Suitability Assessment',
            'support_plan' => 'Support Plan',
            'budget_plan' => 'Budget Plan',
            'authority_document' => 'Support Person Authority',
            'risk_assessment' => 'Risk Assessment',
            'other' => 'Other',
        ];
    }

    protected function documentOwnerTypes(): array
    {
        return [
            'participant' => Participant::class,
            'worker' => Worker::class,
            'invoice' => Invoice::class,
            'incident' => Incident::class,
            'pre_approval' => PreApprovalRequest::class,
            'care_review' => MonthlyCareReview::class,
        ];
    }

    public function createDocument()
    {
        $participants = Participant::orderBy('first_name')->orderBy('last_name')->get();
        $workers = Worker::orderBy('first_name')->orderBy('last_name')->get();
        $invoices = Invoice::orderBy('invoice_number')->get();
        $incidents = Incident::orderByDesc('occurred_at')->get();
        $preApprovals = PreApprovalRequest::orderByDesc('submitted_at')->get();
        $careReviews = MonthlyCareReview::orderByDesc('created_at')->get();

        $documentTypes = $this->documentTypeOptions();
        $ownerTypes = array_keys($this->documentOwnerTypes());

        $ownerType = request()->input('owner_type', 'participant');
        $ownerId = request()->input('owner_id');

        return view('admin.documents.create', compact(
            'participants',
            'workers',
            'invoices',
            'incidents',
            'preApprovals',
            'careReviews',
            'documentTypes',
            'ownerTypes',
            'ownerType',
            'ownerId'
        ));
    }

    public function storeDocument(Request $request)
    {
        $allowedDocumentTypes = array_keys($this->documentTypeOptions());

        $allowedOwnerTypes = array_keys($this->documentOwnerTypes());

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', Rule::in($allowedDocumentTypes)],
            'owner_type' => ['required', 'string', Rule::in($allowedOwnerTypes)],
            'owner_ids' => ['required', 'array', 'min:1'],
            'owner_ids.*' => ['integer'],
            'expires_at' => ['nullable', 'date'],
            'is_sensitive' => ['nullable', 'boolean'],
            'onboarding_required' => ['nullable', 'boolean'],
            'file' => Document::fileValidationRules(),
            'supporting_documents' => ['nullable', 'array'],
            'supporting_documents.*' => ['file', 'mimes:'.implode(',', Document::ALLOWED_FILE_EXTENSIONS), 'max:'.Document::MAX_FILE_SIZE_KB],
        ], [
            'owner_ids.min' => 'Please select at least one owner for the form.',
        ]);

        $ownerType = $this->documentOwnerTypes()[$validated['owner_type']];
        $ownerIds = array_values(array_filter($validated['owner_ids']));

        if (! in_array($validated['owner_type'], ['participant', 'worker']) && count($ownerIds) > 1) {
            return back()->withErrors(['owner_ids' => 'Only one owner may be selected for this type.'])->withInput();
        }

        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        $documentStatus = $request->boolean('onboarding_required') ? 'active' : 'uploaded';

        // Process supporting documents
        $supportingDocs = [];
        if ($request->hasFile('supporting_documents')) {
            foreach ($request->file('supporting_documents') as $supportingFile) {
                $supportingPath = $supportingFile->store('documents/supporting', 'local');
                $supportingDocs[] = [
                    'name' => $supportingFile->getClientOriginalName(),
                    'path' => $supportingPath,
                    'size' => $supportingFile->getSize(),
                    'mime_type' => $supportingFile->getMimeType(),
                    'id' => md5($supportingPath.time()),
                ];
            }
        }

        $assignedDocuments = [];
        foreach ($ownerIds as $ownerId) {
            $owner = $ownerType::findOrFail($ownerId);

            $metadata = [
                'supporting_documents' => $supportingDocs,
            ];

            $document = Document::create([
                'owner_type' => $ownerType,
                'owner_id' => $owner->id,
                'document_type' => $validated['document_type'],
                'description' => $request->input('description') ?? null,
                'title' => $validated['title'],
                'storage_disk' => 'local',
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'uploaded_by_id' => auth()->id(),
                'status' => $documentStatus,
                'onboarding_required' => $request->boolean('onboarding_required'),
                'expires_at' => $validated['expires_at'] ?? null,
                'is_sensitive' => $validated['is_sensitive'] ?? true,
                'metadata' => $metadata,
            ]);

            $document->versions()->create([
                'storage_disk' => 'local',
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'uploaded_by_id' => auth()->id(),
                'version_number' => 1,
            ]);

            $recipientUser = null;
            if ($ownerType === Participant::class) {
                $recipientUser = $owner->user;
            } elseif ($ownerType === Worker::class) {
                $recipientUser = $owner->user;
            }

            if ($recipientUser && $ownerType === Participant::class) {
                $signatureService = new SignatureRequestService;
                $signatureService->create($document, $recipientUser, auth()->user());
            }

            if ($recipientUser) {
                NotificationService::notify([
                    'user_id' => $recipientUser->id,
                    'type' => 'info',
                    'data' => [
                        'title' => 'New form assigned',
                        'message' => "A new form '{$document->title}' has been assigned to you.",
                        'url' => $ownerType === Participant::class ? route('portal.participant.documents.pending') : route('portal.worker.forms'),
                    ],
                ]);
            }

            $assignedDocuments[] = $document;
        }

        return redirect()->route('portal.admin.documents')->with('status', 'Form assigned successfully.');
    }

    public function uploadDocumentVersion(Request $request, Document $document)
    {
        $validated = $request->validate([
            'file' => Document::fileValidationRules(),
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $document->addVersionFromUploadedFile($request->file('file'), auth()->user(), $validated['notes'] ?? null);

        AuditLogService::record('Document Version Uploaded', $document, [], [
            'document_id' => $document->id,
            'version_count' => $document->versions()->count(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('status', 'Document version uploaded successfully.');
    }

    public function previewDocument(Document $document)
    {
        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        AuditLogService::record('Document Previewed', $document, [], [
            'previewed_by' => auth()->id(),
        ]);

        if (! $document->isPreviewable()) {
            return $this->downloadDocument($document);
        }

        $filePath = Storage::disk($document->storage_disk)->path($document->path);

        return response()->file($filePath, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'inline; filename="'.$document->title.'"',
        ]);
    }

    public function downloadDocument(Document $document)
    {
        if (! Storage::disk($document->storage_disk)->exists($document->path)) {
            abort(404);
        }

        return Storage::disk($document->storage_disk)->download($document->path, $document->title);
    }

    public function downloadDocumentVersion(Document $document, DocumentVersion $version)
    {
        if ($version->document_id !== $document->id) {
            abort(404);
        }

        if (! Storage::disk($version->storage_disk)->exists($version->path)) {
            abort(404);
        }

        $filename = $document->title.'_v'.$version->version_number.'.'.pathinfo($version->path, PATHINFO_EXTENSION);

        return Storage::disk($version->storage_disk)->download($version->path, $filename);
    }

    public function downloadDocumentSignature(Document $document, DocumentSignature $signature)
    {
        if ($signature->document_id !== $document->id) {
            abort(404);
        }

        $disk = $signature->signature_disk ?? 'local';
        $path = $signature->signature_path;

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $filename = $document->title.'_signature.'.pathinfo($path, PATHINFO_EXTENSION);

        return Storage::disk($disk)->download($path, $filename);
    }

    public function downloadDocumentCertificate(Document $document, DocumentSignature $signature)
    {
        if ($signature->document_id !== $document->id) {
            abort(404);
        }

        $disk = $signature->certificate_disk ?? 'local';
        $path = $signature->certificate_path;

        if (! $path || ! Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $filename = $document->title.'_certificate.'.pathinfo($path, PATHINFO_EXTENSION);

        AuditLogService::record('Document Certificate Download', $document, [], [
            'document_id' => $document->id,
            'signature_id' => $signature->id,
            'downloaded_by' => auth()->id(),
        ]);

        return Storage::disk($disk)->download($path, $filename);
    }

    public function reports()
    {
        return view('admin.reports');
    }

    public function exportReport(string $type)
    {
        $filename = 'admin-'.$type.'.csv';

        $rows = match ($type) {
            'participants' => Participant::query()
                ->select('participant_number', 'first_name', 'last_name', 'email', 'phone', 'status')
                ->orderBy('id')
                ->get(),
            'workers' => Worker::query()
                ->select('worker_number', 'first_name', 'last_name', 'email', 'phone', 'status')
                ->orderBy('id')
                ->get(),
            'invoices' => Invoice::query()
                ->select('invoice_number', 'status', 'amount_cents', 'invoice_date')
                ->orderBy('invoice_date')
                ->get(),
            default => throw new \InvalidArgumentException('Unsupported report type.'),
        };

        return response()->streamDownload(function () use ($rows, $type) {
            $handle = fopen('php://output', 'wb');

            if ($type === 'participants') {
                fputcsv($handle, ['participant_number', 'first_name', 'last_name', 'email', 'phone', 'status']);
            }

            if ($type === 'workers') {
                fputcsv($handle, ['worker_number', 'first_name', 'last_name', 'email', 'phone', 'status']);
            }

            if ($type === 'invoices') {
                fputcsv($handle, ['invoice_number', 'status', 'amount_cents', 'invoice_date']);
            }

            foreach ($rows as $row) {
                if ($type === 'participants') {
                    fputcsv($handle, [$row->participant_number, $row->first_name, $row->last_name, $row->email, $row->phone, $row->status]);

                    continue;
                }

                if ($type === 'workers') {
                    fputcsv($handle, [$row->worker_number, $row->first_name, $row->last_name, $row->email, $row->phone, $row->status]);

                    continue;
                }

                fputcsv($handle, [$row->invoice_number, $row->status, $row->amount_cents, $row->invoice_date]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function toggleDocumentOnboarding(Request $request, Document $document)
    {
        $document->onboarding_required = ! $document->onboarding_required;
        $document->save();

        // Notify participants currently in onboarding if we assigned the document
        if ($document->onboarding_required) {
            $participants = Participant::where('status', 'onboarding')->get();
            foreach ($participants as $participant) {
                NotificationService::notify([
                    'user_id' => $participant->user->id,
                    'type' => 'info',
                    'data' => [
                        'title' => 'Onboarding form assigned',
                        'message' => "A new form '{$document->title}' has been added to your onboarding checklist.",
                        'url' => route('portal.onboarding.show', ['token' => $participant->onboarding_token]),
                    ],
                ]);
            }
        }

        return back()->with('status', 'Onboarding assignment updated.');
    }
}
