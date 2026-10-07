<?php

namespace App\Http\Controllers;

use App\Models\ParticipantAccountDelegation;
use App\Models\User;
use App\Notifications\ParticipantAccountInvitation;
use App\Services\AuditLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ParticipantAccountController extends Controller
{
    public function managerDashboard(Request $request)
    {
        $actor = $request->attributes->get('delegate.actor');
        abort_unless($actor instanceof User && $actor->role === 'manager', 403);

        $delegations = $actor->participantAccountDelegations()
            ->whereNotNull('accepted_at')
            ->whereNull('revoked_at')
            ->whereHas('participant.user', fn ($query) => $query->where('status', 'active'))
            ->with('participant.user')
            ->latest('accepted_at')
            ->get();

        return view('portal.manager.dashboard', compact('delegations'));
    }

    public function index(Request $request)
    {
        $this->assertParticipantAccountContext($request);
        $participant = Auth::user()->participant()->firstOrFail();
        $delegations = $participant->accountDelegations()->with('manager')->latest()->get();

        return view('portal.participant.accounts', compact('delegations'));
    }

    public function invite(Request $request)
    {
        $this->assertParticipantAccountContext($request);
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $participant = Auth::user()->participant()->firstOrFail();
        $email = mb_strtolower(trim($validated['email']));
        $actor = $request->attributes->get('delegate.actor');
        abort_if(
            $email === mb_strtolower(Auth::user()->email)
                || ($actor instanceof User && $email === mb_strtolower($actor->email)),
            422,
            'You cannot invite your own account.'
        );

        $existing = $participant->accountDelegations()
            ->whereRaw('LOWER(invited_email) = ?', [$email])
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->exists();

        if ($existing) {
            return back()->withErrors(['email' => 'There is already an active invitation or access link for this email.']);
        }

        $token = Str::random(64);
        $invitation = $participant->accountDelegations()->create([
            'invited_email' => $email,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(14),
        ]);

        Notification::route('mail', $email)->notify(new ParticipantAccountInvitation(
            $participant->user->name,
            $token
        ));
        AuditLogService::record('Participant Account Invitation Created', $invitation, [], [
            'participant_id' => $participant->id,
            'invited_email' => $email,
        ]);

        return redirect()->route('portal.participant.accounts.index')->with('status', 'Invitation sent. Access begins only after the recipient accepts it.');
    }

    public function showInvitation(Request $request, string $token)
    {
        $invitation = $this->findPendingInvitation($token);
        $actor = $request->attributes->get('delegate.actor') ?? Auth::user();

        if (! $actor) {
            $existingUser = User::whereRaw('LOWER(email) = ?', [$invitation->invited_email])->first();

            return view('auth.manager-register', compact('invitation', 'existingUser'));
        }

        abort_unless(
            $actor instanceof User
                && $actor->role === 'manager'
                && strcasecmp($actor->email, $invitation->invited_email) === 0,
            403
        );

        return view('portal.participant.account-invitation', compact('invitation'));
    }

    public function registerManager(Request $request, string $token)
    {
        abort_unless(! Auth::check(), 403);

        $invitation = $this->findPendingInvitation($token);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        abort_if(
            User::whereRaw('LOWER(email) = ?', [$invitation->invited_email])->exists(),
            422,
            'This email already has an account. Sign in to the existing manager account to accept the invitation.'
        );

        $manager = User::create([
            'name' => $validated['name'],
            'email' => $invitation->invited_email,
            'phone' => $validated['phone'] ?? null,
            'role' => 'manager',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => $validated['password'],
            'password_changed_at' => now(),
        ]);

        $invitation->update([
            'manager_user_id' => $manager->id,
            'token_hash' => null,
            'accepted_at' => now(),
        ]);
        Auth::login($manager);
        $request->session()->regenerate();

        AuditLogService::record('Participant Account Invitation Accepted', $invitation, [], [
            'participant_id' => $invitation->participant_id,
            'manager_user_id' => $manager->id,
        ]);

        User::whereIn('role', ['admin', 'system_admin'])
            ->where('status', 'active')
            ->get()
            ->each(function (User $admin) use ($invitation, $manager) {
                NotificationService::notify([
                    'user_id' => $admin->id,
                    'participant_id' => $invitation->participant_id,
                    'type' => 'info',
                    'data' => [
                        'title' => 'New manager registered',
                        'message' => "{$manager->name} ({$manager->email}) registered as a manager for {$invitation->participant->user->name}.",
                        'url' => route('portal.admin.managers'),
                    ],
                ]);
            });

        $requireMfa = (bool) \App\Models\PortalSetting::where('key', 'require_mfa')->value('value');
        if ($requireMfa && in_array('manager', config('fortify.mfa_required_roles', []), true)) {
            return redirect()->route('portal.mfa.setup');
        }

        return redirect()->route('portal.manager.dashboard')
            ->with('status', 'Your manager account is ready. You can now access the assigned participant account.');
    }

    public function acceptInvitation(Request $request, string $token)
    {
        $invitation = $this->findPendingInvitation($token);
        $actor = $request->attributes->get('delegate.actor');

        abort_unless(
            $actor instanceof User
                && $actor->role === 'manager'
                && $actor->status === 'active'
                && strcasecmp($actor->email, $invitation->invited_email) === 0,
            403
        );

        abort_if($invitation->participant->user_id === $actor->id, 422, 'You cannot manage your own account as a delegate.');

        $invitation->update([
            'manager_user_id' => $actor->id,
            'token_hash' => null,
            'accepted_at' => now(),
        ]);
        $request->session()->put('participant_account_user_id', $invitation->participant->user_id);
        AuditLogService::record('Participant Account Invitation Accepted', $invitation, [], [
            'participant_id' => $invitation->participant_id,
            'manager_user_id' => $actor->id,
        ]);

        return redirect()->route('portal.manager.dashboard')
            ->with('status', 'Access accepted. You can now access the assigned participant account.');
    }

    public function switchAccount(Request $request)
    {
        $actor = $request->attributes->get('delegate.actor');
        abort_unless($actor instanceof User && in_array($actor->role, ['participant', 'manager'], true), 403);

        $participantUserId = $request->input('participant_user_id');
        if (blank($participantUserId) || (int) $participantUserId === (int) $actor->id) {
            $request->session()->forget('participant_account_user_id');

            $route = $actor->role === 'manager' ? 'portal.manager.dashboard' : 'portal.dashboard';

            return redirect()->route($route)->with('status', 'You are back in your own account.');
        }

        $delegation = ParticipantAccountDelegation::query()
            ->where('manager_user_id', $actor->id)
            ->whereNotNull('accepted_at')
            ->whereNull('revoked_at')
            ->whereHas('participant', fn ($query) => $query->where('user_id', $participantUserId))
            ->firstOrFail();

        $request->session()->put('participant_account_user_id', $delegation->participant->user_id);
        AuditLogService::record('Participant Account Switched', $delegation, [], [
            'participant_id' => $delegation->participant_id,
        ]);

        return redirect()->route('portal.dashboard')->with('status', 'Switched to '.$delegation->participant->user->name.'’s account.');
    }

    public function revoke(Request $request, ParticipantAccountDelegation $delegation)
    {
        $this->assertParticipantAccountContext($request);
        $participant = Auth::user()->participant()->firstOrFail();
        abort_unless($delegation->participant_id === $participant->id, 404);

        $delegation->update(['revoked_at' => now()]);
        $actor = $request->attributes->get('delegate.actor');
        AuditLogService::record('Participant Account Access Revoked', $delegation, [], [
            'participant_id' => $participant->id,
            'manager_user_id' => $delegation->manager_user_id,
        ]);

        if ($actor instanceof User && $actor->role === 'manager' && $delegation->manager_user_id === $actor->id) {
            return redirect()->route('portal.manager.dashboard')
                ->with('status', 'Your access to this participant account has been revoked.');
        }

        return back()->with('status', 'Invitation or delegated access has been revoked.');
    }

    private function findPendingInvitation(string $token): ParticipantAccountDelegation
    {
        return ParticipantAccountDelegation::query()
            ->with('participant.user')
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();
    }

    private function assertParticipantAccountContext(Request $request): void
    {
        $actor = $request->attributes->get('delegate.actor');
        $isOwner = $actor instanceof User
            && $actor->role === 'participant'
            && (int) $actor->id === (int) Auth::id();
        $isAssignedManager = $actor instanceof User
            && $actor->role === 'manager'
            && $request->attributes->get('delegate.participant_context') === true
            && (int) $request->attributes->get('delegate.participant_id') === (int) Auth::user()?->participant?->id;

        abort_unless($isOwner || $isAssignedManager, 403);
    }
}