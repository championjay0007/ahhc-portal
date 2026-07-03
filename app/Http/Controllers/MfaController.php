<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;

class MfaController extends Controller
{
    public function showSetup()
    {
        $user = Auth::user();

        if ($user->mfa_enabled) {
            return redirect()->route('portal.dashboard')->with('status', 'Multi-factor authentication is already enabled.');
        }

        if (! $user->two_factor_secret) {
            app(EnableTwoFactorAuthentication::class)($user);
        }

        return view('auth.mfa_setup', [
            'user' => $user,
            'qrCodeSvg' => $user->twoFactorQrCodeSvg(),
            'recoveryCodes' => $user->two_factor_recovery_codes ? $user->recoveryCodes() : [],
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = Auth::user();
        $provider = app(TwoFactorAuthenticationProvider::class);

        if (! $user->two_factor_secret) {
            return redirect()->route('portal.mfa.setup');
        }

        $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);

        if (! $provider->verify($secret, $request->code)) {
            return back()->withErrors(['code' => 'The provided authentication code is invalid or expired.']);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'mfa_enabled' => true,
            'mfa_enrolled_at' => now(),
        ])->save();

        AuditLogService::record('MFA Enabled', $user, [], []);

        return redirect()->route('portal.dashboard')->with('status', 'Multi-factor authentication has been enabled.');
    }

    public function showChallenge(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('portal.dashboard');
        }

        if (! $request->session()->has('mfa.user_id')) {
            return redirect()->route('portal.login');
        }

        return view('auth.two_factor_challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = User::find($request->session()->get('mfa.user_id'));

        if (! $user || ! $user->two_factor_secret) {
            return redirect()->route('portal.login')->withErrors(['email' => 'Your authentication session has expired. Please sign in again.']);
        }

        $provider = app(TwoFactorAuthenticationProvider::class);
        $secret = Fortify::currentEncrypter()->decrypt($user->two_factor_secret);
        $code = $request->code;

        if ($provider->verify($secret, $code)) {
            return $this->completeTwoFactorLogin($request, $user);
        }

        if (in_array($code, $user->recoveryCodes(), true)) {
            $user->replaceRecoveryCode($code);

            return $this->completeTwoFactorLogin($request, $user);
        }

        return back()->withErrors(['code' => 'Invalid two-factor authentication code.']);
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        if ($user->mfa_enabled) {
            app(DisableTwoFactorAuthentication::class)($user);
            $user->forceFill([
                'mfa_enabled' => false,
                'mfa_enrolled_at' => null,
            ])->save();

            AuditLogService::record('MFA Disabled', $user, [], []);
        }

        return redirect()->route('portal.profile')->with('status', 'Multi-factor authentication has been disabled.');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = Auth::user();

        if (! $user->mfa_enabled) {
            return redirect()->route('portal.mfa.setup')->withErrors(['mfa' => 'MFA must be enabled to regenerate recovery codes.']);
        }

        app(GenerateNewRecoveryCodes::class)($user);

        AuditLogService::record('MFA Recovery Codes Regenerated', $user, [], []);

        return back()->with('status', 'Your recovery codes have been refreshed. Store them securely.');
    }

    public function resetUserMfa(User $user)
    {
        app(DisableTwoFactorAuthentication::class)($user);

        $user->forceFill([
            'mfa_enabled' => false,
            'mfa_enrolled_at' => null,
        ])->save();

        AuditLogService::record('MFA Reset by Admin', $user, [], []);

        return back()->with('status', 'The user’s MFA has been reset. They must re-enroll at next login.');
    }

    protected function completeTwoFactorLogin(Request $request, User $user)
    {
        Auth::login($user, $request->session()->pull('mfa.remember', false));
        $request->session()->forget(['mfa.user_id', 'mfa.remember']);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        AuditLogService::record('Login', $user);

        return redirect()->intended(route('portal.dashboard'));
    }
}
