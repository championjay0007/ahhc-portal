<?php

namespace App\Http\Middleware;

use App\Models\ParticipantAccountDelegation;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ApplyParticipantAccountContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard();
        $actorId = $request->session()->get($guard->getName());
        $actor = $actorId
            ? $guard->getProvider()->retrieveById($actorId)
            : $guard->user();

        if (! $actor instanceof User) {
            return $next($request);
        }

        $guard->setUser($actor);
        $request->attributes->set('delegate.actor', $actor);
        $request->attributes->set('delegate.actor_id', $actor->id);
        $request->attributes->set('delegate.participant_context', false);

        if (! in_array($actor->role, ['participant', 'manager'], true)) {
            View::share('delegateActor', $actor);
            View::share('managedParticipantAccounts', collect());
            View::share('participantHasManagerAccess', false);
            View::share('delegateParticipantContext', false);

            return $next($request);
        }

        $participantHasManagerAccess = false;
        if ($actor->role === 'participant' && $actor->participant) {
            $participantHasManagerAccess = $actor->participant->accountDelegations()
                ->whereNull('revoked_at')
                ->where(function ($query) {
                    $query->whereNotNull('accepted_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->exists();
        }

        $accounts = $actor->participantAccountDelegations()
            ->whereNotNull('accepted_at')
            ->whereNull('revoked_at')
            ->whereHas('participant.user', fn ($query) => $query->where('status', 'active'))
            ->with('participant.user')
            ->get();

        $selectedUserId = $request->session()->get('participant_account_user_id');
        $delegateParticipantContext = false;
        if ($selectedUserId) {
            $delegation = $accounts->first(fn ($account) => (int) $account->participant->user_id === (int) $selectedUserId);
            if ($delegation) {
                $guard->setUser($delegation->participant->user);
                $delegateParticipantContext = $actor->role === 'manager';
                $request->attributes->set('delegate.participant_context', $delegateParticipantContext);
                $request->attributes->set('delegate.participant_id', $delegation->participant_id);
            } else {
                $request->session()->forget('participant_account_user_id');
            }
        }

        View::share('delegateActor', $actor);
        View::share('managedParticipantAccounts', $accounts);
        View::share('participantHasManagerAccess', $participantHasManagerAccess);
        View::share('delegateParticipantContext', $delegateParticipantContext);

        if (
            $actor->role === 'manager'
            && $request->routeIs('portal.participant.*')
            && ! $request->routeIs('portal.participant.accounts.accept', 'portal.participant.accounts.switch')
            && ! $delegateParticipantContext
        ) {
            return redirect()->route('portal.manager.dashboard')
                ->with('status', 'Select an assigned participant account to continue.');
        }

        try {
            return $next($request);
        } finally {
            $guard->setUser($actor);
        }
    }
}