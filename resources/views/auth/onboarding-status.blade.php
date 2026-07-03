@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="status-icon mb-3">
                            <i class="bi bi-hourglass-split text-primary" style="font-size: 3rem;"></i>
                        </div>
                        <h2 class="card-title mb-2">{{ ucfirst(str_replace('_', ' ', $participant->status)) }}</h2>
                        <p class="text-muted mb-0">Application Status</p>
                    </div>

                    <div class="alert alert-info mb-4" role="alert">
                        <p class="mb-0">{{ $statusMessage }}</p>
                    </div>

                    @if($participant->status === \App\Models\Participant::STATUS_ONBOARDING && $onboardingTokenValid)
                        <div class="mb-4">
                            <h5 class="mb-3">Resume Onboarding</h5>
                            <p class="text-muted small mb-3">Complete the remaining steps of your onboarding wizard.</p>
                            <a href="{{ route('portal.onboarding.show', ['token' => $onboardingToken]) }}" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-right me-2"></i>Continue Onboarding
                            </a>
                        </div>

                        @if($onboardingProgress > 0)
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $onboardingProgress }}%;" aria-valuenow="{{ $onboardingProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted">{{ $onboardingProgress }}% complete</small>
                        @endif
                    @endif

                    <div class="divider my-4">
                        <span class="text-muted small">OR</span>
                    </div>

                    <div class="support-section">
                        <h5 class="mb-3">Need Help?</h5>
                        <p class="text-muted small mb-3">If you have questions about your application or onboarding status, please contact the Allegiance Heart &amp; Home Care support team.</p>
                        <a href="{{ route('portal.support.create') }}" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-chat-left-text me-2"></i>Contact Support
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <a href="{{ route('portal.logout') }}" class="btn btn-link text-decoration-none">
                            <i class="bi bi-box-arrow-right me-2"></i>Log Out
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-3 border-0 bg-light">
                <div class="card-body p-3">
                    <small class="text-muted d-block mb-2">
                        <strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $participant->status)) }}
                    </small>
                    <small class="text-muted d-block">
                        <strong>Last Updated:</strong> {{ optional($participant->updated_at)->format('d M Y H:i') ?? 'Recently' }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .divider {
        position: relative;
        text-align: center;
    }
    .divider span {
        background: white;
        padding: 0 10px;
        position: relative;
        z-index: 1;
    }
    .divider::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        height: 1px;
        background: #e9ecef;
        z-index: 0;
    }
</style>
@endsection
