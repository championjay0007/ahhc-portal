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

        if (! in_array($actor->role, ['participant', 'manager'], true)) {
            View::share('delegateActor', $actor);
            View::share('managedParticipantAccounts', collect());

            return $next($request);
        }

        $accounts = $actor->participantAccountDelegations()
            ->whereNotNull('accepted_at')
            ->whereNull('revoked_at')
            ->whereHas('participant.user', fn ($query) => $query->where('status', 'active'))
            ->with('participant.user')
            ->get();

        $selectedUserId = $request->session()->get('participant_account_user_id');
        if ($selectedUserId) {
            $delegation = $accounts->first(fn ($account) => (int) $account->participant->user_id === (int) $selectedUserId);
            if ($delegation) {
                $guard->setUser($delegation->participant->user);
            } else {
                $request->session()->forget('participant_account_user_id');
            }
        }

        View::share('delegateActor', $actor);
        View::share('managedParticipantAccounts', $accounts);

        try {
            return $next($request);
        } finally {
            $guard->setUser($actor);
        }
    }
}