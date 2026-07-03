@extends('layouts.admin')

@section('title', 'Worker Nominations')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Worker Nominations</h2>
            <p class="text-muted">Review and manage worker nominations from participants.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="status-filter" class="form-label">Status</label>
                    <select class="form-select" id="status-filter" name="status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="search" class="form-label">Search</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="search" 
                        name="search" 
                        placeholder="Search by worker name or email..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('portal.admin.nominations.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Nominations Table -->
    @if($nominations->isEmpty())
        <div class="card text-center p-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <h5 class="mt-3">No nominations found</h5>
            <p class="text-muted">There are no worker nominations matching your filters.</p>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Worker Name</th>
                            <th>Type</th>
                            <th>Participant</th>
                            <th>Service Type</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nominations as $nomination)
                            <tr>
                                <td>
                                    <strong>{{ $nomination->worker_full_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $nomination->worker_email }}</small>
                                </td>
                                <td>
                                    <small>{{ ucfirst($nomination->worker_type) }}</small>
                                </td>
                                <td>
                                    <strong>{{ $nomination->participant->first_name }} {{ $nomination->participant->last_name }}</strong>
                                    <br>
                                    <small class="text-muted">#{{ $nomination->participant->participant_number }}</small>
                                </td>
                                <td>
                                    {{ $nomination->service_type }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $nomination->status->badge() }}">
                                        {{ $nomination->status->label() }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $nomination->created_at->format('d M Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('portal.admin.nominations.show', $nomination) }}" class="btn btn-outline-primary" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if($nomination->canBeApproved())
                                            <form method="POST" action="{{ route('portal.admin.nominations.approve', $nomination) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Approve" onclick="return confirm('Approve this nomination?')">
                                                    <i class="bi bi-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $nomination->id }}" title="Reject">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        @endif
                                        @if($nomination->canSendInvitation())
                                            <form method="POST" action="{{ route('portal.admin.nominations.invite_worker', $nomination) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info" title="Send Invitation" onclick="return confirm('Send invitation to worker?')">
                                                    <i class="bi bi-send"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            @if($nomination->canBeRejected())
                                <div class="modal fade" id="rejectModal{{ $nomination->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Reject Nomination</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('portal.admin.nominations.reject', $nomination) }}">
                                                @csrf
                                                <div class="modal-body">
                                                    <p class="mb-3">Rejecting nomination for <strong>{{ $nomination->worker_full_name }}</strong></p>
                                                    <div class="form-group">
                                                        <label for="rejection_reason" class="form-label">Rejection Reason</label>
                                                        <textarea 
                                                            class="form-control" 
                                                            id="rejection_reason" 
                                                            name="rejection_reason" 
                                                            rows="3"
                                                            required
                                                            placeholder="Explain why this nomination is being rejected..."></textarea>
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
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $nominations->links() }}
        </div>
    @endif

    <style>
        /* Mobile responsive styles for admin nominations index */
        @media (max-width: 374px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
            }

            /* Filter form - stack on mobile */
            .row.g-3 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }

            .col-md-4, .col-md-5, .col-md-3 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .d-flex.gap-2.align-items-end {
                flex-direction: column;
                align-items: stretch;
            }

            .d-flex.gap-2.align-items-end .btn {
                width: 100%;
            }

            /* Table optimization for mobile */
            .table {
                font-size: 0.85rem;
            }

            .table th, .table td {
                padding: 0.5rem 0.35rem;
            }

            /* Hide columns on extra-small screens */
            .table th:nth-child(3),
            .table td:nth-child(3),
            .table th:nth-child(4),
            .table td:nth-child(4),
            .table th:nth-child(6),
            .table td:nth-child(6) {
                display: none;
            }

            .table th:nth-child(2) {
                min-width: 60px;
            }

            /* Button group - make responsive */
            .btn-group-sm .btn {
                padding: 0.35rem 0.5rem;
                font-size: 0.75rem;
            }

            .btn-group-sm .btn i {
                font-size: 0.9rem;
            }

            /* Badge sizing */
            .badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.55rem;
            }

            /* Modal on small screens */
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

            .modal-footer .btn {
                font-size: 0.85rem;
            }

            /* Text overflow handling */
            .table td small {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            h2 {
                font-size: 1.4rem;
            }

            p.text-muted {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 575px) and (min-width: 375px) {
            .col-md-4, .col-md-5, .col-md-3 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .table th:nth-child(4),
            .table td:nth-child(4),
            .table th:nth-child(6),
            .table td:nth-child(6) {
                display: none;
            }
        }

        /* Tablet and up - ensure table is scrollable if needed */
        @media (max-width: 767px) {
            .table-responsive {
                -webkit-overflow-scrolling: touch;
            }

            .modal-dialog {
                max-height: 90vh;
                margin: auto 0.5rem;
            }

            .modal-body {
                max-height: calc(90vh - 120px);
                overflow-y: auto;
            }
        }

        /* Ensure button groups wrap on mobile */
        @media (max-width: 576px) {
            .btn-group-sm {
                display: flex;
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .btn-group-sm .btn {
                flex: 1 1 auto;
                min-width: 36px;
            }
        }

        /* Touch-friendly sizing */
        @media (hover: none) and (pointer: coarse) {
            .btn, button, a.btn {
                min-height: 44px;
                min-width: 44px;
            }

            .btn-group-sm .btn {
                min-height: 40px;
            }

            .table td {
                padding: 0.75rem 0.5rem;
            }
        }

        /* Prevent horizontal overflow */
        .table-responsive {
            -webkit-overflow-scrolling: touch;
            overflow-x: auto;
        }
    </style>
@endsection
