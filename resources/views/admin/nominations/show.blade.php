@extends('layouts.admin')

@section('title', 'Nomination Details')

@section('content')
    <div class="mb-4">
        <a href="{{ route('portal.admin.nominations.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
            <i class="bi bi-arrow-left"></i> Back to Nominations
        </a>
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2>{{ $nomination->worker_full_name }}</h2>
                <p class="text-muted">{{ ucfirst($nomination->worker_type) }} • {{ $nomination->service_type }}</p>
            </div>
            <span class="badge bg-{{ $nomination->status->badge() }} fs-6">
                {{ $nomination->status->label() }}
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Worker Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Worker Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Full Name:</dt>
                                <dd class="col-sm-7">{{ $nomination->worker_full_name }}</dd>

                                <dt class="col-sm-5">Type:</dt>
                                <dd class="col-sm-7">{{ ucfirst($nomination->worker_type) }}</dd>

                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">
                                    <a href="mailto:{{ $nomination->worker_email }}">{{ $nomination->worker_email }}</a>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Phone:</dt>
                                <dd class="col-sm-7">{{ $nomination->worker_phone }}</dd>

                                @if($nomination->worker_address)
                                    <dt class="col-sm-5">Address:</dt>
                                    <dd class="col-sm-7">{{ $nomination->worker_address }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Service Details -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Service Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-5">Service:</dt>
                                <dd class="col-sm-7">{{ $nomination->service_type }}</dd>

                                @if($nomination->estimated_hours)
                                    <dt class="col-sm-5">Est. Hours:</dt>
                                    <dd class="col-sm-7">{{ number_format($nomination->estimated_hours, 1) }} hrs/week</dd>
                                @endif
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                @if($nomination->estimated_cost)
                                    <dt class="col-sm-5">Est. Cost:</dt>
                                    <dd class="col-sm-7">${{ number_format($nomination->estimated_cost, 2) }}/week</dd>
                                @endif

                                @if($nomination->start_date)
                                    <dt class="col-sm-5">Start Date:</dt>
                                    <dd class="col-sm-7">{{ $nomination->start_date->format('d M Y') }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Participant Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Participant Information</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">
                            <strong>{{ $nomination->participant->first_name }} {{ $nomination->participant->last_name }}</strong>
                        </dd>

                        <dt class="col-sm-3">Participant #:</dt>
                        <dd class="col-sm-9">{{ $nomination->participant->participant_number }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-{{ $nomination->participant->status === 'active' ? 'success' : 'warning' }}">
                                {{ ucfirst($nomination->participant->status) }}
                            </span>
                        </dd>

                        @if($nomination->participant->phone)
                            <dt class="col-sm-3">Phone:</dt>
                            <dd class="col-sm-9">{{ $nomination->participant->phone }}</dd>
                        @endif

                        @if($nomination->participant->email)
                            <dt class="col-sm-3">Email:</dt>
                            <dd class="col-sm-9">
                                <a href="mailto:{{ $nomination->participant->email }}">{{ $nomination->participant->email }}</a>
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Notes from Participant -->
            @if($nomination->notes)
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Participant Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $nomination->notes }}</p>
                    </div>
                </div>
            @endif

            <!-- Documents -->
            @if($nomination->uploaded_documents && count($nomination->uploaded_documents) > 0)
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Uploaded Documents</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            @foreach($nomination->uploaded_documents as $doc)
                                <div class="list-group-item">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-file-earmark me-2"></i>
                                        <div>
                                            <h6 class="mb-0">{{ $doc['name'] ?? 'Document' }}</h6>
                                            <small class="text-muted">
                                                Uploaded {{ \Carbon\Carbon::parse($doc['uploaded_at'])->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status & Actions -->
            <div class="card mb-4 sticky-top" style="top: 20px;">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @if($nomination->canBeApproved())
                        <form method="POST" action="{{ route('portal.admin.nominations.approve', $nomination) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="bi bi-check-circle"></i> Approve Nomination
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="bi bi-x-circle"></i> Reject Nomination
                        </button>
                    @elseif($nomination->canSendInvitation())
                        <form method="POST" action="{{ route('portal.admin.nominations.invite_worker', $nomination) }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-info w-100">
                                <i class="bi bi-send"></i> Send Worker Invitation
                            </button>
                        </form>
                    @elseif(in_array($nomination->status, [\App\Enums\WorkerNominationStatus::WorkerInvited, \App\Enums\WorkerNominationStatus::CompliancePending, \App\Enums\WorkerNominationStatus::PendingSignature, \App\Enums\WorkerNominationStatus::Approved]))
                        <div class="d-flex gap-2">
                            <form method="POST" action="{{ route('portal.admin.nominations.activate', $nomination) }}" class="flex-fill">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-person-check"></i> Activate Worker
                                </button>
                            </form>

                            <div class="flex-fill">
                                <button type="button" class="btn btn-secondary w-100" data-bs-toggle="modal" data-bs-target="#confirmResendModal">
                                    <i class="bi bi-envelope"></i> Resend Invitation
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Admin Notes</h5>
                </div>
                <div class="card-body">
                    @if($nomination->ahhc_admin_notes)
                        <div class="alert alert-info">
                            <p class="mb-0">{{ $nomination->ahhc_admin_notes }}</p>
                        </div>
                    @else
                        <p class="text-muted mb-0">No admin notes yet.</p>
                    @endif
                </div>
            </div>

            @if($nomination->rejection_reason)
                <div class="card mt-3">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Rejection Reason</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $nomination->rejection_reason }}</p>
                    </div>
                </div>
            @endif

            <!-- Timeline -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Timeline</h5>
                </div>
                <div class="card-body">
                    <small class="text-muted d-block mb-2">
                        <strong>Created:</strong> {{ $nomination->created_at->format('d M Y H:i') }}
                    </small>

                    @if($nomination->approved_at && $nomination->approvedBy)
                        <small class="text-muted d-block mb-2">
                            <strong>Approved by:</strong> {{ $nomination->approvedBy->name }} on {{ $nomination->approved_at->format('d M Y H:i') }}
                        </small>
                    @endif

                    @if($nomination->rejected_at && $nomination->rejectedBy)
                        <small class="text-muted d-block mb-2">
                            <strong>Rejected by:</strong> {{ $nomination->rejectedBy->name }} on {{ $nomination->rejected_at->format('d M Y H:i') }}
                        </small>
                    @endif

                    @if($nomination->invited_at && $nomination->invitedBy)
                        <small class="text-muted d-block">
                            <strong>Invited by:</strong> {{ $nomination->invitedBy->name }} on {{ $nomination->invited_at->format('d M Y H:i') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Nomination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                
                <form method="POST" action="{{ route('portal.admin.nominations.reject', $nomination) }}">
                    @csrf
                    <div class="modal-body">
                        <p class="mb-3">You are about to reject the nomination for <strong>{{ $nomination->worker_full_name }}</strong>.</p>
                        <div class="form-group">
                            <label for="rejection_reason" class="form-label">Rejection Reason</label>
                            <textarea 
                                class="form-control" 
                                id="rejection_reason" 
                                name="rejection_reason" 
                                rows="4"
                                required
                                placeholder="Explain the reason for rejection. This will be visible to the participant..."></textarea>
                            <small class="text-muted">Be professional and constructive in your feedback.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Nomination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<!-- Confirm Resend Modal -->
<div class="modal fade" id="confirmResendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resend Worker Invitation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to resend the onboarding invitation to <strong>{{ $nomination->worker_email }}</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="resendInvitationForm" method="POST" action="{{ route('portal.admin.nominations.resend_invitation', $nomination) }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">Yes, Resend</button>
                </form>
            </div>
        </div>
    </div>
</div>

    <style>
        /* Mobile responsive styles for admin nominations show */
        @media (max-width: 374px) {
            /* Header layout */
            .d-flex.justify-content-between.align-items-start {
                flex-direction: column;
                gap: 1rem;
            }

            .badge.fs-6 {
                align-self: flex-start;
            }

            h2 {
                font-size: 1.4rem;
                margin-bottom: 0.35rem;
            }

            p.text-muted {
                font-size: 0.9rem;
            }

            /* Layout - stack columns on mobile */
            .row.g-4 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }

            .col-lg-8, .col-lg-4 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            /* Card styling */
            .card {
                border-radius: 14px;
            }

            .card-header {
                padding: 0.75rem;
            }

            .card-header h5 {
                font-size: 0.95rem;
            }

            .card-body {
                padding: 0.75rem;
            }

            /* Definition list adjustments */
            .row.g-3 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }

            .col-md-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            dl.row {
                --bs-gutter-x: 0.5rem;
            }

            dl.row dt {
                font-size: 0.85rem;
                font-weight: 600;
            }

            dl.row dd {
                font-size: 0.85rem;
            }

            /* Button sizing */
            .btn {
                font-size: 0.9rem;
                padding: 0.6rem 1rem;
                min-height: 42px;
            }

            .btn-sm {
                font-size: 0.8rem;
                padding: 0.4rem 0.7rem;
            }

            .w-100 {
                width: 100%;
            }

            /* Alert sizing */
            .alert {
                padding: 0.65rem 0.85rem;
                font-size: 0.9rem;
            }

            /* Badge sizing */
            .badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.55rem;
            }

            /* Sticky positioning on mobile */
            .sticky-top {
                position: static;
                top: auto;
            }

            /* Modal adjustments */
            .modal-dialog {
                margin: 0.5rem;
                max-width: calc(100vw - 1rem);
            }

            .modal-header {
                padding: 0.75rem;
            }

            .modal-body {
                padding: 0.75rem;
            }

            .modal-footer {
                padding: 0.75rem;
                gap: 0.5rem;
            }

            /* List items */
            .list-group-item {
                padding: 0.75rem;
                border-radius: 12px;
                margin-bottom: 0.5rem;
            }

            .list-group-item h6 {
                font-size: 0.9rem;
            }

            .list-group-item i {
                font-size: 1rem;
            }

            /* Back button */
            .btn-outline-secondary.btn-sm {
                font-size: 0.8rem;
                padding: 0.4rem 0.7rem;
            }
        }

        @media (max-width: 575px) and (min-width: 375px) {
            .col-lg-8, .col-lg-4 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .col-md-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .sticky-top {
                position: static;
                top: auto;
            }
        }

        /* Tablet and up */
        @media (max-width: 991px) {
            .col-lg-8, .col-lg-4 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .sticky-top {
                position: static;
            }
        }

        /* Modal on small screens */
        @media (max-width: 576px) {
            .modal-dialog {
                max-height: 90vh;
            }

            .modal-body {
                max-height: calc(90vh - 120px);
                overflow-y: auto;
            }

            textarea {
                font-size: 16px; /* Prevent iOS zoom */
            }
        }

        /* Touch targets */
        @media (hover: none) and (pointer: coarse) {
            button, a.btn, .btn {
                min-height: 44px;
                min-width: 44px;
            }

            .list-group-item {
                padding: 1rem 0.75rem;
            }
        }

        /* Prevent overflow */
        .card {
            overflow-x: hidden;
        }
    </style>
@endsection
