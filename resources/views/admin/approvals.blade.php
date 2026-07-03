@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="content-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-1">Approval Workflows</h2>
                <p class="text-muted mb-0">Review and approve pre-approvals, invoices, and care notes</p>
            </div>
            <a href="{{ route('portal.admin.dashboard') }}" class="btn btn-outline-primary">← Back to Dashboard</a>
        </div>
    </div>

    <!-- TAB NAVIGATION -->
    <ul class="nav nav-tabs mb-4" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pre-approvals-tab" data-bs-toggle="tab" data-bs-target="#pre-approvals" type="button" role="tab">
                <i class="bi bi-check-circle me-2"></i>Pre-Approvals ({{ $pendingApprovals->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                <i class="bi bi-receipt me-2"></i>Invoices ({{ $invoices->count() }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="care-notes-tab" data-bs-toggle="tab" data-bs-target="#care-notes" type="button" role="tab">
                <i class="bi bi-file-earmark-text me-2"></i>Care Notes ({{ $careNotes->count() }})
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- PRE-APPROVALS TAB -->
        <div class="tab-pane fade show active" id="pre-approvals" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    @if($pendingApprovals->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Participant</th>
                                        <th>Request Type</th>
                                        <th>Amount</th>
                                        <th>Purpose</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingApprovals as $approval)
                                        <tr>
                                            <td class="fw-bold">{{ $approval->participant->first_name ?? 'N/A' }}</td>
                                            <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $approval->request_type)) }}</span></td>
                                            <td>${{ number_format($approval->amount_cents / 100, 2) }}</td>
                                            <td>{{ Str::limit($approval->description, 30) }}</td>
                                            <td><small class="text-muted">{{ $approval->created_at->format('M d, Y') }}</small></td>
                                            <td><span class="badge bg-warning text-dark">{{ ucfirst($approval->status) }}</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approvalModal" onclick="viewApproval({{ $approval->id }})">
                                                    <i class="bi bi-eye me-1"></i>Review
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">All caught up! No pending pre-approvals.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- INVOICES TAB -->
        <div class="tab-pane fade" id="invoices" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Participant</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Attachments</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td class="fw-bold">#{{ $invoice->id }}</td>
                                            <td>{{ $invoice->participant->first_name ?? 'N/A' }}</td>
                                            <td>${{ number_format($invoice->amount_cents / 100, 2) }}</td>
                                            <td><small class="text-muted">{{ $invoice->created_at->format('M d, Y') }}</small></td>
                                            <td>
                                                @if($invoice->attachments)
                                                    <span class="badge bg-primary">{{ count(json_decode($invoice->attachments, true)) }}</span>
                                                @else
                                                    <span class="badge bg-light text-dark">0</span>
                                                @endif
                                            </td>
                                            <td><span class="badge bg-{{ $invoice->status === 'submitted' ? 'warning' : 'success' }} text-dark">{{ ucfirst($invoice->status) }}</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>Review
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">No invoices pending review.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- CARE NOTES TAB -->
        <div class="tab-pane fade" id="care-notes" role="tabpanel">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    @if($careNotes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Participant</th>
                                        <th>Worker</th>
                                        <th>Visit Date</th>
                                        <th>Service Type</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($careNotes as $note)
                                        <tr>
                                            <td class="fw-bold">{{ optional($note->participant)->first_name ?? 'N/A' }}</td>
                                            <td>{{ optional($note->worker)->first_name ?? 'N/A' }}</td>
                                            <td><small>{{ optional($note->shift_date)->format('M d, Y') }}</small></td>
                                            <td>{{ ucfirst($note->service_type ?? 'General') }}</td>
                                            <td><span class="badge bg-{{ $note->status === 'draft' ? 'secondary' : 'success' }}">{{ ucfirst($note->status) }}</span></td>
                                            <td><small class="text-muted">{{ $note->submitted_at?->format('M d, Y') ?? 'Not submitted' }}</small></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye me-1"></i>View
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-text" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="text-muted mt-3">No care notes to review.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- APPROVAL MODAL -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Pre-Approval Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="approvalContent">
                <p class="text-muted">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success">Approve</button>
                <button type="button" class="btn btn-danger">Decline</button>
            </div>
        </div>
    </div>
</div>

<script>
function viewApproval(id) {
    // This would load approval details via AJAX
    console.log('View approval:', id);
}
</script>

@endsection
