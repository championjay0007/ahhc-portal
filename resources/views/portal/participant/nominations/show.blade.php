@extends('layouts.portal')

@section('title', 'Nomination Details')

@section('content')
    <div class="mb-4">
        <a href="{{ route('portal.participant.nominations.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
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

    <!-- Status Timeline -->
    <div class="card portal-card p-4 mb-4">
        <h5 class="mb-3">Status Timeline</h5>
        <div class="timeline">
            @php
                $statuses = [
                    'Submitted',
                    'Under Review',
                    'Approved',
                    'Rejected',
                    'Worker Invited',
                    'Compliance Pending',
                    'Pending Signature',
                    'Active',
                    'Assigned',
                ];
                $currentStatusIndex = array_search($nomination->status->value, $statuses);
            @endphp

            @foreach($statuses as $index => $status)
                <div class="timeline-item">
                    <div class="timeline-marker @if($index <= $currentStatusIndex) completed @endif">
                        @if($index <= $currentStatusIndex)
                            <i class="bi bi-check"></i>
                        @else
                            <i class="bi bi-circle"></i>
                        @endif
                    </div>
                    <div class="timeline-content">
                        <h6 class="mb-0">{{ $status }}</h6>
                        @if($status === 'Submitted' && $nomination->created_at)
                            <small class="text-muted">{{ $nomination->created_at->format('d M Y \a\t H:i') }}</small>
                        @elseif($status === 'Approved' && $nomination->approved_at)
                            <small class="text-muted">{{ $nomination->approved_at->format('d M Y \a\t H:i') }}</small>
                        @elseif($status === 'Rejected' && $nomination->rejected_at)
                            <small class="text-muted">{{ $nomination->rejected_at->format('d M Y \a\t H:i') }}</small>
                        @elseif($status === 'Worker Invited' && $nomination->invited_at)
                            <small class="text-muted">{{ $nomination->invited_at->format('d M Y \a\t H:i') }}</small>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Worker Details -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card portal-card p-4">
                <h5 class="mb-3">Worker Information</h5>
                <dl class="row">
                    <dt class="col-sm-4">Full Name:</dt>
                    <dd class="col-sm-8">{{ $nomination->worker_full_name }}</dd>

                    <dt class="col-sm-4">Type:</dt>
                    <dd class="col-sm-8">{{ ucfirst($nomination->worker_type) }}</dd>

                    <dt class="col-sm-4">Email:</dt>
                    <dd class="col-sm-8">
                        <a href="mailto:{{ $nomination->worker_email }}">{{ $nomination->worker_email }}</a>
                    </dd>

                    <dt class="col-sm-4">Phone:</dt>
                    <dd class="col-sm-8">{{ $nomination->worker_phone }}</dd>

                    @if($nomination->worker_address)
                        <dt class="col-sm-4">Address:</dt>
                        <dd class="col-sm-8">{{ $nomination->worker_address }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card portal-card p-4">
                <h5 class="mb-3">Service Details</h5>
                <dl class="row">
                    <dt class="col-sm-4">Service Type:</dt>
                    <dd class="col-sm-8">{{ $nomination->service_type }}</dd>

                    @if($nomination->estimated_hours)
                        <dt class="col-sm-4">Est. Hours:</dt>
                        <dd class="col-sm-8">{{ number_format($nomination->estimated_hours, 1) }} hours/week</dd>
                    @endif

                    @if($nomination->estimated_cost)
                        <dt class="col-sm-4">Est. Cost:</dt>
                        <dd class="col-sm-8">${{ number_format($nomination->estimated_cost, 2) }}/week</dd>
                    @endif

                    @if($nomination->start_date)
                        <dt class="col-sm-4">Start Date:</dt>
                        <dd class="col-sm-8">{{ $nomination->start_date->format('d M Y') }}</dd>
                    @endif

                    <dt class="col-sm-4">Submitted:</dt>
                    <dd class="col-sm-8">{{ $nomination->created_at->format('d M Y') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- Notes & Comments -->
    @if($nomination->notes || $nomination->ahhc_admin_notes || $nomination->rejection_reason)
        <div class="card portal-card p-4 mb-4">
            <h5 class="mb-3">Notes & Feedback</h5>

            @if($nomination->notes)
                <div class="mb-3">
                    <h6>Your Notes:</h6>
                    <p class="text-muted">{{ $nomination->notes }}</p>
                </div>
            @endif

            @if($nomination->ahhc_admin_notes)
                <div class="mb-3 alert alert-info">
                    <h6>Allegiance Heart &amp; Home Care Comments:</h6>
                    <p class="mb-0">{{ $nomination->ahhc_admin_notes }}</p>
                </div>
            @endif

            @if($nomination->rejection_reason)
                <div class="alert alert-danger">
                    <h6>Rejection Reason:</h6>
                    <p class="mb-0">{{ $nomination->rejection_reason }}</p>
                </div>
            @endif
        </div>
    @endif

    <!-- Uploaded Documents -->
    @if($nomination->uploaded_documents && count($nomination->uploaded_documents) > 0)
        <div class="card portal-card p-4 mb-4">
            <h5 class="mb-3">Supporting Documents</h5>
            <div class="list-group">
                @foreach($nomination->uploaded_documents as $doc)
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">
                                <i class="bi bi-file-earmark"></i> {{ $doc['name'] ?? 'Document' }}
                            </h6>
                            <small class="text-muted">
                                Uploaded {{ \Carbon\Carbon::parse($doc['uploaded_at'])->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Actions -->
    <div class="d-flex gap-2">
        <a href="{{ route('portal.participant.nominations.index') }}" class="btn btn-outline-secondary">
            Back to Nominations
        </a>
        @if($nomination->status->value === 'Submitted' || $nomination->status->value === 'Rejected')
            <form method="POST" action="{{ route('portal.participant.nominations.destroy', $nomination) }}" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this nomination?')">
                    Delete Nomination
                </button>
            </form>
        @endif
    </div>

    <style>
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 20px;
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 15px;
            top: 40px;
            width: 2px;
            height: calc(100% + 20px);
            background: #e9ecef;
        }

        .timeline-marker {
            width: 32px;
            height: 32px;
            background: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 15px;
            position: relative;
            z-index: 1;
            color: #6c757d;
            font-weight: bold;
        }

        .timeline-marker.completed {
            background: #198754;
            color: white;
        }

        .timeline-content {
            flex-grow: 1;
            padding-top: 5px;
        }

        /* ========================================
           MOBILE RESPONSIVE STYLES
           ======================================== */

        @media (max-width: 374px) {
            /* Header layout */
            .d-flex.justify-content-between.align-items-start {
                flex-direction: column;
                gap: 1rem;
            }

            .badge.fs-6 {
                align-self: flex-start;
                font-size: 0.9rem !important;
            }

            h2 {
                font-size: 1.4rem;
                margin-bottom: 0.35rem;
            }

            /* Timeline adjustments */
            .timeline {
                padding: 1rem 0;
            }

            .timeline-item {
                margin-bottom: 1.25rem;
            }

            .timeline-marker {
                width: 28px;
                height: 28px;
                font-size: 0.75rem;
                margin-right: 10px;
            }

            .timeline-marker i {
                font-size: 0.85rem;
            }

            .timeline-content h6 {
                font-size: 0.9rem;
            }

            .timeline-content small {
                font-size: 0.75rem;
            }

            .timeline-item:not(:last-child)::after {
                left: 13px;
            }

            /* Card layout */
            .card.portal-card {
                padding: 1rem !important;
                border-radius: 14px;
            }

            .card.p-4 {
                padding: 1rem !important;
            }

            h5 {
                font-size: 0.95rem;
                margin-bottom: 1rem;
            }

            /* Two-column to single column */
            .row.g-4 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }

            .col-md-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            /* Definition list */
            dl.row {
                --bs-gutter-x: 0.5rem;
                row-gap: 0.75rem;
            }

            dl.row dt {
                font-size: 0.8rem;
                font-weight: 600;
                color: #495057;
            }

            dl.row dd {
                font-size: 0.85rem;
                margin-bottom: 0;
            }

            /* Buttons */
            .d-flex.gap-2 {
                flex-wrap: wrap;
                gap: 0.5rem !important;
            }

            .d-flex.gap-2 > * {
                flex-basis: 100%;
                min-width: 0;
            }

            .btn {
                font-size: 0.9rem;
                padding: 0.6rem 1rem;
                min-height: 42px;
            }

            .btn-sm {
                font-size: 0.8rem;
                padding: 0.4rem 0.7rem;
                min-height: 36px;
            }

            .btn-outline-secondary.btn-sm {
                min-width: auto;
            }

            /* List items */
            .list-group-item {
                padding: 0.75rem;
                border-radius: 12px;
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
            }

            .list-group-item h6 {
                font-size: 0.85rem;
                margin-bottom: 0.25rem;
            }

            .list-group-item i {
                margin-right: 0.5rem;
                font-size: 0.95rem;
            }

            /* Badge sizing */
            .badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.55rem;
            }

            /* Rejection reason and admin notes */
            .alert {
                padding: 0.75rem;
                font-size: 0.85rem;
                margin-bottom: 1rem;
            }

            p.text-muted {
                font-size: 0.85rem;
            }

            /* Ensure no horizontal overflow */
            .card, .card-body {
                overflow-x: hidden;
            }
        }

        @media (max-width: 575px) and (min-width: 375px) {
            .col-md-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .row.g-4 {
                --bs-gutter-x: 1rem;
                --bs-gutter-y: 1rem;
            }

            .d-flex.gap-2 {
                flex-wrap: wrap;
            }

            .d-flex.gap-2 > * {
                flex-basis: 48%;
            }
        }

        /* Tablets and up - ensure proper card behavior */
        @media (max-width: 767px) {
            .col-md-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            /* Ensure timeline is scrollable if needed */
            .timeline {
                -webkit-overflow-scrolling: touch;
            }
        }

        /* Touch-friendly targets */
        @media (hover: none) and (pointer: coarse) {
            button, a.btn, .btn {
                min-height: 44px;
                min-width: 44px;
            }

            .list-group-item {
                padding: 1rem 0.75rem;
            }

            .timeline-content {
                cursor: pointer;
            }
        }

        /* Landscape mode */
        @media (max-height: 600px) and (orientation: landscape) {
            .timeline {
                padding: 1rem 0;
            }

            .timeline-item {
                margin-bottom: 0.75rem;
            }

            .card.portal-card {
                padding: 0.75rem !important;
            }
        }

        /* Prevent overflow on all screens */
        * {
            max-width: 100%;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
@endsection
