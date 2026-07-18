@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>{{ $worker->first_name }} {{ $worker->last_name }}<br><small class="text-muted">{{ $worker->email }}</small></h2>
                <span class="badge bg-primary fs-5">{{ $stage->label() }}</span>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Stage Progress -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Onboarding Progress</h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar" style="width: {{ ($worker->onboarding_stage / 6) * 100 }}%">
                            Stage {{ $worker->onboarding_stage }}/6
                        </div>
                    </div>

                    <div class="row g-3">
                        @for ($i = 1; $i <= 6; $i++)
                            <div class="col-md-2 text-center">
                                <div class="mb-2">
                                    @if ($i < $worker->onboarding_stage)
                                        <div class="badge rounded-circle" style="width: 40px; height: 40px; background-color: #198754; display: inline-flex; align-items: center; justify-content: center; font-size: 20px;">✓</div>
                                    @elseif ($i == $worker->onboarding_stage)
                                        <div class="badge rounded-circle" style="width: 40px; height: 40px; background-color: #0d6efd; display: inline-flex; align-items: center; justify-content: center; font-size: 16px;">●</div>
                                    @else
                                        <div class="badge rounded-circle" style="width: 40px; height: 40px; background-color: #e9ecef; color: #6c757d; display: inline-flex; align-items: center; justify-content: center;">{{ $i }}</div>
                                    @endif
                                </div>
                                <small>Stage {{ $i }}</small>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Submitted Worker Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Qualification</label>
                            <div class="form-control-plaintext ps-0">{{ $worker->qualification ?: 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Availability</label>
                            <div class="form-control-plaintext ps-0">{{ $worker->availability ?: 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Vehicle Type</label>
                            <div class="form-control-plaintext ps-0">{{ $worker->vehicle_type ?: 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Notes</label>
                            <div class="form-control-plaintext ps-0">{{ $worker->notes ?: 'No notes provided' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Submitted Documents</h6>
                    <span class="badge bg-secondary">{{ $worker->complianceDocuments->count() }}</span>
                </div>
                <div class="card-body">
                    @if ($worker->complianceDocuments->isEmpty())
                        <div class="alert alert-warning mb-0">No documents have been uploaded yet.</div>
                    @else
                        <div class="list-group">
                            @foreach ($worker->complianceDocuments as $doc)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between gap-3 align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $doc->document_type }}</h6>
                                            <p class="mb-1 small text-muted">{{ basename($doc->document_path ?? '') }}</p>
                                            <div class="small text-muted">
                                                <div>Uploaded: {{ $doc->created_at->format('M d, Y') }}</div>
                                                @if ($doc->issue_date)
                                                    <div>Issue date: {{ $doc->issue_date->format('M d, Y') }}</div>
                                                @endif
                                                @if ($doc->expiry_date)
                                                    <div>Expiry date: {{ $doc->expiry_date->format('M d, Y') }}</div>
                                                @endif
                                                @if ($doc->notes)
                                                    <div>Notes: {{ $doc->notes }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-{{ $doc->status === 'submitted' ? 'warning' : ($doc->status === 'active' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'secondary')) }} mb-2">
                                                {{ ucfirst($doc->status) }}
                                            </span>
                                            @if ($doc->document_path)
                                                <div class="d-flex gap-2 justify-content-end mt-2">
                                                    <a href="{{ route('portal.admin.compliance.documents.preview', $doc) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                                    <a href="{{ route('portal.admin.compliance.documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Submitted Declarations</h6>
                    <span class="badge bg-secondary">{{ $worker->declarations->count() }}</span>
                </div>
                <div class="card-body">
                    @if ($worker->declarations->isEmpty())
                        <div class="alert alert-info mb-0">No declarations have been submitted yet.</div>
                    @else
                        <div class="list-group">
                            @foreach ($worker->declarations as $decl)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between gap-3 align-items-start flex-wrap">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $decl->declaration_type->label() }}</h6>
                                            <p class="mb-1">{{ $decl->declaration_text }}</p>
                                            <div class="small text-muted">
                                                <div>Status: {{ $decl->isSigned() ? 'Signed' : ($decl->isDeclined() ? 'Declined' : 'Pending') }}</div>
                                                @if ($decl->signed_at)
                                                    <div>Signed: {{ $decl->signed_at->format('M d, Y H:i') }}</div>
                                                @endif
                                                @if ($decl->decline_reason)
                                                    <div>Decline reason: {{ $decl->decline_reason }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="badge bg-{{ $decl->isSigned() ? 'success' : ($decl->isDeclined() ? 'danger' : 'warning') }}">
                                            {{ $decl->isSigned() ? 'Signed' : ($decl->isDeclined() ? 'Declined' : 'Pending') }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Stage Details -->
            @if ($worker->onboarding_stage == 1)
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-light border-primary">
                        <h6 class="mb-0">Stage 1: Invited</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> Awaiting account setup and MFA enrollment</p>
                        <p><strong>Invitation Token:</strong> {{ $worker->onboarding_token }}</p>
                        <p><strong>Expires:</strong> {{ $worker->onboarding_expires_at ? $worker->onboarding_expires_at->format('M d, Y H:i') : 'N/A' }}</p>
                        <p><strong>Invited By:</strong> {{ $worker->invitedBy ? $worker->invitedBy->name : 'N/A' }}</p>
                        <p><strong>Invited At:</strong> {{ $worker->invited_at ? $worker->invited_at->format('M d, Y') : 'N/A' }}</p>
                        
                        @if (!$worker->user)
                            <div class="alert alert-warning">User account not yet created. Awaiting worker account creation.</div>
                        @elseif (! $worker->user->mfa_enabled)
                            <div class="alert alert-warning">User account created. Awaiting worker MFA setup before Stage 2.</div>
                        @else
                            <form method="POST" action="{{ route('admin.worker_onboarding.stage1.advance', $worker) }}">
                                @csrf
                                <button type="submit" class="btn btn-success">Move to Stage 2</button>
                            </form>
                        @endif
                    </div>
                </div>
            @elseif ($worker->onboarding_stage == 2)
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-light border-primary">
                        <h6 class="mb-0">Stage 2: Upload Compliance Documents</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> Awaiting compliance document submission</p>
                        <p><strong>Submitted At:</strong> {{ $worker->stage_2_submitted_at ? $worker->stage_2_submitted_at->format('M d, Y') : 'Not submitted yet' }}</p>

                        @if ($worker->complianceDocuments->isEmpty())
                            <div class="alert alert-warning">No documents submitted yet.</div>
                        @else
                            <div class="list-group mb-3">
                                @foreach ($worker->complianceDocuments as $doc)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between gap-3 align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1">{{ $doc->document_type }}</h6>
                                                <p class="mb-1 small text-muted">{{ basename($doc->document_path ?? '') }}</p>
                                                <small class="text-muted">Uploaded: {{ $doc->created_at->format('M d, Y') }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-{{ $doc->status === 'submitted' ? 'warning' : ($doc->status === 'active' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'secondary')) }} mb-2">
                                                    {{ ucfirst($doc->status) }}
                                                </span>
                                                <div class="d-flex gap-2 justify-content-end mt-2">
                                                    @if ($doc->document_path)
                                                        <a href="{{ route('portal.admin.compliance.documents.preview', $doc) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                                        <a href="{{ route('portal.admin.compliance.documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.worker_onboarding.stage2.approve', $worker) }}" class="mb-2">
                            @csrf
                            <div class="mb-3">
                                <label for="notes" class="form-label">Review Notes (Optional)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-success">Approve & Move to Stage 3</button>
                        </form>

                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectStage2Modal">
                            Reject Stage 2
                        </button>
                    </div>
                </div>
            @elseif ($worker->onboarding_stage == 3)
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-light border-primary">
                        <h6 class="mb-0">Stage 3: Document Review</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> Awaiting admin review completion</p>
                        <p><strong>Submitted At:</strong> {{ $worker->stage_3_submitted_at ? $worker->stage_3_submitted_at->format('M d, Y') : 'Not submitted' }}</p>

                        @if ($worker->complianceDocuments->isEmpty())
                            <div class="alert alert-warning">No documents to review.</div>
                        @else
                            <form method="POST" action="{{ route('admin.worker_onboarding.stage3.verify', $worker) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Documents</label>
                                    <div class="list-group">
                                        @foreach ($worker->complianceDocuments as $index => $doc)
                                            <div class="list-group-item d-flex align-items-start gap-3 flex-wrap">
                                                <div class="form-check me-3">
                                                    <input class="form-check-input" type="checkbox" value="1" id="select_{{ $index }}" onchange="toggleRow({{ $index }})">
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                                        <div>
                                                            <h6 class="mb-1">{{ $doc->document_type }}</h6>
                                                            <p class="mb-1 small text-muted">{{ basename($doc->document_path ?? '') }}</p>
                                                            <small class="text-muted">Uploaded: {{ $doc->created_at->format('M d, Y') }}</small>
                                                        </div>
                                                        <div class="text-end d-flex flex-wrap gap-2 justify-content-end">
                                                            @if ($doc->document_path)
                                                                <a href="{{ route('portal.admin.compliance.documents.preview', $doc) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                                                <a href="{{ route('portal.admin.compliance.documents.download', $doc) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="row mt-2 g-2 align-items-center">
                                                        <input type="hidden" id="id_{{ $index }}" name="documents[{{ $index }}][id]" value="{{ $doc->id }}" disabled>
                                                        <div class="col-auto">
                                                            <select name="documents[{{ $index }}][action]" id="action_{{ $index }}" class="form-select form-select-sm" disabled>
                                                                <option value="active">Mark Active</option>
                                                                <option value="reject">Mark Rejected</option>
                                                            </select>
                                                        </div>
                                                        <div class="col">
                                                            <input type="text" name="documents[{{ $index }}][reason]" id="reason_{{ $index }}" class="form-control form-control-sm" placeholder="Rejection reason (optional)" disabled>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="ms-3">
                                                    <span class="badge bg-{{ strtolower($doc->status) === 'active' ? 'success' : (strtolower($doc->status) === 'rejected' ? 'danger' : 'secondary') }}">{{ $doc->status }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <input type="hidden" name="auto_approve" value="1">

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Review Notes (Optional)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">Process Selected Documents</button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('admin.worker_onboarding.stage3.approve', $worker) }}">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">Approve & Move to Stage 4</button>
                            </form>
                        @endif
                    </div>
                </div>
            @elseif ($worker->onboarding_stage == 4)
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-light border-primary">
                        <h6 class="mb-0">Stage 4: Sign Declarations</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> Awaiting declaration signatures</p>

                        @if ($worker->declarations->isEmpty())
                            <div class="alert alert-warning">No declarations available.</div>
                        @else
                            <div class="list-group mb-3">
                                @foreach ($worker->declarations as $decl)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between flex-wrap align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $decl->declaration_type->label() }}</h6>
                                                <small class="text-muted">{{ $decl->isSigned() ? 'Signed: ' . $decl->signed_at->format('M d, Y') : 'Not yet signed' }}</small>
                                            </div>
                                            <span class="badge bg-{{ $decl->isSigned() ? 'success' : 'warning' }} mt-2 mt-md-0">
                                                {{ $decl->isSigned() ? '✓' : 'Pending' }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.worker_onboarding.stage4.approve', $worker) }}">
                            @csrf
                            <button type="submit" class="btn btn-success" {{ !$worker->getAllDeclarationsSignedForStage4() ? 'disabled' : '' }}>
                                Approve & Move to Stage 5
                            </button>
                        </form>
                    </div>
                </div>
            @elseif ($worker->onboarding_stage == 5)
                <div class="card shadow-sm mb-4 border-primary">
                    <div class="card-header bg-light border-primary">
                        <h6 class="mb-0">Stage 5: Define Approved Services</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> Awaiting service category approval</p>

                        @if ($worker->serviceApprovals->isEmpty())
                            <div class="alert alert-info">No services approved yet. Add at least one service to proceed.</div>
                        @else
                            <div class="list-group mb-3">
                                @foreach ($worker->serviceApprovals as $service)
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between flex-wrap align-items-center">
                                            <div>
                                                <h6 class="mb-1">{{ $service->service_category }}</h6>
                                                @if ($service->description)
                                                    <small class="text-muted">{{ $service->description }}</small>
                                                @endif
                                            </div>
                                            <span class="badge bg-success mt-2 mt-md-0">Approved</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.worker_onboarding.stage5.services.add', $worker) }}" class="mb-4">
                            @csrf
                            <h6>Add Service Category</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" name="service_category" placeholder="e.g., Personal Care" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="date" class="form-control" name="approval_end_date" placeholder="End date (optional)">
                                </div>
                            </div>
                            <textarea class="form-control mb-3" name="description" placeholder="Description (optional)" rows="2"></textarea>
                            <button type="submit" class="btn btn-sm btn-primary">Add Service</button>
                        </form>

                        <form method="POST" action="{{ route('admin.worker_onboarding.stage5.approve', $worker) }}" class="mb-3">
                            @csrf
                            <button type="submit" class="btn btn-success" {{ $worker->serviceApprovals->isEmpty() ? 'disabled' : '' }}>
                                Approve & Move to Stage 6
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#rejectWorkerModal">
                            Reject Worker
                        </button>
                    </div>
                </div>
            @elseif ($worker->onboarding_stage == 6)
                <div class="card shadow-sm mb-4 border-success">
                    <div class="card-header bg-light border-success">
                        <h6 class="mb-0">Stage 6: Assigned to Participant</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> Onboarding complete</p>
                        <p><strong>Assigned At:</strong> {{ $worker->stage_6_assigned_at ? $worker->stage_6_assigned_at->format('M d, Y') : 'N/A' }}</p>

                        <form method="POST" action="{{ route('admin.worker_onboarding.stage6.assign_participants', $worker) }}">
                            @csrf

                            <div class="mb-3">
                                <label for="participant_ids" class="form-label">Assign Participants</label>
                                <select id="participant_ids" name="participant_ids[]" class="form-select" multiple size="10">
                                    @foreach($participants as $participant)
                                        <option value="{{ $participant->id }}"
                                            {{ in_array($participant->id, $selectedParticipantIds ?? []) ? 'selected' : '' }}>
                                            {{ $participant->first_name }} {{ $participant->last_name }} · {{ $participant->participant_number ?? 'No ID' }}
                                            @if(in_array($participant->id, $nominatedParticipantIds))
                                                (Nominated Participant)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Search by name or participant number. Nominated participants are pre-selected.</div>
                                @error('participant_ids')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-success">Save Assignments</button>
                        </form>

                        @if ($worker->assignments->isNotEmpty())
                            <div class="mt-4">
                                <h6>Current Assignments</h6>
                                <div class="list-group">
                                    @foreach ($worker->assignments as $assignment)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between flex-wrap align-items-center">
                                                <div>
                                                    <h6 class="mb-1">{{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}</h6>
                                                    <small class="text-muted">Assigned: {{ optional($assignment->start_date)->format('M d, Y') }}</small>
                                                </div>
                                                <span class="badge bg-success mt-2 mt-md-0">Active</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Worker Information</h6>
                </div>
                <div class="card-body">
                    <p><small><strong>Worker ID:</strong></small><br><code>{{ $worker->worker_number }}</code></p>
                    <p><small><strong>Phone:</strong></small><br>{{ $worker->phone }}</p>
                    <p><small><strong>Email:</strong></small><br>{{ $worker->email }}</p>
                    <p><small><strong>Role Type:</strong></small><br>{{ $worker->role_type }}</p>
                    <p><small><strong>Status:</strong></small><br><span class="badge bg-{{ $worker->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($worker->status) }}</span></p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.worker_onboarding.resend_invitation', $worker) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm w-100">Resend Invitation Email</button>
                    </form>

                    <button type="button" class="btn btn-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#rejectWorkerModal">
                        Reject Onboarding
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Stage 2 Modal -->
<div class="modal fade" id="rejectStage2Modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Stage 2</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.worker_onboarding.stage2.reject', $worker) }}">
                @csrf
                <div class="modal-body">
                    <label for="rejection_reason" class="form-label">Rejection Reason</label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Worker Modal -->
<div class="modal fade" id="rejectWorkerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Onboarding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.worker_onboarding.reject', $worker) }}">
                @csrf
                <div class="modal-body">
                    <label for="rejection_reason" class="form-label">Rejection Reason</label>
                    <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Onboarding</button>
                </div>
            </form>
        </div>
    </div>
</div>
        <script>
            function toggleRow(index) {
                const cb = document.getElementById('select_' + index);
                const action = document.getElementById('action_' + index);
                const reason = document.getElementById('reason_' + index);
                const idInput = document.getElementById('id_' + index);
                if (!cb || !action || !reason || !idInput) return;
                const enabled = cb.checked;
                action.disabled = !enabled;
                reason.disabled = !enabled;
                idInput.disabled = !enabled;
            }
        </script>
@endsection
