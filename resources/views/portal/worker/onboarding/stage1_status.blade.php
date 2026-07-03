@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Worker Onboarding - Stage 1: Account Created</h4>
                </div>
                <div class="card-body p-5">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">Account created successfully</h6>
                        <p class="mb-2">Your worker account has been created. To continue onboarding, please complete multi-factor authentication (MFA).</p>
                        <p class="mb-0">Once MFA is enabled, Allegiance Heart &amp; Home Care administrators will advance your onboarding to Stage 2.</p>
                    </div>

                    @if ($mfaEnabled)
                        <div class="alert alert-success">
                            <h6 class="mb-1">MFA Completed</h6>
                            <p class="mb-3">Thank you. Your account is now secured and you will be taken to the next onboarding step.</p>
                            <a href="{{ route('worker.onboarding.show', ['token' => $token]) }}" class="btn btn-outline-primary">Continue onboarding</a>
                            <p class="small text-muted mt-2">If you are not redirected automatically, use the button above.</p>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <h6 class="mb-1">Awaiting MFA Setup</h6>
                            <p class="mb-2">Please sign in using your email and password, then follow the MFA setup instructions.</p>
                            @if ($mfaRequired)
                                <p class="mb-0 text-muted">Multi-factor authentication is currently required by the organisation policy. This setting can be toggled by your administrator in System Settings → MFA.</p>
                            @else
                                <p class="mb-0 text-muted">MFA is optional for your organisation right now. If administrators have disabled it, you may continue onboarding without it.</p>
                            @endif
                        </div>
                    @endif

                    <div class="mt-4">
                        <p><strong>Name:</strong> {{ $worker->first_name }} {{ $worker->last_name }}</p>
                        <p><strong>Email:</strong> {{ $worker->email }}</p>
                        <p><strong>Phone:</strong> {{ $worker->phone }}</p>
                        <p><strong>Invitation Expires:</strong> {{ $worker->onboarding_expires_at?->format('M d, Y') ?? 'N/A' }}</p>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('worker.onboarding.show', ['token' => $token]) }}" class="btn btn-primary w-100">
                            @if ($mfaRequired)
                                Sign in to complete MFA
                            @else
                                Continue onboarding
                            @endif
                        </a>
                    </div>

                    <div class="alert alert-light mt-4 small">
                        @if ($mfaRequired)
                            Multi-factor authentication is required by organisation policy. Contact your administrator if you believe this setting should be changed.
                        @else
                            MFA is currently disabled by organisation policy, so you may proceed with onboarding without enabling it.
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
