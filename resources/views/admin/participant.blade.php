@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Participant: {{ $participant->first_name }} {{ $participant->last_name }}</h4>
            <p class="text-muted mb-0">{{ $participant->participant_number }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.admin.participants') }}" class="btn btn-sm btn-outline-secondary">All participants</a>
            <a href="{{ route('portal.admin.assignments') }}" class="btn btn-sm btn-outline-secondary">View assignments</a>
            <a href="{{ route('portal.admin.participants.care_notes', $participant) }}" class="btn btn-sm btn-outline-secondary">Care notes</a>
            <a href="{{ route('portal.admin.budgets') }}" class="btn btn-sm btn-outline-secondary">Budget summary</a>
            <a href="{{ route('portal.admin.participants.edit', $participant) }}" class="btn btn-sm btn-primary">Edit</a>
            @if($participant->user)
                <form method="POST" action="{{ route('portal.admin.users.dashboard.login', $participant->user) }}" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="btn btn-sm btn-outline-success">Force dashboard login</button>
                </form>
            @endif
            @if(in_array($participant->status, ['onboarding', 'changes_requested'], true))
                <form method="POST" action="{{ route('portal.admin.participants.resend_onboarding', $participant) }}" class="d-inline-block">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-info">Resend onboarding invitation</button>
                </form>
            @endif
            <form method="POST" action="{{ route('portal.admin.participants.destroy', $participant) }}" class="d-inline-block" onsubmit="return confirm('Delete this participant?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
            </form>

            @php
                $inReviewStates = [
                    \App\Models\Participant::STATUS_PENDING_ADMIN_REVIEW,
                ];
            @endphp

            @if(in_array($participant->status, $inReviewStates, true))
                <form id="approve-form" method="POST" action="{{ route('portal.admin.participants.approve', $participant) }}" class="d-inline-block">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                </form>

                <form id="request-changes-form" method="POST" action="{{ route('portal.admin.participants.request_changes', $participant) }}" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="notes" id="request_changes_notes">
                    <button type="button" class="btn btn-sm btn-warning" onclick="submitRequestChanges()">Request changes</button>
                </form>

                <form id="reject-form" method="POST" action="{{ route('portal.admin.participants.reject', $participant) }}" class="d-inline-block">
                    @csrf
                    <input type="hidden" name="reason" id="reject_reason">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="submitReject()">Reject</button>
                </form>
            @endif

            @if($participant->status === 'active')
                <form method="POST" action="{{ route('portal.admin.participants.deactivate', $participant) }}" class="d-inline-block" onsubmit="return confirm('Deactivate this participant?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning">Deactivate</button>
                </form>
            @endif
        </div>
    </div>

    <script>
        function submitRequestChanges() {
            const notes = prompt('Enter notes to request changes (optional):');
            if (notes === null) return; // cancelled
            document.getElementById('request_changes_notes').value = notes;
            document.getElementById('request-changes-form').submit();
        }

        function submitReject() {
            const reason = prompt('Enter rejection reason (optional):');
            if (reason === null) return;
            document.getElementById('reject_reason').value = reason;
            document.getElementById('reject-form').submit();
        }
    </script>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card p-3">
                <h6>Profile</h6>
                <p class="mb-1"><strong>Participant #</strong> {{ $participant->participant_number }}</p>
                <p class="mb-1"><strong>Email</strong> {{ $participant->email }}</p>
                <p class="mb-1"><strong>Phone</strong> {{ $participant->phone }}</p>
                <p class="mb-1"><strong>Status</strong> {{ ucfirst($participant->status) }}</p>
                <hr>
                <h6 class="mb-2">Assign Worker</h6>
                <form method="POST" action="{{ route('portal.admin.assign_worker') }}">
                    @csrf
                    <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                    <div class="mb-2">
                        <select name="worker_id" class="form-select" required>
                            <option value="">Select worker</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}">{{ $worker->first_name }} {{ $worker->last_name }} ({{ $worker->worker_number ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-primary">Assign</button>
                    </div>
                </form>
            </div>

            <div class="card p-3 mt-3">
                @php
                    $budgetLimit = $participant->budget_limit_cents ?? 0;
                    $budgetUsed = $participant->current_budget_used_cents ?? 0;
                    $budgetRemaining = max(0, $budgetLimit - $budgetUsed);
                    $budgetPercent = $budgetLimit ? min(100, round(($budgetUsed / $budgetLimit) * 100, 2)) : 0;
                @endphp
                <h6>Budget</h6>
                <p class="mb-1"><strong>Limit:</strong> ${{ number_format($budgetLimit / 100, 2) }}</p>
                <p class="mb-1"><strong>Used:</strong> ${{ number_format($budgetUsed / 100, 2) }}</p>
                <p class="mb-1"><strong>Remaining:</strong> ${{ number_format($budgetRemaining / 100, 2) }}</p>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar {{ $budgetPercent > 100 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ min(100, $budgetPercent) }}%;"></div>
                </div>
            </div>

            <div class="card p-3 mt-3">
                <h6>Assign onboarding form</h6>
                <p class="small text-muted mb-3">Pending participant documents: {{ $pendingDocumentsCount }}</p>
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('portal.admin.documents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="owner_type" value="participant">
                    <input type="hidden" name="owner_id" value="{{ $participant->id }}">

                    <div class="mb-3">
                        <label for="title" class="form-label">Form title</label>
                        <input id="title" name="title" type="text" class="form-control" value="{{ old('title') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="document_type" class="form-label">Form type</label>
                        <select id="document_type" name="document_type" class="form-select" required>
                            @foreach($documentTypes as $type => $label)
                                <option value="{{ $type }}"{{ old('document_type') === $type ? ' selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="file" class="form-label">Form file</label>
                        <input id="file" name="file" type="file" class="form-control" required>
                        <div class="form-text">Accepted formats: PDF, JPG, PNG, DOC, DOCX, TXT.</div>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary">Assign form</button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card p-3 mb-3">
                <h6>Risk profile</h6>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="text-muted small mb-1">Current risk level</p>
                        <h4 class="mb-1">{{ $currentRisk->level ?? 'Low Risk' }}</h4>
                        <p class="small text-muted mb-0">Score {{ $currentRisk->score ?? 0 }}</p>
                    </div>
                    <span class="badge bg-{{ $currentRisk && $currentRisk->level === 'Critical Risk' ? 'danger' : ($currentRisk && $currentRisk->level === 'High Risk' ? 'warning' : ($currentRisk && $currentRisk->level === 'Medium Risk' ? 'info' : 'secondary')) }} py-2 px-3">
                        {{ $currentRisk->level ?? 'Low Risk' }}
                    </span>
                </div>
                @if(!empty($currentRisk?->trigger_reasons))
                    <div class="small text-muted mb-2">Trigger reasons</div>
                    <ul class="mb-0">
                        @foreach($currentRisk->trigger_reasons as $reason)
                            <li>{{ $reason }}</li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-muted small">No current risk triggers.</div>
                @endif
                @if(!empty($currentRisk?->calculated_at))
                    <div class="text-muted small mt-3">Calculated {{ $currentRisk->calculated_at->format('Y-m-d H:i') }}</div>
                @endif
            </div>

            <div class="card p-3 mb-3">
                <h6>Risk history</h6>
                @if($riskHistory->isEmpty())
                    <p class="text-muted mb-0">No previous risk records found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Level</th>
                                    <th>Score</th>
                                    <th>Reasons</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($riskHistory as $entry)
                                    <tr>
                                        <td>{{ optional($entry->calculated_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ $entry->level }}</td>
                                        <td>{{ $entry->score }}</td>
                                        <td>{{ implode(', ', $entry->trigger_reasons ?? []) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card p-3 mb-3">
                <h6>Summary</h6>
                <div class="row">
                    <div class="col-md-4">
                        <div class="small text-muted">Care notes</div>
                        <div class="fw-bold">{{ $participant->careNotes->count() }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Invoices</div>
                        <div class="fw-bold">{{ $participant->invoices->count() }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Pre-approvals</div>
                        <div class="fw-bold">{{ $participant->preApprovalRequests->count() }}</div>
                    </div>
                </div>
            </div>

            <div class="card p-3 mb-3">
                <h6>Onboarding review</h6>
                <p class="small text-muted mb-2">Review the participant's submitted onboarding details, agreements and documents.</p>

                <div class="mb-2">
                    <strong>Status:</strong>
                    <span class="badge bg-info text-dark">{{ ucfirst($participant->status) }}</span>
                </div>

                @if($onboardingProgress)
                    <div class="mb-2">
                        <strong>Onboarding progress:</strong> {{ ucfirst($onboardingProgress->status) }}
                        ({{ count($onboardingProgress->completed_steps ?? []) }} of 8 steps completed)
                    </div>
                @else
                    <div class="mb-2 text-muted">No onboarding progress record available.</div>
                @endif

                @php
                    $onboardingSubmission = $participant->latestOnboardingSubmission();
                    $draftData = $onboardingProgress?->draft_data ?? [];
                @endphp

                @if($onboardingSubmission || ($draftData && count((array) $draftData) > 0))
                    <hr>
                    
                    <!-- Personal Data Section -->
                    @php
                        $personalData = $onboardingSubmission?->personal_data ?? $draftData;
                    @endphp
                    @if($personalData && count((array) $personalData) > 0)
                        <div class="mb-3">
                            <strong class="d-block mb-2">Personal Details (Onboarding {{ $onboardingSubmission ? 'Submission' : 'Draft' }})</strong>
                            <div class="row ms-1">
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Full Name</div>
                                    <div>{{ $personalData['full_name'] ?? 'Not provided' }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Emergency Contact Name</div>
                                    <div>{{ $personalData['emergency_contact_name'] ?? 'Not provided' }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Emergency Contact Phone</div>
                                    <div>{{ $personalData['emergency_contact_phone'] ?? 'Not provided' }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Emergency Contact Relationship</div>
                                    <div>{{ $personalData['emergency_contact_relationship'] ?? 'Not provided' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Support Person Data Section -->
                    @php
                        $supportPersonData = $onboardingSubmission?->support_person_data ?? ($draftData['support_first_name'] ? [
                            'first_name' => $draftData['support_first_name'] ?? '',
                            'last_name' => $draftData['support_last_name'] ?? '',
                            'email' => $draftData['support_email'] ?? '',
                            'phone' => $draftData['support_phone'] ?? '',
                            'relationship' => $draftData['support_relationship'] ?? ''
                        ] : null);
                    @endphp
                    @if($supportPersonData)
                        <div class="mb-3">
                            <strong class="d-block mb-2">Support Person (Onboarding {{ $onboardingSubmission ? 'Submission' : 'Draft' }})</strong>
                            <div class="row ms-1">
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Name</div>
                                    <div>
                                        {{ $supportPersonData['first_name'] ?? '' }} 
                                        {{ $supportPersonData['last_name'] ?? '' }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Relationship</div>
                                    <div>{{ $supportPersonData['relationship'] ?? 'Not provided' }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Email</div>
                                    <div>{{ $supportPersonData['email'] ?? 'Not provided' }}</div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="small text-muted">Phone</div>
                                    <div>{{ $supportPersonData['phone'] ?? 'Not provided' }}</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr>
                @endif

                @if($participant->supportPerson)
                    <div class="mb-2">
                        <strong>Support person (Current):</strong>
                        {{ $participant->supportPerson->first_name }} {{ $participant->supportPerson->last_name }}
                        @if($participant->supportPerson->phone)
                            • {{ $participant->supportPerson->phone }}
                        @endif
                        @if($participant->supportPerson->email)
                            • {{ $participant->supportPerson->email }}
                        @endif
                    </div>
                @endif

                <div class="mb-2">
                    <strong>Signed agreements:</strong>
                    {{ $signedAgreements->count() }} / {{ count($requiredAgreements ?? []) }}
                </div>

                @if(isset($missingAgreements) && $missingAgreements->isNotEmpty())
                    <div class="alert alert-warning py-2 mb-2">
                        <strong>Missing agreements:</strong> {{ $missingAgreements->implode(', ') }}
                    </div>
                @endif

                <div class="mb-2">
                    <strong>Submitted documents:</strong>
                </div>

                @if($documents->isEmpty())
                    <div class="text-muted">No documents uploaded yet.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach($documents as $doc)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $doc->title ?? $doc->document_type }}</div>
                                    <div class="small text-muted">Type: {{ $doc->document_type }} • Status: {{ ucfirst($doc->status) }}</div>
                                </div>
                                <div class="text-end">
                                    <a href="{{ route('portal.admin.documents.show', $doc) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if($onboardingSubmission || ($draftData && count((array) $draftData) > 0))
                    <hr>
                    <div class="row mt-3">
                        @if($onboardingSubmission)
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Submission Status</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-{{ $onboardingSubmission->status === 'approved' ? 'success' : ($onboardingSubmission->status === 'rejected' ? 'danger' : ($onboardingSubmission->status === 'changes_requested' ? 'warning' : 'info')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $onboardingSubmission->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Submitted</div>
                                <div class="fw-semibold">{{ $onboardingSubmission->submitted_at?->format('M d, Y H:i') ?? 'Not submitted' }}</div>
                            </div>
                            @if($onboardingSubmission->reviewed_at)
                                <div class="col-md-6 mb-3">
                                    <div class="small text-muted">Reviewed</div>
                                    <div class="fw-semibold">{{ $onboardingSubmission->reviewed_at->format('M d, Y H:i') }}</div>
                                </div>
                            @endif
                            @if($onboardingSubmission->admin_comments)
                                <div class="col-md-6 mb-3">
                                    <div class="small text-muted">Admin Comments</div>
                                    <div class="fw-semibold">{{ $onboardingSubmission->admin_comments }}</div>
                                </div>
                            @endif
                        @else
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Onboarding Status</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-warning">Draft (In Progress)</span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Current Step</div>
                                <div class="fw-semibold">{{ $onboardingProgress->current_step ?? 0 }} of 8</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-1">Budget ledger</h6>
                        <p class="text-muted small mb-0">Current quarter transaction history, approvals and category allocation.</p>
                    </div>
                    <form method="POST" action="{{ route('portal.admin.reports.export') }}" class="d-flex gap-2 align-items-center">
                            @csrf
                            <input type="hidden" name="report_type" value="Participant Budget">
                            <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                            <input type="hidden" name="start_date" value="{{ $budget->quarter_start_date }}">
                            <input type="hidden" name="end_date" value="{{ $budget->quarter_end_date }}">
                            <select name="export_format" class="form-select form-select-sm" style="width: 130px;">
                                @foreach($exportFormats as $format => $label)
                                    <option value="{{ $format }}">{{ strtoupper($format) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary">Export</button>
                            <a href="{{ route('budgets.create', ['participant_id' => $participant->id]) }}" class="btn btn-sm btn-primary">Create Budget</a>
                        </form>
                </div>

                @if(!empty($budgetAlerts))
                    <div class="alert alert-warning">
                        <ul class="mb-0">
                            @foreach($budgetAlerts as $alert)
                                <li>{{ is_array($alert) ? ($alert['message'] ?? json_encode($alert)) : $alert }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="small text-muted">Total available</div>
                        <div class="fw-bold">${{ number_format(($budgetMetrics['total'] ?? 0) / 100, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Committed</div>
                        <div class="fw-bold">${{ number_format(($budgetMetrics['committed'] ?? 0) / 100, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Approved</div>
                        <div class="fw-bold">${{ number_format(($budgetMetrics['approved'] ?? 0) / 100, 2) }}</div>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="small text-muted">Paid</div>
                        <div class="fw-bold">${{ number_format(($budgetMetrics['paid'] ?? 0) / 100, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Remaining</div>
                        @php
                            $bmTotal = $budgetMetrics['total'] ?? $budgetMetrics['total_available'] ?? 0;
                            $bmUsed = $budgetMetrics['used'] ?? ($budgetMetrics['approved'] ?? 0);
                            $bmCommitted = $budgetMetrics['committed'] ?? 0;
                            $bmRemaining = (int) ($bmTotal - $bmCommitted - $bmUsed);
                        @endphp
                        <div class="fw-bold">${{ number_format($bmRemaining / 100, 2) }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="small text-muted">Quarter</div>
                        <div class="fw-bold">{{ $budget->quarter_start_date }} - {{ $budget->quarter_end_date }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($budgetTransactions as $transaction)
                                <tr>
                                    <td>{{ optional($transaction->created_at)->format('Y-m-d') }}</td>
                                    <td>{{ ucfirst($transaction->type) }}</td>
                                    <td>{{ $transaction->description }}</td>
                                    <td class="text-end">${{ number_format($transaction->amount_cents / 100, 2) }}</td>
                                    <td>
                                        @if($transaction->reference_type === 'invoice' && $transaction->reference_id)
                                            <a href="{{ route('portal.admin.invoices.show', $transaction->reference_id) }}">Invoice #{{ $transaction->reference_id }}</a>
                                        @elseif($transaction->reference_type === 'pre_approval' && $transaction->reference_id)
                                            Pre-approval #{{ $transaction->reference_id }}
                                        @else
                                            {{ ucfirst($transaction->reference_type ?? 'N/A') }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No budget transactions yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-3 mb-3">
                <h6>Budget categories</h6>
                @if(empty($budgetCategories))
                    <p class="text-muted mb-0">No category breakdown available yet.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($budgetCategories as $category => $amount)
                            <div class="list-group-item px-0 py-3 border-top-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>{{ $category }}</span>
                                    <strong>${{ number_format($amount / 100, 2) }}</strong>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="card p-3 mb-3">
                <h6>Status timeline</h6>
                <ul class="list-group list-group-flush">
                    @forelse($participant->participantStatusHistories->take(10) as $history)
                        <li class="list-group-item">
                            <div class="small text-muted">{{ optional($history->created_at)->format('Y-m-d H:i') }} • {{ $history->changedBy?->name ?? 'System' }}</div>
                            <div>{{ ucfirst($history->previous_status ?? 'None') }} → {{ ucfirst($history->new_status) }}</div>
                            @if($history->notes)
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($history->notes, 100) }}</div>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No status history available.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card p-3 mb-3">
                <h6>Audit trail</h6>
                <ul class="list-group list-group-flush">
                    @forelse($auditEntries as $entry)
                        <li class="list-group-item">
                            <div class="small text-muted">{{ optional($entry->created_at)->format('Y-m-d H:i') }} • {{ $entry->user?->name ?? 'System' }}</div>
                            <div>{{ $entry->action }}</div>
                            @if($entry->description)
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($entry->description, 100) }}</div>
                            @endif
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No recent audit entries.</li>
                    @endforelse
                </ul>
            </div>

            <div class="card p-3 mb-3">
                <h6>Recent Care Notes</h6>
                <ul class="list-group list-group-flush">
                    @forelse($participant->careNotes->take(8) as $note)
                        <li class="list-group-item">{{ optional($note->shift_date)->format('Y-m-d') ?? '' }} — {{ \Illuminate\Support\Str::limit($note->care_summary ?? '—', 120) }}</li>
                    @empty
                        <li class="list-group-item text-muted">No care notes</li>
                    @endforelse
                </ul>
            </div>

            <div class="card p-3 mb-3">
                <h6>Pre-approvals</h6>
                <ul class="list-group list-group-flush">
                    @forelse($participant->preApprovalRequests->take(8) as $req)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $req->request_number }}</div>
                                <div class="small text-muted">{{ $req->service_type }} — {{ ucfirst($req->status) }}</div>
                            </div>
                            <div class="small text-muted">{{ $req->submitted_at?->format('Y-m-d') }}</div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No pre-approvals</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

