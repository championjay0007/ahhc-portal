@extends('layouts.admin')

@section('content')
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Worker Compliance Dashboard</h1>
            <p class="text-muted">Monitor and manage worker compliance documents</p>
        </div>
    </div>

    <!-- Compliance Score Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3>Overall Compliance Score</h3>
                            <p class="text-muted mb-0">Percentage of workers with active compliance documents</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div style="font-size: 3rem; font-weight: bold; color: var(--bs-success);">
                                <span id="compliance-score">--</span>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-3 shadow-sm" style="border-color: var(--bs-warning) !important;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Expiring Soon</h5>
                    <p class="card-text">
                        <span id="expiring-count" class="h3 text-warning">0</span>
                        <small class="text-muted d-block">Within 30 days</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-3 shadow-sm" style="border-color: var(--bs-danger) !important;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Expired</h5>
                    <p class="card-text">
                        <span id="expired-count" class="h3 text-danger">0</span>
                        <small class="text-muted d-block">Past expiry date</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-3 shadow-sm" style="border-color: var(--bs-secondary) !important;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Missing</h5>
                    <p class="card-text">
                        <span id="missing-count" class="h3 text-secondary">0</span>
                        <small class="text-muted d-block">Not submitted</small>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-3 shadow-sm" style="border-color: var(--bs-info) !important;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Workers at Risk</h5>
                    <p class="card-text">
                        <span id="workers-at-risk" class="h3 text-info">0</span>
                        <small class="text-muted d-block">With issues</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards Row 2 -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-3 shadow-sm" style="border-color: var(--bs-primary) !important;">
                <div class="card-body">
                    <h5 class="card-title text-muted">Alerts</h5>
                    <p class="card-text">
                        <span id="alerts-count" class="h3 text-primary">0</span>
                        <small class="text-muted d-block">Total compliance alerts</small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs for Different Views -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="expiring-tab" data-bs-toggle="tab" href="#expiring-documents" role="tab">
                                Expiring Soon
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="expired-tab" data-bs-toggle="tab" href="#expired-documents" role="tab">
                                Expired
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="missing-tab" data-bs-toggle="tab" href="#missing-documents" role="tab">
                                Missing
                            </a>
                        </li>
                        <li class="nav-item ms-auto">
                            <a href="{{ route('portal.admin.compliance.export', ['type' => 'all']) }}" class="nav-link">
                                <i class="bi bi-download"></i> Export Report
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content">
                        <!-- Expiring Documents -->
                        <div class="tab-pane fade show active" id="expiring-documents">
                            <div class="table-responsive">
                                <table class="table table-hover" id="expiring-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Worker</th>
                                            <th>Document Type</th>
                                            <th>Expiry Date</th>
                                            <th>Days Remaining</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expiring-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Expired Documents -->
                        <div class="tab-pane fade" id="expired-documents">
                            <div class="table-responsive">
                                <table class="table table-hover" id="expired-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Worker</th>
                                            <th>Document Type</th>
                                            <th>Expiry Date</th>
                                            <th>Days Overdue</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="expired-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Missing Documents -->
                        <div class="tab-pane fade" id="missing-documents">
                            <div class="table-responsive">
                                <table class="table table-hover" id="missing-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Worker</th>
                                            <th>Document Type</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="missing-tbody">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">Loading...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadComplianceReport();
});

function loadDashboardStats() {
    fetch('{{ route("portal.admin.compliance.dashboard.stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('compliance-score').textContent = data.compliance_score ?? '--';
            document.getElementById('expiring-count').textContent = data.expiring_soon?.count ?? 0;
            document.getElementById('expired-count').textContent = data.expired?.count ?? 0;
            document.getElementById('missing-count').textContent = data.missing?.count ?? 0;
            document.getElementById('workers-at-risk').textContent = data.workers_with_issues?.count ?? 0;
            document.getElementById('alerts-count').textContent = data.alerts ?? 0;
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            document.getElementById('compliance-score').textContent = 'Err';
        });
}

function loadComplianceReport() {
    fetch('{{ route("portal.admin.compliance.report") }}')
        .then(response => response.json())
        .then(data => {
            loadExpiringDocuments(data.expiring_soon?.documents ?? []);
            loadExpiredDocuments(data.expired?.documents ?? []);
            loadMissingDocuments(data.missing?.documents ?? []);
        })
        .catch(error => console.error('Error loading report:', error));
}

function loadExpiringDocuments(documents) {
    const tbody = document.getElementById('expiring-tbody');
    tbody.innerHTML = documents.length ? documents.map(doc => `
        <tr>
            <td>
                <a href="/portal/admin/compliance/workers/${doc.worker_id}">${doc.worker_name}</a>
            </td>
            <td>${doc.document_type}</td>
            <td>${doc.expiry_date}</td>
            <td>
                <span class="badge bg-warning text-dark">${doc.days_remaining} days</span>
            </td>
            <td>
                <a href="/portal/admin/compliance/documents/${doc.id}" class="btn btn-sm btn-outline-primary">View</a>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="5" class="text-center text-muted py-4">No documents expiring soon</td></tr>';
}

function loadExpiredDocuments(documents) {
    const tbody = document.getElementById('expired-tbody');
    tbody.innerHTML = documents.length ? documents.map(doc => `
        <tr class="table-danger">
            <td>
                <a href="/portal/admin/compliance/workers/${doc.worker_id}">${doc.worker_name}</a>
            </td>
            <td>${doc.document_type}</td>
            <td>${doc.expiry_date}</td>
            <td>
                <span class="badge bg-danger">${Math.abs(doc.days_overdue)} days overdue</span>
            </td>
            <td>
                <a href="/portal/admin/compliance/documents/${doc.id}" class="btn btn-sm btn-outline-danger">Review</a>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="5" class="text-center text-muted py-4">No expired documents</td></tr>';
}

function loadMissingDocuments(documents) {
    const tbody = document.getElementById('missing-tbody');
    tbody.innerHTML = documents.length ? documents.map(doc => `
        <tr>
            <td>
                <a href="/portal/admin/compliance/workers/${doc.worker_id}">${doc.worker_name}</a>
            </td>
            <td>${doc.document_type}</td>
            <td>
                <span class="badge bg-secondary">Missing</span>
            </td>
            <td>
                <a href="/portal/admin/compliance/workers/${doc.worker_id}" class="btn btn-sm btn-outline-secondary">Manage</a>
            </td>
        </tr>
    `).join('') : '<tr><td colspan="4" class="text-center text-muted py-4">All documents submitted</td></tr>';
}
</script>
@endpush