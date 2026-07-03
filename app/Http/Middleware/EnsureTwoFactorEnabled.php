<?php

namespace App\Http\Middleware;

use App\Models\PortalSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnabled
{
    protected array $requiredRoles = [
        'admin',
        'manager',
        'finance',
        'worker',
        'supplier',
        'participant',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Allow workers to continue onboarding only when MFA is not required globally.
        if ($request->routeIs('worker.onboarding.*') || $request->routeIs('portal.onboarding.*')) {
            if (! $this->isMfaRequiredGlobally()) {
                return $next($request);
            }
            // If MFA is required globally, fall through and enforce it as usual.
        }

        if (! $this->shouldEnforceMfa($user) || $user->mfa_enabled) {
            return $next($request);
        }

        return redirect()->route('portal.mfa.setup');
    }

    protected function shouldEnforceMfa($user): bool
    {
        if (! $this->isMfaRequiredGlobally()) {
            return false;
        }

        return in_array($user->role, $this->requiredRoles, true);
    }

    protected function isMfaRequiredGlobally(): bool
    {
        $setting = PortalSetting::where('key', 'require_mfa')->first();

        if (! $setting) {
            return false;
        }

        return (bool) $setting->value;
    }
}
