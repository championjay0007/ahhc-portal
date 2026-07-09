<div class="nomination-detail-modal-content">
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2 class="mb-1">{{ $nomination->worker_full_name }}</h2>
                <p class="text-muted mb-0">{{ ucfirst($nomination->worker_type) }} • {{ $nomination->service_type }}</p>
            </div>
            <span class="badge bg-{{ $nomination->status->badge() }} fs-6">
                {{ $nomination->status->label() }}
            </span>
        </div>
    </div>

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
                                Uploaded {{ \Illuminate\Support\Carbon::parse($doc['uploaded_at'])->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
