@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Worker Onboarding - Stage 1: Create Account</h4>
                </div>
                <div class="card-body p-5">
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading">Welcome to the Allegiance Heart &amp; Home Care Portal!</h6>
                        <p class="mb-2">You've been invited to join as a worker. To get started, please create your account below.</p>
                        <small class="text-muted">Your invitation expires on <strong>{{ $worker->onboarding_expires_at->format('M d, Y') }}</strong></small>
                    </div>

                    <form method="POST" action="{{ route('worker.onboarding.stage1.submit', ['token' => $token]) }}">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="{{ $worker->first_name }}" disabled>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="{{ $worker->last_name }}" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $worker->email }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" value="{{ $worker->phone }}" disabled>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3">Create Your Password</h6>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                Password must be at least 8 characters and include uppercase, lowercase, numbers, and symbols.
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Create Account & Continue</button>
                    </form>

                    <div class="alert alert-warning mt-4">
                        <small>
                            <strong>Next Steps:</strong> After creating your account, you'll be asked to set up two-factor authentication (2FA) for security.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
