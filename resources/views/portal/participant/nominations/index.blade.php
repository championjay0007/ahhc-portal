@extends('layouts.portal')

@section('title', 'Nominate Workers')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Nominate Workers / Suppliers</h2>
            <p class="text-muted">Manage your worker and supplier nominations. Allegiance Heart &amp; Home Care will review and approve before workers can access the portal.</p>
        </div>
        <div>
            <a href="{{ route('portal.participant.nominations.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nominate Worker
            </a>
        </div>
    </div>

    @if($nominations->isEmpty())
        <div class="card portal-card p-5 text-center">
            <div class="mb-3">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            </div>
            <h5>No nominations yet</h5>
            <p class="text-muted mb-4">Start by nominating a worker or supplier to join your care team.</p>
            <a href="{{ route('portal.participant.nominations.create') }}" class="btn btn-primary">
                Nominate Your First Worker
            </a>
        </div>
    @else
        <div class="row g-4">
            @foreach($nominations as $nomination)
                <div class="col-md-6">
                    <div class="card portal-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-0">{{ $nomination->worker_full_name }}</h5>
                                    <small class="text-muted">{{ ucfirst($nomination->worker_type) }}</small>
                                </div>
                                <span class="badge bg-{{ $nomination->status->badge() }}">
                                    {{ $nomination->status->label() }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <p class="mb-1">
                                    <strong>Service Type:</strong> {{ $nomination->service_type }}
                                </p>
                                <p class="mb-1">
                                    <strong>Email:</strong> <a href="mailto:{{ $nomination->worker_email }}">{{ $nomination->worker_email }}</a>
                                </p>
                                <p class="mb-0">
                                    <strong>Phone:</strong> {{ $nomination->worker_phone }}
                                </p>
                            </div>

                            @if($nomination->start_date)
                                <p class="mb-2">
                                    <strong>Proposed Start:</strong> {{ $nomination->start_date->format('d M Y') }}
                                </p>
                            @endif

                            @if($nomination->estimated_hours)
                                <p class="mb-2">
                                    <strong>Estimated Hours:</strong> {{ number_format($nomination->estimated_hours, 1) }} hours/week
                                </p>
                            @endif

                            @if($nomination->estimated_cost)
                                <p class="mb-2">
                                    <strong>Estimated Cost:</strong> ${{ number_format($nomination->estimated_cost, 2) }}
                                </p>
                            @endif

                            <!-- Status-specific information -->
                            @if($nomination->isPending())
                                <div class="alert alert-info alert-sm mb-2">
                                    <small>
                                        @if($nomination->status->value === 'Submitted')
                                            Submitted on {{ $nomination->created_at->format('d M Y') }}. Allegiance Heart &amp; Home Care is reviewing your nomination.
                                        @elseif($nomination->status->value === 'Under Review')
                                            Your nomination is under review by Allegiance Heart &amp; Home Care.
                                        @elseif($nomination->status->value === 'Approved')
                                            ✓ Nomination approved! Awaiting worker invitation.
                                        @elseif($nomination->status->value === 'Worker Invited')
                                            Worker has been invited to complete registration.
                                        @elseif($nomination->status->value === 'Compliance Pending')
                                            Worker is completing compliance requirements.
                                        @elseif($nomination->status->value === 'Pending Signature')
                                            Awaiting worker agreement signatures.
                                        @endif
                                    </small>
                                </div>
                            @endif

                            @if($nomination->isRejected() && $nomination->rejection_reason)
                                <div class="alert alert-danger alert-sm mb-2">
                                    <small>
                                        <strong>Rejection Reason:</strong> {{ $nomination->rejection_reason }}
                                    </small>
                                </div>
                            @endif

                            @if($nomination->ahhc_admin_notes)
                                <div class="alert alert-warning alert-sm mb-2">
                                    <small>
                                        <strong>Allegiance Heart &amp; Home Care Notes:</strong> {{ $nomination->ahhc_admin_notes }}
                                    </small>
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-4">
                                <button type="button" class="btn btn-sm btn-outline-primary flex-grow-1 load-nomination-detail-btn"
                                        data-detail-url="{{ route('portal.participant.nominations.show', $nomination) }}">
                                    View Details
                                </button>
                                @if($nomination->status->value === 'Submitted' || $nomination->status->value === 'Rejected')
                                    <form method="POST" action="{{ route('portal.participant.nominations.destroy', $nomination) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this nomination?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Submitted {{ $nomination->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $nominations->links() }}
        </div>
    @endif

    <style>
        /* Mobile responsive adjustments for nominations index */
        @media (max-width: 374px) {
            .d-flex.justify-content-between {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .d-flex.justify-content-between .btn {
                width: 100%;
            }

            .col-md-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .card.h-100 {
                min-height: auto;
            }

            .card-body {
                padding: 1rem;
            }

            .card-footer {
                padding: 0.75rem;
                font-size: 0.85rem;
            }

            .d-flex.gap-2 {
                flex-wrap: wrap;
            }

            .d-flex.gap-2 .btn {
                flex-basis: 100%;
            }

            .alert-sm {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
                margin-bottom: 0.5rem;
            }

            h5.card-title {
                font-size: 1rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.55rem;
            }
        }

        @media (max-width: 575px) {
            .col-md-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .row.g-4 {
                --bs-gutter-x: 1rem;
                --bs-gutter-y: 1rem;
            }
        }

        /* Ensure buttons don't overflow on small screens */
        .d-flex.gap-2 {
            flex-wrap: wrap;
        }

        .btn-sm {
            font-size: 0.85rem;
        }

        /* Alert sizing */
        .alert-sm {
            padding: 0.65rem 0.85rem;
            margin-bottom: 0.75rem;
        }

        .alert-sm strong {
            display: inline;
        }
    </style>

    <!-- Nomination Detail Modal -->
    <div class="modal fade" id="nominationDetailModal" tabindex="-1" aria-labelledby="nominationDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nominationDetailModalLabel">Nomination Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="nominationDetailModalBody">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 mb-0">Loading nomination details…</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('nominationDetailModal');
            const modalBody = document.getElementById('nominationDetailModalBody');
            const nominationModal = modalElement ? new bootstrap.Modal(modalElement, {
                keyboard: true,
                backdrop: 'static'
            }) : null;

            document.querySelectorAll('.load-nomination-detail-btn').forEach(button => {
                button.addEventListener('click', async function () {
                    if (!nominationModal || !modalBody) {
                        return;
                    }

                    const url = button.dataset.detailUrl;
                    if (!url) {
                        return;
                    }

                    modalBody.innerHTML = `
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 mb-0">Loading nomination details…</p>
                        </div>
                    `;
                    nominationModal.show();

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load nomination details.');
                        }

                        modalBody.innerHTML = await response.text();
                    } catch (error) {
                        modalBody.innerHTML = `
                            <div class="alert alert-danger mb-0">
                                Unable to load nomination details at this time. Please try again.
                            </div>
                        `;
                    }
                });
            });
        });
    </script>
@endsection
