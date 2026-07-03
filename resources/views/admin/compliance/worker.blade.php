@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="mb-1">Worker Compliance: {{ $worker->first_name }} {{ $worker->last_name }}</h3>
            <p class="text-muted mb-0">Worker #{{ $worker->worker_number }} • {{ $worker->email ?? 'No email set' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.admin.workers') }}" class="btn btn-sm btn-outline-secondary">All Workers</a>
            <a href="{{ route('portal.admin.compliance.dashboard') }}" class="btn btn-sm btn-outline-primary">Compliance Dashboard</a>
            <form method="POST" action="{{ route('portal.admin.compliance.workers.initialize', $worker) }}" class="d-inline-block">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning">Initialize Documents</button>
            </form>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card p-4">
                <h5 class="mb-3">Worker Details</h5>
                <p class="mb-2"><strong>Status:</strong> {{ ucfirst($worker->status) }}</p>
                <p class="mb-2"><strong>Compliance expiry:</strong> {{ optional($worker->compliance_expiry_at)->format('Y-m-d') ?? 'Not set' }}</p>
                <p class="mb-2"><strong>Background check:</strong> {{ optional($worker->background_check_expiry_at)->format('Y-m-d') ?? 'Not set' }}</p>
                <p class="mb-2"><strong>Suspended at:</strong> {{ optional($worker->compliance_suspended_at)->format('Y-m-d H:i') ?? 'None' }}</p>
                <p class="mb-2"><strong>Suspension reason:</strong> {{ $worker->compliance_suspension_reason ?? 'None' }}</p>
            </div>

            <div class="card p-4">
                <h5 class="mb-3">Compliance Summary</h5>
                <div class="mb-2"><strong>Documents total:</strong> {{ $details['total_documents'] }}</div>
                <div class="mb-2"><strong>Active:</strong> {{ $details['active'] }}</div>
                <div class="mb-2"><strong>Expiring soon:</strong> {{ $details['expiring_soon'] }}</div>
                <div class="mb-2"><strong>Expired:</strong> {{ $details['expired'] }}</div>
                <div class="mb-2"><strong>Missing:</strong> {{ $details['missing'] }}</div>
                <div class="mb-2"><strong>Rejected:</strong> {{ $details['rejected'] }}</div>
                <div class="mb-2">
                    <strong>Assignable:</strong>
                    <span class="badge bg-{{ $details['can_be_assigned'] ? 'success' : 'danger' }}">
                        {{ $details['can_be_assigned'] ? 'Yes' : 'No' }}
                    </span>
                </div>
                @if(! $details['can_be_assigned'])
                    <div class="text-muted small">{{ $details['blocking_reason'] }}</div>
                @endif
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Compliance Documents</h5>
                    <span class="badge bg-secondary">{{ $details['total_documents'] }} records</span>
                </div>

                @if($worker->complianceDocuments->isEmpty())
                    <div class="alert alert-warning mb-0">
                        No compliance documents have been created for this worker yet. Click "Initialize Documents" to create required compliance placeholders.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Document</th>
                                    <th>Status</th>
                                    <th>Issue Date</th>
                                    <th>Expiry Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($worker->complianceDocuments as $doc)
                                    <tr>
                                        <td>{{ $doc->getTypeLabel() }}</td>
                                        <td>
                                            <span class="badge bg-{{ $doc->status === 'Active' ? 'success' : ($doc->status === 'Expired' ? 'danger' : ($doc->status === 'Expiring Soon' ? 'warning text-dark' : ($doc->status === 'Rejected' ? 'dark' : 'secondary')) ) }}">
                                                {{ $doc->status }}
                                            </span>
                                        </td>
                                        <td>{{ optional($doc->issue_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td>{{ optional($doc->expiry_date)->format('Y-m-d') ?? '—' }}</td>
                                        <td>
                                            <a href="{{ route('portal.admin.compliance.documents.show', $doc) }}" class="btn btn-sm btn-outline-primary">View</a>
                                            @if($doc->document_path)
                                                <a href="{{ route('portal.admin.compliance.documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Compliance Alerts</h5>
                    <span class="badge bg-info">{{ $alerts->count() }} recent</span>
                </div>

                @if($alerts->isEmpty())
                    <div class="text-muted">No alerts have been recorded for this worker.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($alerts as $alert)
                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-semibold">{{ ucfirst(str_replace('_', ' ', $alert->alert_type)) }}</div>
                                    <div class="small text-muted">{{ $alert->message }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-{{ $alert->alert_level === 'critical' ? 'danger' : ($alert->alert_level === 'high' ? 'warning text-dark' : 'secondary') }} mb-1">{{ ucfirst($alert->alert_level) }}</span>
                                    <div class="small text-muted">{{ optional($alert->sent_at)->diffForHumans() }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
