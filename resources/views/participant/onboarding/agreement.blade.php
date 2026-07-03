@extends('layouts.auth')

@php
    $showRouteName = $showRouteName ?? 'participant.onboarding.agreement.show';
    $downloadRouteName = $downloadRouteName ?? 'participant.onboarding.agreement.download';
    $backRouteName = $backRouteName ?? 'participant.onboarding.show';
@endphp

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">{{ $agreement->title }}</h2>
                            <p class="mb-0 mt-2 small">Review and download your agreement</p>
                        </div>
                        <div>
                            <a href="{{ route('participant.onboarding.show', ['token' => $token]) }}" class="btn btn-outline-light">
                                Back to onboarding
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if($agreement->description)
                        <p class="text-muted mb-3">{{ $agreement->description }}</p>
                    @endif

                    <div class="mb-4">
                        <a href="{{ route('participant.onboarding.agreement.download', ['token' => $token, 'agreement' => $agreement]) }}" class="btn btn-primary me-2">
                            Download agreement
                        </a>
                        <a href="{{ route('participant.onboarding.show', ['token' => $token]) }}" class="btn btn-outline-secondary">
                            Return to onboarding
                        </a>
                    </div>

                    <div class="border rounded p-4 bg-light" style="white-space: pre-wrap; font-size: 0.95rem;">
                        {!! nl2br(e($agreement->content)) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
