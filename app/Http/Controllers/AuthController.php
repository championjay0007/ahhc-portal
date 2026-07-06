<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\OnboardingProgress;
use App\Models\Participant;
use App\Models\PortalNotification;
use App\Models\PortalSetting;
use App\Models\PreApprovalRequest;
use App\Models\SignatureRequest;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerNomination;
use App\Services\AuditLogService;
use App\Services\BudgetService;
use App\Services\MessageService;
use App\Services\NotificationService;
use App\Services\OnboardingAgreementService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Force login a user using a one-time token created by an admin.
     */
    public function forceLogin(Request $request, User $user, $token)
    {
        if (! $user->force_dashboard || empty($user->force_dashboard_token)) {
            return redirect()->route('portal.login')->withErrors(['login' => 'This force login link is invalid or expired.']);
        }

        if ($user->force_dashboard_token !== $token) {
            return redirect()->route('portal.login')->withErrors(['login' => 'This force login link is invalid.']);
        }

        if ($user->force_dashboard_token_expires_at && now()->gt($user->force_dashboard_token_expires_at)) {
            return redirect()->route('portal.login')->withErrors(['login' => 'This force login link has expired.']);
        }

        Auth::loginUsingId($user->id);

        // Clear the token so it cannot be reused
        $user->update([
            'force_dashboard_token' => null,
            'force_dashboard_token_expires_at' => null,
        ]);

        $request->session()->regenerate();

        return $this->redirectToDashboard($user);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $userByEmail = User::where('email', $credentials['email'])->first();

        if ($userByEmail && $userByEmail->status !== 'active' && ! $this->canParticipantLoginWhileInactive($userByEmail)) {
            AuditLogService::record(
                'Failed Login - Inactive Account',
                $userByEmail,
                ['email' => $credentials['email']],
                [],
                $userByEmail->id
            );

            return back()->withErrors([
                'email' => 'Your account is not active. Please contact AHHC support if you believe this is an error.',
            ])->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            AuditLogService::record(
                'Failed Login',
                null,
                ['email' => $credentials['email']],
                [],
                $userByEmail?->id
            );

            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        if ($user->mfa_enabled) {
            Auth::logout();
            $request->session()->put('mfa.user_id', $user->id);
            $request->session()->put('mfa.remember', $request->boolean('remember'));

            return redirect()->route('portal.mfa.challenge');
        }

        if ($this->shouldRequireMfaForUser($user)) {
            $request->session()->regenerate();

            return redirect()->route('portal.mfa.setup');
        }

        $request->session()->regenerate();

        $user->update([
            'last_login_at' => now(),
        ]);

        AuditLogService::record('Login');

        $request->session()->flash('show_dashboard_notifications_modal', true);

        return $this->redirectToDashboard($user);
    }

    public function showForgotPasswordForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('portal.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function showRegister(Request $request)
    {
        $prefill_email = null;
        $prefill_first_name = null;
        $prefill_last_name = null;
        $prefill_role = null;

        if ($request->has('nomination')) {
            $nomination = WorkerNomination::find($request->query('nomination'));
            if ($nomination) {
                $prefill_email = $nomination->worker_email;
                $parts = preg_split('/\s+/', trim($nomination->worker_full_name ?? ''), 2);
                $prefill_first_name = $parts[0] ?? null;
                $prefill_last_name = $parts[1] ?? null;
                $prefill_role = 'worker';
                $prefill_nomination_id = $nomination->id;
            }
        }

        return view('auth.register', compact('prefill_email', 'prefill_first_name', 'prefill_last_name', 'prefill_role', 'prefill_nomination_id'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'in:participant,worker'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => $validated['password'],
            'password_changed_at' => now(),
        ]);

        $nominationId = $request->input('nomination_id');
        $profile = $this->createRoleProfile($user, $nominationId);

        if (in_array($user->role, ['participant', 'worker'], true)) {
            $this->notifyAdminsAboutNewSignup($user, $profile);
            $this->notifyUserAccountCreated($user, $profile);
        }

        // If worker registered via nomination, keep profile pending and show awaiting approval
        if ($user->role === 'worker' && $profile && $profile->status === 'pending') {
            $this->notifyAdminsAboutNewSignup($user, $profile);
            $this->notifyUserAccountCreated($user, $profile);

            return view('auth.registration-pending', ['email' => $user->email]);
        }

        Auth::login($user);

        AuditLogService::record('User Create', $user, [], [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ]);

        $request->session()->regenerate();

        return $this->redirectToDashboard($user);
    }

    public function showAdminCreate()
    {
        return view('auth.admin-create-user');
    }

    protected function redirectToDashboard(User $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('portal.admin.dashboard');
        }

        if ($user->role === 'worker') {
            return redirect()->route('portal.worker.dashboard');
        }

        if ($user->role === 'participant' && $user->participant) {
            $participant = $user->participant;

            if ($user->force_dashboard) {
                return redirect()->route('portal.dashboard');
            }

            if ($participant->status === Participant::STATUS_ONBOARDING && $participant->onboarding_token) {
                return redirect()->route('portal.onboarding.show', ['token' => $participant->onboarding_token]);
            }

            if ($participant->status === Participant::STATUS_PENDING_ADMIN_REVIEW) {
                return redirect()->route('portal.participant.documents.index');
            }

            if (in_array($participant->status, [
                Participant::STATUS_ONBOARDING,
                Participant::STATUS_AHHC_REVIEW,
                Participant::STATUS_ELIGIBILITY_ASSESSMENT,
                Participant::STATUS_SUITABILITY_ASSESSMENT,
            ], true)) {
                return redirect()->route('portal.onboarding.status');
            }
        }

        return redirect()->route('portal.dashboard');
    }

    public function createUser(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'in:participant,worker,admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'force_dashboard' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'status' => 'active',
            'force_dashboard' => $request->boolean('force_dashboard'),
            'mfa_enabled' => false,
            'password' => $validated['password'],
            'password_changed_at' => now(),
        ]);

        $profile = $this->createRoleProfile($user);

        if (in_array($user->role, ['participant', 'worker'], true)) {
            $this->notifyUserAccountCreated($user, $profile, true);
        }

        AuditLogService::record('User Create', $user, [], [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
        ]);

        return redirect()->route('portal.admin.users')->with('status', 'User created successfully.');
    }

    protected function shouldRequireMfaForUser(User $user): bool
    {
        $setting = PortalSetting::where('key', 'require_mfa')->first();

        if (! $setting) {
            return false;
        }

        if (! (bool) $setting->value) {
            return false;
        }

        return in_array($user->role, config('fortify.mfa_required_roles', []), true);
    }

    protected function canParticipantLoginWhileInactive(User $user): bool
    {
        if ($user->role !== 'participant') {
            return false;
        }

        if (! $user->participant) {
            return false;
        }

        return in_array($user->participant->status, [
            Participant::STATUS_ONBOARDING,
            Participant::STATUS_PENDING_ADMIN_REVIEW,
            Participant::STATUS_AHHC_REVIEW,
            Participant::STATUS_ELIGIBILITY_ASSESSMENT,
            Participant::STATUS_SUITABILITY_ASSESSMENT,
            Participant::STATUS_ACTIVE,
        ], true);
    }

    protected function createRoleProfile(User $user, $nominationId = null)
    {
        [$firstName, $lastName] = $this->splitFullName($user->name);

        if ($user->role === 'participant') {
            return Participant::create([
                'user_id' => $user->id,
                'participant_number' => 'P-'.$user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'status' => 'active',
                'phone' => $user->phone,
                'email' => $user->email,
                'consent_to_share' => false,
                'budget_limit_cents' => 0,
                'current_budget_used_cents' => 0,
                'created_by_id' => $user->id,
                'updated_by_id' => $user->id,
            ]);
        }

        if ($user->role === 'worker') {
            $status = $nominationId ? 'pending' : 'active';

            return Worker::create([
                'user_id' => $user->id,
                'worker_number' => 'W-'.$user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $user->phone,
                'email' => $user->email,
                'role_type' => 'worker',
                'status' => $status,
            ]);
        }

        return null;
    }

    protected function notifyAdminsAboutNewSignup(User $user, $profile = null): void
    {
        $roleLabel = $user->role === 'participant' ? 'Participant' : 'Worker';
        $notificationData = [
            'title' => "{$roleLabel} registration",
            'message' => $user->role === 'participant'
                ? "{$user->name} just registered as a new participant."
                : "{$user->name} just registered as a new worker.",
            'url' => route('portal.admin.activity'),
        ];

        User::where('role', 'admin')->get()->each(function (User $admin) use ($profile, $notificationData) {
            NotificationService::notify(array_merge([
                'user_id' => $admin->id,
                'type' => 'info',
            ], $profile instanceof Participant ? ['participant_id' => $profile->id] : ['worker_id' => $profile?->id], [
                'data' => $notificationData,
            ]));
        });
    }

    protected function notifyUserAccountCreated(User $user, $profile = null, bool $createdByAdmin = false): void
    {
        $notificationData = [
            'title' => 'Account created',
            'message' => $createdByAdmin
                ? 'Your account has been created by an AHHC administrator. Please sign in to continue.'
                : 'Your account has been created successfully. Welcome to the AHHC portal.',
            'url' => $user->role === 'worker' ? route('portal.worker.dashboard') : route('portal.dashboard'),
        ];

        NotificationService::notify(array_merge([
            'user_id' => $user->id,
            'type' => 'success',
        ], $profile instanceof Participant ? ['participant_id' => $profile->id] : ['worker_id' => $profile?->id], [
            'data' => $notificationData,
        ]));

        $template = $this->findAccountCreatedTemplate($user, $createdByAdmin);
        if ($template) {
            $senderId = Auth::id() ?: User::where('role', 'admin')->orderBy('id')->value('id') ?: $user->id;
            MessageService::sendMessageUsingTemplate(
                $senderId,
                $user->id,
                $template,
                $this->buildAccountCreationReplacements($user, $profile, $createdByAdmin)
            );
        }
    }

    protected function findAccountCreatedTemplate(User $user, bool $createdByAdmin = false): ?MessageTemplate
    {
        $searchTerms = [
            'account',
            'registration',
            'welcome',
            $user->role,
            $createdByAdmin ? 'admin' : 'self',
        ];

        $template = MessageTemplate::where('is_active', true)
            ->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%");
                }
            })
            ->orderByDesc('updated_at')
            ->first();

        return $template ?? MessageTemplate::where('is_active', true)
            ->where('type', 'notification')
            ->orderByDesc('updated_at')
            ->first();
    }

    protected function buildAccountCreationReplacements(User $user, $profile = null, bool $createdByAdmin = false): array
    {
        [$firstName, $lastName] = $this->splitFullName($user->name);

        $replacements = [
            'user_name' => $user->name,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user->email,
            'role' => ucfirst($user->role),
            'dashboard_url' => $user->role === 'admin' ? route('portal.admin.dashboard') : ($user->role === 'worker' ? route('portal.worker.dashboard') : route('portal.dashboard')),
            'registration_source' => $createdByAdmin ? 'administrator' : 'self-registration',
        ];

        if ($profile instanceof Participant) {
            $replacements['participant_name'] = "{$profile->first_name} {$profile->last_name}";
            $replacements['participant_number'] = $profile->participant_number;
        }

        if ($profile instanceof Worker) {
            $replacements['worker_name'] = "{$profile->first_name} {$profile->last_name}";
            $replacements['worker_number'] = $profile->worker_number;
        }

        return $replacements;
    }

    protected function splitFullName(string $fullName): array
    {
        $trimmedName = trim($fullName);

        if ($trimmedName === '') {
            return ['User', 'User'];
        }

        $parts = preg_split('/\s+/', $trimmedName);
        $firstName = $parts[0] ?? 'User';
        $lastName = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'User';

        return [$firstName, $lastName];
    }

    public function profile()
    {
        $user = Auth::user();

        if ($user->role === 'worker') {
            return redirect()->route('portal.worker.profile');
        }

        return view('portal.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'worker') {
            return redirect()->route('portal.worker.profile');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $validated['profile_photo_path'] = $request->file('profile_photo')->storePublicly('profile_photos', 'public');
        }

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'profile_photo_path' => $validated['profile_photo_path'] ?? $user->profile_photo_path,
        ]);

        if ($participant = $user->participant) {
            [$firstName, $lastName] = $this->splitFullName($validated['name']);
            $participant->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
            ]);
        }

        return back()->with('status', 'Profile updated successfully.');
    }

    public function dashboard()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return redirect()->route('portal.admin.dashboard');
        }

        if ($user->role === 'worker') {
            return redirect()->route('portal.worker.dashboard');
        }

        $participant = null;
        $documents = collect();
        $invoices = collect();
        $preApprovals = collect();
        $careNotes = collect();
        $complaints = collect();
        $workers = collect();
        $assignments = collect();

        $documentsCount = 0;
        $preApprovalsCount = 0;
        $invoicesCount = 0;
        $openComplaintsCount = 0;
        $pendingDocumentsCount = 0;
        $careNotesCount = 0;
        $workersCount = 0;
        $preApprovalsApprovedCount = 0;
        $preApprovalsPendingCount = 0;
        $submittedInvoicesCount = 0;
        $paidInvoicesCount = 0;
        $budgetLimitCents = 0;
        $usedBudgetCents = 0;
        $committedBudgetCents = 0;
        $remainingBudgetCents = 0;
        $budgetPercent = 0;
        $currentQuarterLabel = 'Current quarter';
        $budgetUpdatedAtLabel = now()->format('j M Y');
        $participantName = $user->name;
        $participantStatus = 'active';
        $supportPerson = null;
        $alerts = collect();
        $unreadNotificationCount = 0;
        $unreadMessageCount = 0;
        $upcomingServices = collect();

        if ($user->role === 'participant') {
            $participant = Participant::firstOrNew(['user_id' => $user->id]);

            if (! $participant->exists) {
                [$firstName, $lastName] = $this->splitFullName($user->name);

                $participant->fill([
                    'participant_number' => 'P-'.$user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'status' => 'active',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'consent_to_share' => false,
                    'budget_limit_cents' => 0,
                    'current_budget_used_cents' => 0,
                    'created_by_id' => $user->id,
                    'updated_by_id' => $user->id,
                ])->save();
            }

            $participant->load('supportPerson');
            $documents = $participant->documents()->latest()->take(10)->get();
            $invoices = $participant->invoices()->latest()->take(10)->get();
            $preApprovals = $participant->preApprovalRequests()->latest()->take(10)->get();
            $careNotes = $participant->careNotes()->latest()->take(10)->get();
            $complaints = $participant->complaints()->latest()->take(10)->get();
            $assignments = $participant->assignments()->with('worker')->where('status', 'active')->get();
            $workers = $assignments->pluck('worker')->unique();

            // Load unread counts
            $unreadNotificationCount = PortalNotification::where('user_id', $user->id)->whereNull('read_at')->count();
            $unreadMessageCount = Message::where('recipient_id', $user->id)->whereNull('read_at')->count();

            // Build upcoming services from pre-approval requests (preferred source for participant-managed services)
            $upcomingServices = $participant->preApprovalRequests()
                ->with('worker')
                ->whereIn('status', ['submitted', 'approved'])
                ->where(function ($q) {
                    $q->whereDate('start_date', '>=', now()->toDateString())
                        ->orWhereNull('start_date');
                })
                ->orderBy('start_date', 'asc')
                ->get();

            $documentsCount = $participant->documents()->count();
            $preApprovalsCount = $participant->preApprovalRequests()->count();
            $preApprovalsApprovedCount = $participant->preApprovalRequests()
                ->whereIn('status', [PreApprovalRequest::STATUS_APPROVED, PreApprovalRequest::STATUS_APPROVED_WITH_CONDITIONS])
                ->count();
            $preApprovalsPendingCount = $participant->preApprovalRequests()
                ->whereIn('status', [PreApprovalRequest::STATUS_SUBMITTED, PreApprovalRequest::STATUS_INFO_REQUESTED])
                ->count();
            $invoicesCount = $participant->invoices()->count();
            $submittedInvoicesCount = $participant->invoices()->where('status', 'submitted')->count();
            $paidInvoicesCount = $participant->invoices()->where('status', 'paid')->count();
            $openComplaintsCount = $participant->complaints()->where('status', 'open')->count();
            $pendingDocumentsCount = SignatureRequest::where('assigned_user_id', $user->id)
                ->whereIn('status', [SignatureRequest::STATUS_PENDING, SignatureRequest::STATUS_VIEWED])
                ->count();
            $careNotesCount = $participant->careNotes()->count();
            $workersCount = $workers->count();

            $budgetService = new BudgetService;
            $budget = $budgetService->getOrCreateBudgetForParticipantQuarter($participant, now());
            $budgetMetrics = $budgetService->getBudgetMetrics($budget);

            $budgetLimitCents = $budgetMetrics['total_available'] > 0
                ? $budgetMetrics['total_available']
                : max(0, (int) ($participant->budget_limit_cents ?? 0));
            $usedBudgetCents = $budgetMetrics['used'] ?? max(0, (int) ($participant->current_budget_used_cents ?? 0));
            $committedBudgetCents = $budgetMetrics['committed'] ?? 0;
            $remainingBudgetCents = $budgetMetrics['remaining'] ?? max(0, $budgetLimitCents - $committedBudgetCents - $usedBudgetCents);

            $budgetPercent = isset($budgetMetrics['utilization_percent'])
                ? min(100, (int) round($budgetMetrics['utilization_percent']))
                : ($budgetLimitCents > 0 ? min(100, (int) round(($usedBudgetCents / $budgetLimitCents) * 100)) : 0);
            $currentQuarterLabel = $budget->quarter_start_date && $budget->quarter_end_date
                ? $budget->quarter_start_date->format('j M Y').' – '.$budget->quarter_end_date->format('j M Y')
                : $this->formatFiscalQuarterLabel(now());
            $budgetUpdatedAtLabel = optional($budget->updated_at ?? $budget->created_at)->format('j M Y') ?? now()->format('j M Y');
            $participantName = $participant->preferred_name ?? $participant->first_name;
            $participantStatus = $participant->status;
            $supportPerson = optional($participant)->supportPerson;

            $onboardingProgress = OnboardingProgress::where('participant_id', $participant->id)->first();
            $onboardingInProgress = $participant->status === Participant::STATUS_ONBOARDING;
            $onboardingCurrentStep = $onboardingProgress?->current_step ?? 1;
            $onboardingCompletedSteps = $onboardingProgress?->completed_steps ?? [];
            $onboardingRemainingSteps = max(0, 8 - count($onboardingCompletedSteps));
            $onboardingCompletionPercent = $onboardingProgress ? (int) round($onboardingProgress->completionPercentage()) : 0;
            $onboardingStatus = $onboardingProgress?->status ?? ($onboardingInProgress ? 'in_progress' : 'complete');
            $onboardingResumeUrl = $participant->onboarding_token ? route('portal.onboarding.show', ['token' => $participant->onboarding_token]) : null;
            $onboardingSignedAgreementCount = Document::query()
                ->where('owner_type', Participant::class)
                ->where('owner_id', $participant->id)
                ->whereHas('signatures')
                ->whereIn('document_type', array_values(OnboardingAgreementService::requiredAgreements()))
                ->count();

            $requiredDocumentCategories = Document::mandatoryParticipantDocumentCategories();
            $uploadedDocumentCategories = $participant->documents()
                ->pluck('document_type')
                ->map(fn ($type) => Document::normalizeParticipantDocumentCategory($type))
                ->unique();
            $missingDocumentCategories = collect($requiredDocumentCategories)
                ->diff($uploadedDocumentCategories)
                ->values();
            $requiredDocumentCompletionPercent = round((count($requiredDocumentCategories) - $missingDocumentCategories->count()) / max(count($requiredDocumentCategories), 1) * 100);

            $onboardingSteps = collect([
                'Account setup',
                'Multi-factor authentication',
                'Profile details',
                'Emergency contact',
                'Support person',
                'Upload documents',
                'Sign agreements',
                'Review & complete',
            ])->map(function ($label, $index) use ($onboardingCompletedSteps, $onboardingCurrentStep) {
                $step = $index + 1;
                $state = in_array($step, $onboardingCompletedSteps, true)
                    ? 'completed'
                    : ($step === $onboardingCurrentStep ? 'current' : 'pending');

                return [
                    'step' => $step,
                    'label' => $label,
                    'state' => $state,
                ];
            })->all();

            // Build alerts array
            $alerts = collect();
            if ($pendingDocumentsCount > 0) {
                $alerts->push([
                    'type' => 'danger',
                    'icon' => 'bi-pen-fill',
                    'title' => 'Pending Signatures',
                    'message' => "$pendingDocumentsCount document(s) need your e-signature",
                    'action' => route('portal.participant.documents.pending'),
                    'action_label' => 'Sign Now',
                ]);
            }
            if ($budgetPercent >= 80) {
                $alerts->push([
                    'type' => 'warning',
                    'icon' => 'bi-exclamation-triangle-fill',
                    'title' => 'Budget Alert',
                    'message' => "You've used {$budgetPercent}% of your quarterly budget",
                    'action' => route('portal.participant.budget'),
                    'action_label' => 'View Budget',
                ]);
            }
            if ($preApprovalsCount > 0) {
                $pendingApprovals = $participant->preApprovalRequests()->where('status', 'submitted')->count();
                if ($pendingApprovals > 0) {
                    $alerts->push([
                        'type' => 'info',
                        'icon' => 'bi-info-circle-fill',
                        'title' => 'Pending Approvals',
                        'message' => "$pendingApprovals pre-approval request(s) awaiting review",
                        'action' => route('portal.participant.pre_approvals.index'),
                        'action_label' => 'View Details',
                    ]);
                }
            }
            if ($openComplaintsCount > 0) {
                $alerts->push([
                    'type' => 'secondary',
                    'icon' => 'bi-chat-left-text-fill',
                    'title' => 'Open Complaints',
                    'message' => "$openComplaintsCount complaint(s) in progress",
                    'action' => route('portal.participant.complaints.create'),
                    'action_label' => 'Manage',
                ]);
            }
        }

        return view('portal.dashboard', compact(
            'user',
            'participant',
            'participantName',
            'participantStatus',
            'supportPerson',
            'documents',
            'invoices',
            'preApprovals',
            'upcomingServices',
            'careNotes',
            'complaints',
            'workers',
            'assignments',
            'alerts',
            'documentsCount',
            'preApprovalsCount',
            'invoicesCount',
            'openComplaintsCount',
            'pendingDocumentsCount',
            'careNotesCount',
            'workersCount',
            'budgetLimitCents',
            'usedBudgetCents',
            'committedBudgetCents',
            'remainingBudgetCents',
            'budgetPercent',
            'currentQuarterLabel',
            'budgetUpdatedAtLabel',
            'preApprovalsApprovedCount',
            'preApprovalsPendingCount',
            'submittedInvoicesCount',
            'paidInvoicesCount',
            'onboardingInProgress',
            'onboardingCurrentStep',
            'onboardingRemainingSteps',
            'onboardingCompletionPercent',
            'onboardingStatus',
            'onboardingResumeUrl',
            'onboardingSignedAgreementCount',
            'requiredDocumentCategories',
            'uploadedDocumentCategories',
            'missingDocumentCategories',
            'requiredDocumentCompletionPercent',
            'onboardingSteps'
        ));
    }

    public function logout(Request $request)
    {
        AuditLogService::record('Logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
