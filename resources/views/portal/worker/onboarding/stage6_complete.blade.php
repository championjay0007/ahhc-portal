@extends('layouts.auth')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-success" role="alert">
                <div class="text-center mb-4">
                    <h1 class="display-4 mb-3">✓ Onboarding Complete!</h1>
                    <span class="badge bg-success fs-5">Stage 6 of 6</span>
                </div>

                <h5 class="alert-heading">Welcome to the Allegiance Heart &amp; Home Care Portal!</h5>
                <p>Congratulations! You have successfully completed your worker onboarding. Your account is now fully activated and you can begin providing services.</p>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Your Assigned Participants</h6>
                        </div>
                        <div class="card-body">
                            @if ($assignments->isEmpty())
                                <div class="alert alert-info mb-0">
                                    <p class="mb-0">No participants assigned yet. You'll receive assignments as they become available.</p>
                                </div>
                            @else
                                <div class="list-group">
                                    @foreach ($assignments as $assignment)
                                        <a href="{{ route('portal.worker.participants.show', $assignment->participant) }}" class="list-group-item list-group-item-action">
                                            <div class="d-flex justify-content-between align-items-start flex-wrap">
                                                <div>
                                                    <h6 class="mb-1">{{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}</h6>
                                                    <small class="text-muted">
                                                        Assigned: {{ $assignment->start_date->format('M d, Y') }}
                                                    </small>
                                                </div>
                                                <span class="badge bg-success mt-2 mt-md-0">Active</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Getting Started</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h6>1. Review Your Dashboard</h6>
                                <p class="text-muted">Visit your dashboard to see upcoming shifts and assignments.</p>
                            </div>
                            <div class="mb-3">
                                <h6>2. Check Your Shifts</h6>
                                <p class="text-muted">View your scheduled shifts and confirm your availability.</p>
                            </div>
                            <div class="mb-3">
                                <h6>3. Submit Care Notes</h6>
                                <p class="text-muted">After each shift, complete a care note documenting the services provided.</p>
                            </div>
                            <div>
                                <h6>4. Report Issues</h6>
                                <p class="text-muted">If any incidents occur, immediately report them through the incident management system.</p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2 flex-wrap onboarding-action-buttons">
                        <a href="{{ route('portal.worker.dashboard') }}" class="btn btn-primary btn-lg flex-fill">Go to Dashboard</a>
                        <a href="{{ route('portal.worker.assigned_participants') }}" class="btn btn-outline-secondary btn-lg flex-fill">View Participants</a>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Important Reminders</h6>
                        </div>
                        <div class="card-body small">
                            <ul class="mb-0">
                                <li><strong>2FA Enabled:</strong> Your account is protected with two-factor authentication</li>
                                <li><strong>Privacy:</strong> You can only see assigned participant information</li>
                                <li><strong>Confidentiality:</strong> Never share participant details</li>
                                <li><strong>Care Notes:</strong> Mandatory for all service delivery</li>
                                <li><strong>Support:</strong> Contact Allegiance Heart &amp; Home Care for any questions</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Your Profile</h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="mb-2">
                                <strong>{{ $worker->first_name }} {{ $worker->last_name }}</strong>
                            </p>
                            <p class="text-muted mb-2">
                                Worker ID: {{ $worker->worker_number }}
                            </p>
                            <a href="#" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
