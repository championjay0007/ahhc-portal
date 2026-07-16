@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="dashboard-hero mb-4 p-4 p-lg-5">
        <div class="row align-items-center">
            <div class="col-lg">
                <span class="badge-soft mb-3 d-inline-flex align-items-center">Enterprise admin operations</span>
                <h2 class="fw-bold mb-2">Allegiance Heart & Home Care Self-Management Admin Dashboard</h2>
                <p class="text-muted mb-0">Monitor participants, workers, approvals, budgets, forms, incidents, and access governance from one secure command centre.</p>
            </div>
            <div class="col-lg-auto mt-3 mt-lg-0">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="{{ route('portal.admin.documents.create') }}" class="btn btn-primary btn-sm">Assign New Form</a>
                    <a href="{{ route('portal.admin.backups.dashboard') }}" class="btn btn-outline-secondary btn-sm">Backups</a>
                    <a href="{{ route('portal.admin.activity') }}" class="btn btn-outline-secondary btn-sm">Notifications</a>
                    <a href="{{ route('portal.admin.settings') }}" class="btn btn-outline-secondary btn-sm">2FA / MFA</a>
                    <a href="{{ route('portal.admin.settings') }}" class="btn btn-outline-secondary btn-sm">RBAC</a>
                    <a href="{{ route('portal.admin.activity') }}" class="btn btn-outline-secondary btn-sm">Audit Log</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-success">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Participants</p>
                            <h2 class="fw-bold mb-0">{{ $participantsCount }}</h2>
                            <small class="text-muted">{{ $participantsOnboarding }} onboarding</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
            <a href="{{ route('portal.admin.enquiries.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle bg-info">
                                <i class="bi bi-chat-left-text fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">New Enquiries</p>
                                <h2 class="fw-bold mb-0">{{ $newEnquiriesCount }}</h2>
                                <small class="text-muted">Review incoming requests</small>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-primary">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Workers/Suppliers</p>
                            <h2 class="fw-bold mb-0">{{ $workersCount }}</h2>
                            <small class="text-muted">{{ $workersPending }} pending approval</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
            <a href="{{ route('admin.worker_onboarding.index') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle bg-info">
                                <i class="bi bi-person-plus fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Worker Onboarding</p>
                                <h2 class="fw-bold mb-0">Open</h2>
                                <small class="text-muted">Review accounts and approvals</small>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm">
            <a href="{{ route('portal.admin.documents.create') }}" class="text-decoration-none">
                <div class="card dashboard-card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-circle bg-purple">
                                <i class="bi bi-file-earmark-text fs-4"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Forms to Sign</p>
                                <h2 class="fw-bold mb-0">{{ $pendingDocuments }}</h2>
                                <small class="text-muted">agreements / consents</small>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-primary">Assign a new form</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-sm">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-warning">
                            <i class="bi bi-receipt fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Invoice Queue</p>
                            @php
                                $invoiceLabel = $invoicePendingAmount >= 1000
                                    ? '$' . number_format($invoicePendingAmount / 1000, 1) . 'k'
                                    : '$' . number_format($invoicePendingAmount, 2);
                            @endphp
                            <h2 class="fw-bold mb-0">{{ $invoiceLabel }}</h2>
                            <small class="text-muted">{{ $submittedInvoices }} pending review</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-secondary">
                            <i class="bi bi-lock fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Committed</p>
                            <h2 class="fw-bold mb-0">${{ number_format(($totalCommittedCents ?? 0) / 100, 2) }}</h2>
                            <small class="text-muted">Funds reserved (pre-approvals & invoices)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-info">
                            <i class="bi bi-calendar-day fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Today's Shifts</p>
                            <h2 class="fw-bold mb-0">{{ $todaysShifts }}</h2>
                            <small class="text-muted">scheduled for today</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-primary">
                            <i class="bi bi-calendar-event fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Upcoming Shifts</p>
                            <h2 class="fw-bold mb-0">{{ $upcomingShifts }}</h2>
                            <small class="text-muted">scheduled forward</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-warning">
                            <i class="bi bi-exclamation-circle fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Missed Shifts</p>
                            <h2 class="fw-bold mb-0">{{ $missedShifts }}</h2>
                            <small class="text-muted">unresolved sessions</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle bg-secondary">
                            <i class="bi bi-clock-history fs-4"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Unconfirmed Shifts</p>
                            <h2 class="fw-bold mb-0">{{ $unconfirmedShifts }}</h2>
                            <small class="text-muted">awaiting worker confirmation</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> -->

    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2">Admin Control Centre: setup, assign, approve and monitor</h5>
                    <p class="text-muted small mb-4">Allegiance Heart & Home Care creates records, assigns forms, controls access and monitors compliance here.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100" style="background: #d1f2eb;">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-dark rounded-pill px-3">1. Set up participant</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li>• Profile + contacts</li>
                                    <li>• Quarter budget dates</li>
                                    <li>• Support/care plan</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100" style="background: #d6eaf8;">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-dark rounded-pill px-3">2. Set up worker</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li>• Mobile/participant/Allegiance Heart & Home Care</li>
                                    <li>• Compliance docs</li>
                                    <li>• Assign to participant</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 h-100" style="background: #e8daef;">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <span class="badge bg-dark rounded-pill px-3">3. Assign forms</span>
                                </div>
                                <ul class="list-unstyled small text-muted mb-0">
                                    <li>• Agreement + handbook</li>
                                    <li>• Privacy + consent</li>
                                    <li>• Worker declaration</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 rounded-3" style="background: #f8f9fa;">
                        <p class="mb-1"><strong>Admin actions:</strong> approve access, link workers, assign forms and check compliance.</p>
                        <small class="text-muted">Audit logs: who viewed, uploaded, edited, approved, signed or downloaded records.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-2">Live Quarterly Budget Dashboard</h5>
                    <p class="text-muted small mb-2">Live figures update as pre-approvals are committed and invoices are approved or paid.</p>
                    <p class="text-muted small mb-3">Shows opening quarter budget, committed funds (pre-approvals and pending invoices), approved/paid spend, and the live available balance.</p>

                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr class="text-muted small">
                                    <th>Participant</th>
                                    <th>Budget</th>
                                    <th>Used</th>
                                    <th>Committed</th>
                                    <th>Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalBudget = collect($budgetData)->sum('budget');
                                    $totalUsed = collect($budgetData)->sum('used');
                                    $totalCommitted = collect($budgetData)->sum('committed');
                                    $totalRemaining = collect($budgetData)->sum('remaining');
                                @endphp

                                <tr class="fw-bold">
                                    <td>Total</td>
                                    <td>${{ number_format($totalBudget, 0) }}</td>
                                    <td>${{ number_format($totalUsed, 0) }}</td>
                                    <td>${{ number_format($totalCommitted ?? 0, 0) }}</td>
                                    <td class="text-success">${{ number_format($totalRemaining, 0) }}</td>
                                </tr>
                                <tr><td colspan="5" class="py-2"></td></tr>

                                @forelse($budgetData as $budget)
                                    <tr>
                                        <td class="fw-bold">{{ $budget['name'] }}</td>
                                        <td>${{ number_format($budget['budget'], 0) }}</td>
                                        <td>${{ number_format($budget['used'], 0) }}</td>
                                        <td>${{ number_format($budget['committed'] ?? 0, 0) }}</td>
                                        <td class="text-success fw-bold">${{ number_format($budget['remaining'], 0) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="py-2">
                                            <div class="progress" style="height: 6px;">
                                                @php
                                                    $totalUsedForPercent = ($budget['used'] ?? 0) + ($budget['committed'] ?? 0);
                                                    $usedPercent = $budget['budget'] > 0 ? min(100, ($totalUsedForPercent / $budget['budget']) * 100) : 0;
                                                    $remainingPercent = max(0, 100 - $usedPercent);
                                                @endphp
                                                <div class="progress-bar" style="width: {{ $usedPercent }}%; background: {{ $usedPercent >= 80 ? '#dc3545' : '#20c997' }};"></div>
                                                @if($remainingPercent > 0)
                                                    <div class="progress-bar" style="width: {{ $remainingPercent }}%; background: #e9ecef;"></div>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-3">No participants with budgets</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 p-3 rounded-3" style="background: #fff3cd;">
                        <h6 class="fw-bold mb-2">Live budget formula</h6>
                        <ul class="list-unstyled small mb-0">
                            <li>Quarter budget + carry-over - approved/paid invoices</li>
                            <li>- committed pending items = live available balance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="mb-3 p-3 rounded-4 text-white" style="background: linear-gradient(135deg, #20c997, #0f5132);">
                        <h6 class="mb-0">Approval Workflows</h6>
                    </div>
                    <ul class="list-unstyled small mb-0 text-muted">
                        <li class="mb-2">• Pre-approval review queue</li>
                        <li class="mb-2">• Secondary approvals</li>
                        <li class="mb-2">• Escalation routing</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="mb-3 p-3 rounded-4 text-white" style="background: linear-gradient(135deg, #0d6efd, #0dcaf0);">
                        <h6 class="mb-0">Care Notes & Shifts</h6>
                    </div>
                    <ul class="list-unstyled small mb-0 text-muted">
                        <li class="mb-2">• Daily care note updates</li>
                        <li class="mb-2">• Shift handover visibility</li>
                        <li class="mb-2">• Worker availability checks</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="mb-3 p-3 rounded-4 text-white" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                        <h6 class="mb-0">Incidents & Quality</h6>
                    </div>
                    <ul class="list-unstyled small mb-0 text-muted">
                        <li class="mb-2">• Open incident monitoring</li>
                        <li class="mb-2">• Complaint follow-up</li>
                        <li class="mb-2">• Quality assurance review</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card dashboard-card h-100 border-0">
                <div class="card-body p-4">
                    <div class="mb-3 p-3 rounded-4 text-white" style="background: linear-gradient(135deg, #6f42c1, #343a40);">
                        <h6 class="mb-0">System Admin</h6>
                    </div>
                    <ul class="list-unstyled small mb-0 text-muted">
                        <li class="mb-2">• User access control</li>
                        <li class="mb-2">• Audit log review</li>
                        <li class="mb-2">• Settings and compliance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notification Alert Modal -->
@include('components.notification-alert-modal')

@endsection

@push('styles')
<style>
    .dashboard-hero {
        border-radius: 28px;
        border: 1px solid rgba(13, 110, 253, 0.08);
        background: white;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
    }

    .dashboard-card {
        border-radius: 24px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 28px 45px rgba(15, 23, 42, 0.1);
    }

    .nav-group-card {
        background: #f8fafc;
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 18px;
        padding: 1.1rem 1.1rem 1.2rem;
    }

    .nav-group-title {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.85rem;
    }

    .nav-group-title i {
        color: #2563eb;
    }

    .nav-group-links {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .nav-group-links a {
        color: #334155;
        text-decoration: none;
        font-size: 0.95rem;
        padding: 0.45rem 0.55rem;
        border-radius: 10px;
        transition: background-color 0.2s ease, color 0.2s ease;
    }

    .nav-group-links a:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .icon-circle {
        width: 60px;
        height: 60px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        color: white;
    }

    .badge-soft {
        border-radius: 999px;
        padding: 0.5rem 0.85rem;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        background: #e6f4ed;
        color: #0f5132;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }
</style>
@endpush
