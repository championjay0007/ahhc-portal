@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h2 class="h4 mb-1">Onboarding Status</h2>
                            <p class="text-muted mb-0">Track your progress and next steps for account activation.</p>
                        </div>
                        <div>
                            <span class="badge bg-{{ $participant->isActivated() ? 'success' : ($participant->onboarding_status === 'pending_review' ? 'warning' : 'info') }} text-white">
                                {{ ucfirst(str_replace('_', ' ', $participant->onboarding_status)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($participant->onboarding_status === 'changes_requested')
                        <div class="alert alert-warning">
                            <h5 class="mb-2">Changes Requested</h5>
                            <p class="mb-0">Your onboarding was reviewed and changes are required before approval.</p>
                        </div>
                    @elseif($participant->onboarding_status === 'rejected')
                        <div class="alert alert-danger">
                            <h5 class="mb-2">Onboarding Rejected</h5>
                            <p class="mb-0">Your onboarding submission was rejected. Please contact support for next steps.</p>
                        </div>
                    @endif

                    <div class="row g-3">
                        @foreach($checklist as $key => $completed)
                            <div class="col-md-4">
                                <div class="p-3 rounded border {{ $completed ? 'border-success bg-success bg-opacity-10' : 'border-secondary bg-light' }}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="fw-semibold text-uppercase small">{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                                        <span class="badge {{ $completed ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                            {{ $completed ? 'Done' : 'Pending' }}
                                        </span>
                                    </div>
                                    <p class="mb-0 text-muted small">
                                        {{ $key === 'approved' ? 'Review by Allegiance Heart &amp; Home Care' : 'Required to complete onboarding' }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(! $participant->isActivated() && $participant->onboarding_status !== 'pending_review')
                        <div class="mt-4 p-4 rounded border border-info bg-info bg-opacity-10">
                            <p class="mb-2 fw-semibold">Next step</p>
                            <p class="text-muted mb-2">
                                Complete the onboarding form if you have not yet submitted your details. Once submitted, Allegiance Heart &amp; Home Care will review your information.
                            </p>
                            @if(! empty($participant->onboarding_token) && $participant->onboarding_expires_at >= now())
                                <a href="{{ route('participant.onboarding.show', ['token' => $participant->onboarding_token]) }}" class="btn btn-primary">
                                    Continue Onboarding
                                </a>
                            @else
                                <span class="badge bg-secondary">Onboarding link expired</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($submission)
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom">
                        <h3 class="h5 mb-0">Latest Submission</h3>
                    </div>
                    <div class="card-body p-4">
                        <dl class="row">
                            <dt class="col-sm-4 text-muted">Submitted</dt>
                            <dd class="col-sm-8">{{ optional($submission->submitted_at)->format('d M Y H:i') }}</dd>

                            <dt class="col-sm-4 text-muted">Status</dt>
                            <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</dd>

                            @if($submission->admin_comments)
                                <dt class="col-sm-4 text-muted">Admin Comments</dt>
                                <dd class="col-sm-8">{{ $submission->admin_comments }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
