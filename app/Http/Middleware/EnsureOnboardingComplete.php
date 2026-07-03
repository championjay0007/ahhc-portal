<?php

namespace App\Http\Middleware;

use App\Models\Participant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user->role === 'worker') {
            $worker = $user->worker;

            if ($worker && $worker->onboarding_stage >= 6) {
                return $next($request);
            }

            if ($user->force_dashboard) {
                return $next($request);
            }

            if ($request->routeIs('worker.onboarding.*') || $request->routeIs('portal.login') || $request->routeIs('portal.mfa.*')) {
                return $next($request);
            }

            if ($worker && $worker->onboarding_token && $worker->onboarding_expires_at && $worker->onboarding_expires_at->isFuture()) {
                return redirect()->route('worker.onboarding.show', ['token' => $worker->onboarding_token]);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('portal.login')
                ->withErrors(['onboarding' => 'Your worker onboarding is still in progress. Please use your onboarding link or contact AHHC support.']);
        }

        if ($user->role !== 'participant') {
            return $next($request);
        }

        $participant = $user->participant;

        if (! $participant || $participant->status !== Participant::STATUS_ONBOARDING) {
            return $next($request);
        }

        if ($user->force_dashboard) {
            return $next($request);
        }

        if ($request->routeIs('portal.onboarding.*') || $request->routeIs('portal.login') || $request->routeIs('portal.register') || $request->routeIs('portal.mfa.*')) {
            return $next($request);
        }

        if ($participant->onboarding_token && $participant->onboarding_expires_at && $participant->onboarding_expires_at->isFuture()) {
            return redirect()->route('portal.onboarding.show', ['token' => $participant->onboarding_token]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')
            ->withErrors(['onboarding' => 'Your onboarding process is still in progress. Please use your onboarding link or contact AHHC support.']);
    }
}
