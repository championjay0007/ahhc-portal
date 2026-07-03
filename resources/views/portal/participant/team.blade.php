@extends('layouts.portal')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-2">Workers & Suppliers</h1>
            <p class="text-muted mb-0">Manage your care team, service providers, and support contacts.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <a href="{{ route('portal.participant.nominations.create') }}" class="btn btn-primary btn-sm align-self-md-start">
                <i class="bi bi-person-plus me-1"></i>Nominate Worker
            </a>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary btn-sm align-self-md-start">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <!-- Primary Support Contact -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card portal-card p-4 border-start border-success">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="bi bi-star-fill text-warning fs-5"></i>
                            <h5 class="mb-0">Primary Support Contact</h5>
                        </div>
                        @if($participant->supportPerson)
                            <div class="mb-3">
                                <strong class="d-block">{{ $participant->supportPerson->name }}</strong>
                                <small class="text-muted">{{ $participant->supportPerson->role ?? 'Support Person' }}</small>
                            </div>
                            <div class="row g-3 small">
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Email</p>
                                    <a href="mailto:{{ $participant->supportPerson->email }}">{{ $participant->supportPerson->email }}</a>
                                </div>
                                <div class="col-md-6">
                                    <p class="text-muted mb-1">Phone</p>
                                    <a href="tel:{{ $participant->supportPerson->phone }}">{{ $participant->supportPerson->phone }}</a>
                                </div>
                            </div>
                        @else
                            <p class="text-muted mb-0">No assigned support person is available right now. Please contact your administrator.</p>
                        @endif
                    </div>
                    @if($participant->supportPerson)
                        <span class="badge bg-success flex-shrink-0">Active</span>
                    @else
                        <span class="badge bg-secondary flex-shrink-0">Inactive</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- My Workers / Staff Details -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="mb-1">My Workers / Staff Details</h5>
                    <small class="text-muted">See approved workers and suppliers, how they were sourced, and whether they are cleared to provide services.</small>
                </div>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <a href="{{ route('portal.participant.nominations.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Nominate Worker
                    </a>
                    <span class="badge bg-primary">{{ $activeAssignments->count() }}</span>
                </div>
            </div>

            @if($activeAssignments->isEmpty())
                <div class="card portal-card p-4 text-center">
                    <i class="bi bi-person-slash text-muted" style="font-size: 2rem; display: block; margin-bottom: 1rem;"></i>
                    <p class="text-muted mb-3">No approved workers currently assigned. Contact your support person to request assignments.</p>
                    <a href="{{ route('portal.participant.nominations.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Nominate a Worker
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Worker / Supplier</th>
                                <th>Source</th>
                                <th>Approved Service</th>
                                <th>Compliance Status</th>
                                <th>Assigned Shifts</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activeAssignments as $assignment)
                                @php
                                    $worker = $assignment->worker;
                                    $type = strtolower(trim((string) $assignment->assignment_type));
                                    $sourceType = 'Participant-sourced';
                                    if (str_contains($type, 'marketplace') || str_contains($type, 'mable')) {
                                        $sourceType = 'Mable / Marketplace';
                                    } elseif (str_contains($type, 'supplier')) {
                                        $sourceType = 'Supplier';
                                    } elseif (str_contains($type, 'third-party') || str_contains($type, 'third party')) {
                                        $sourceType = 'Third-party';
                                    } elseif (in_array($type, ['primary', 'secondary', 'temporary', 'care worker'])) {
                                        $sourceType = 'Allegiance Heart & Home Care assigned';
                                    }
                                    $serviceName = $worker->role_type ?? $worker->qualification ?? 'Care service';
                                    $bgCheckValid = $worker->background_check_expiry_at && $worker->background_check_expiry_at->isFuture();
                                    $complianceValid = $worker->compliance_expiry_at && $worker->compliance_expiry_at->isFuture();
                                    $complianceLabel = $bgCheckValid && $complianceValid ? 'Approved' : ($sourceType === 'Mable / Marketplace' ? 'Agreement uploaded' : 'Needs review');
                                    $shiftText = $assignment->start_date ? $assignment->start_date->format('d M Y') : 'TBD';
                                    if ($assignment->end_date) {
                                        $shiftText .= ' – ' . $assignment->end_date->format('d M Y');
                                    } else {
                                        $shiftText .= ' (ongoing)';
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $worker->first_name }} {{ $worker->last_name }}</strong>
                                        <div class="small text-muted">{{ $assignment->is_primary ? 'Primary' : ucfirst($assignment->assignment_type ?? 'Secondary') }}</div>
                                    </td>
                                    <td>{{ $sourceType }}</td>
                                    <td>{{ $serviceName }}</td>
                                    <td>
                                        <span class="badge {{ $complianceLabel === 'Approved' ? 'bg-success' : ($complianceLabel === 'Agreement uploaded' ? 'bg-info' : 'bg-warning text-dark') }}">
                                            {{ $complianceLabel }}
                                        </span>
                                    </td>
                                    <td>{{ $shiftText }}</td>
                                    <td class="text-end">
                                        <div class="btn-group" role="group" aria-label="Actions">
                                            <a href="mailto:{{ $worker->email }}" class="btn btn-sm btn-outline-secondary">Contact</a>
                                            @if($worker->user_id)
                                                <a href="{{ route('portal.messages.compose', $worker->user_id) }}" class="btn btn-sm btn-primary">Chat</a>
                                            @endif
                                            <a href="{{ route('portal.participant.documents.index') }}" class="btn btn-sm btn-outline-primary">View docs</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-end">
                    <a href="{{ route('portal.participant.complaints.create') }}" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-exclamation-circle me-1"></i>Submit issue
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="card portal-card p-4">
                <h5 class="mb-3">Key Forms & Documents</h5>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded">
                            <h6>Self-Management Agreement</h6>
                            <p class="small text-muted mb-2">Visible in Forms & E-Signatures and Documents & Uploads.</p>
                            <a href="{{ route('portal.participant.documents.index') }}" class="link-primary">Open Documents</a>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded">
                            <h6>Responsibilities Matrix</h6>
                            <p class="small text-muted mb-2">Visible in Forms & E-Signatures / Documents & Uploads.</p>
                            <a href="{{ route('portal.participant.documents.index') }}" class="link-primary">View Uploads</a>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded">
                            <h6>Pre-Approval Form</h6>
                            <p class="small text-muted mb-2">Submit from the Pre-Approvals menu.</p>
                            <a href="{{ route('portal.participant.pre_approvals.index') }}" class="link-primary">Submit Request</a>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded">
                            <h6>Invoice Submission</h6>
                            <p class="small text-muted mb-2">Submit from Invoices & Receipts with attachments.</p>
                            <a href="{{ route('portal.participant.invoices.index') }}" class="link-primary">Submit Invoice</a>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded">
                            <h6>Incident / Complaint Forms</h6>
                            <p class="small text-muted mb-2">Use Incidents, Complaints & Feedback menu to report issues.</p>
                            <a href="{{ route('portal.participant.complaints.create') }}" class="link-primary">Report Incident</a>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 border rounded">
                            <h6>Care Notes</h6>
                            <p class="small text-muted mb-2">Review monthly review status and service notes.</p>
                            <a href="{{ route('portal.participant.care_notes.index') }}" class="link-primary">View Care Notes</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Work History -->
    @if($suppliers->isNotEmpty())
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <h5 class="mb-1">Service Providers & Suppliers</h5>
                        <small class="text-muted">External service providers and suppliers contracted for specific services</small>
                    </div>
                    <span class="badge bg-info">{{ $suppliers->count() }}</span>
                </div>

                <div class="row g-3">
                    @foreach($suppliers as $assignment)
                        @php
                            $worker = $assignment->worker;
                            $assignmentType = strtolower(trim((string) $assignment->assignment_type));
                            $sourceType = 'Supplier';
                            if (str_contains($assignmentType, 'marketplace') || str_contains($assignmentType, 'mable')) {
                                $sourceType = 'Marketplace / Mable';
                            } elseif (str_contains($assignmentType, 'care worker')) {
                                $sourceType = 'Care Worker';
                            } elseif (str_contains($assignmentType, 'third-party') || str_contains($assignmentType, 'third party')) {
                                $sourceType = 'Third-Party Worker';
                            } elseif ($assignmentType !== '') {
                                $sourceType = ucfirst($assignment->assignment_type);
                            }
                            $complianceStatus = $worker->compliance_expiry_at && $worker->compliance_expiry_at->isPast() ? 'expired' : 'valid';
                            $bgCheckStatus = $worker->background_check_expiry_at && $worker->background_check_expiry_at->isPast() ? 'expired' : 'valid';
                            $profileComplete = $worker->email && $worker->phone && $worker->qualification;
                            $marketplaceAgreement = str_contains(strtolower($worker->notes ?? ''), 'mable') || str_contains(strtolower($worker->notes ?? ''), 'marketplace');
                        @endphp
                        <div class="col-12 col-md-6">
                            <div class="card portal-card p-4 h-100 border-start border-info">
                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                    <div>
                                        <h6 class="mb-0">{{ $worker->first_name }} {{ $worker->last_name }}</h6>
                                        <p class="small text-muted mb-1">{{ $assignment->assignment_type ?? $worker->role_type }}</p>
                                        <span class="badge bg-info text-white">Source: {{ $sourceType }}</span>
                                    </div>
                                    <span class="badge bg-info flex-shrink-0">Active</span>
                                </div>

                                <!-- Service Period -->
                                <div class="small mb-3 pb-3 border-bottom">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <p class="text-muted mb-1">Service Start</p>
                                            <strong>{{ $assignment->start_date->format('d M Y') }}</strong>
                                        </div>
                                        @if($assignment->end_date)
                                            <div class="col-6">
                                                <p class="text-muted mb-1">Service End</p>
                                                <strong>{{ $assignment->end_date->format('d M Y') }}</strong>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Specialization -->
                                @if($worker->qualification)
                                    <div class="small mb-3">
                                        <p class="text-muted mb-1">Specialization</p>
                                        <div class="badge bg-light text-dark">{{ $worker->qualification }}</div>
                                    </div>
                                @endif

                                <!-- Compliance Status -->
                                <div class="small mb-3 pb-3 border-bottom">
                                    <p class="text-muted mb-2">Compliance Status</p>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <span class="badge {{ $bgCheckStatus === 'valid' ? 'bg-success' : 'bg-danger' }} w-100">Background check</span>
                                            <div class="mt-2 small text-muted">{{ $worker->background_check_expiry_at ? $worker->background_check_expiry_at->format('d M Y') : 'Not set' }}</div>
                                        </div>
                                        <div class="col-6">
                                            <span class="badge {{ $complianceStatus === 'valid' ? 'bg-success' : 'bg-danger' }} w-100">Worker compliance</span>
                                            <div class="mt-2 small text-muted">{{ $worker->compliance_expiry_at ? $worker->compliance_expiry_at->format('d M Y') : 'Not set' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="small mb-3 pb-3 border-bottom">
                                    <p class="text-muted mb-2">Profile Snapshot</p>
                                    <p class="mb-1"><strong>Phone:</strong> {{ $worker->phone ?? 'Not provided' }}</p>
                                    <p class="mb-1"><strong>Email:</strong> {{ $worker->email ?? 'Not provided' }}</p>
                                </div>

                                <div class="small mb-3">
                                    <p class="text-muted mb-2">Third-Party Worker Compliance Checklist</p>
                                    <ul class="list-unstyled small mb-0">
                                        <li><i class="bi {{ $bgCheckStatus === 'valid' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i>Background check current</li>
                                        <li><i class="bi {{ $complianceStatus === 'valid' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i>Compliance agreement current</li>
                                        <li><i class="bi {{ $profileComplete ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i>Worker profile complete</li>
                                        <li><i class="bi {{ $marketplaceAgreement ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' }} me-2"></i>Mable/Marketplace agreement</li>
                                    </ul>
                                </div>

                                <!-- Contact Information -->
                                <div class="small mb-3 pb-3 border-bottom">
                                    <p class="text-muted mb-2">Contact Information</p>
                                    <p class="mb-2">
                                        <i class="bi bi-telephone me-2"></i>
                                        <a href="tel:{{ $worker->phone }}">{{ $worker->phone }}</a>
                                    </p>
                                    <p class="mb-0">
                                        <i class="bi bi-envelope me-2"></i>
                                        <a href="mailto:{{ $worker->email }}">{{ $worker->email }}</a>
                                    </p>
                                </div>

                                <!-- Actions -->
                                <div class="d-grid gap-2">
                                    <a href="tel:{{ $worker->phone }}" class="btn btn-sm btn-soft-info">
                                        <i class="bi bi-telephone me-1"></i>Call Provider
                                    </a>
                                    <a href="mailto:{{ $worker->email }}" class="btn btn-sm btn-soft-info">
                                        <i class="bi bi-envelope me-1"></i>Send Message
                                    </a>
                                    @if($worker->user_id)
                                        <a href="{{ route('portal.messages.compose', $worker->user_id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-chat-dots me-1"></i>Chat
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Work History -->
    @if($recentCareNotes->isNotEmpty())
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                    <h5 class="mb-0">Recent Work Notes</h5>
                    <a href="{{ route('portal.participant.care_notes.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>

                <div class="card portal-card">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-muted small">Worker</th>
                                    <th class="text-muted small">Service</th>
                                    <th class="text-muted small">Date</th>
                                    <th class="text-muted small">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentCareNotes as $note)
                                    <tr>
                                        <td class="small">
                                            @if($note->worker)
                                                <strong>{{ $note->worker->first_name }} {{ $note->worker->last_name }}</strong>
                                            @else
                                                <span class="text-muted">Not specified</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ $note->service_type ?? 'General Care' }}</td>
                                        <td class="text-muted small">{{ $note->shift_date ? \Illuminate\Support\Carbon::parse($note->shift_date)->format('d M Y') : '' }}</td>
                                        <td class="small text-truncate" style="max-width: 200px;" title="{{ $note->care_summary }}">
                                            {{ \Illuminate\Support\Str::limit($note->care_summary, 80) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Assignment History -->
    @if($pastAssignments->isNotEmpty())
        <div class="row g-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                    <h5 class="mb-0">Past Assignments</h5>
                    <small class="text-muted">{{ $pastAssignments->count() }} worker(s)</small>
                </div>

                <div class="card portal-card">
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-muted small">Worker Name</th>
                                    <th class="text-muted small">Role</th>
                                    <th class="text-muted small">End Date</th>
                                    <th class="text-muted small">Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pastAssignments as $assignment)
                                    <tr>
                                        <td class="small">
                                            <strong>{{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}</strong>
                                        </td>
                                        <td class="small text-muted">{{ $assignment->worker->role_type ?? 'Worker' }}</td>
                                        <td class="small">{{ optional($assignment->end_date)->format('d M Y') ?? 'N/A' }}</td>
                                        <td class="small text-muted">
                                            @if($assignment->start_date && $assignment->end_date)
                                                {{ $assignment->start_date->diffInMonths($assignment->end_date) }} months
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Request New Worker -->
    <div class="row g-3 mt-4">
        <div class="col-12">
            <div class="card portal-card p-4 bg-light border-0">
                <div class="d-flex align-items-start gap-3">
                    <i class="bi bi-question-circle-fill text-info fs-5 flex-shrink-0 mt-1"></i>
                    <div class="flex-grow-1">
                        <h6 class="mb-2">Need a New Worker or Supplier?</h6>
                        <p class="text-muted small mb-3">If you need additional care workers, service providers, or have concerns about current assignments, please reach out to your support person or submit a care note with your request.</p>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('portal.participant.care_notes.index') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil-fill me-1"></i>Submit Request via Care Notes
                            </a>
                            @if($participant->supportPerson)
                                <a href="mailto:{{ $participant->supportPerson->email }}?subject=New%20Worker%20Request" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-envelope me-1"></i>Email Support Person
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .btn-soft-primary {
        background: rgba(22, 153, 161, 0.1);
        color: #0b7280;
        border: 1px solid rgba(22, 153, 161, 0.2);
    }
    .btn-soft-primary:hover {
        background: rgba(22, 153, 161, 0.15);
        color: #0b7280;
    }

    .btn-soft-info {
        background: rgba(14, 165, 233, 0.1);
        color: #0c4a6e;
        border: 1px solid rgba(14, 165, 233, 0.2);
    }
    .btn-soft-info:hover {
        background: rgba(14, 165, 233, 0.15);
        color: #0c4a6e;
    }

    .border-start {
        border-left: 3px solid !important;
    }

    @media (max-width: 576px) {
        h1 { font-size: 1.5rem; }
        h5 { font-size: 1rem; }
        .table-responsive {
            font-size: 0.875rem;
        }
    }
</style>
@endpush

