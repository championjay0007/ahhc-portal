@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>My Budget</h2>
            <p class="text-muted">Quarterly budget, used amount, remaining balance and spending by care category.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary">{{ $currentQuarterLabel }}</span>
        </div>
    </div>

    @if(!empty($budgetAlerts))
        <div class="alert alert-warning mb-4">
            <h5 class="mb-2">Budget alerts</h5>
            <ul class="mb-0">
                @foreach($budgetAlerts as $alert)
                    <li>{{ is_array($alert) ? ($alert['message'] ?? json_encode($alert)) : $alert }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card portal-card p-4 mb-4">
        <h5>Budget composition</h5>
        <div class="row g-3 mt-3">
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted mb-1">Opening balance</p>
                    <h4>${{ number_format($openingBalanceCents / 100, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted mb-1">Carry-over</p>
                    <h4>${{ number_format($carryOverCents / 100, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted mb-1">Total available</p>
                    <h4>${{ number_format($limitBudgetCents / 100, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted mb-1">Remaining</p>
                    <h4 class="{{ $remainingBudgetCents < 0 ? 'text-danger' : '' }}">${{ number_format($remainingBudgetCents / 100, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card portal-card p-4 mb-4">
        <h5>Budget allocation & usage</h5>
        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted mb-1">Committed (pending approval)</p>
                    <h4>${{ number_format($committedCents / 100, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted mb-1">Approved (invoiced)</p>
                    <h4>${{ number_format($approvedCents / 100, 2) }}</h4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-light p-3">
                    <p class="text-muted mb-1">Paid</p>
                    <h4>${{ number_format($paidCents / 100, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card portal-card p-4 mb-4">
        <h5>Category spend summary</h5>
        <p class="text-muted">Track actual spend across your plan categories.</p>
        @if(empty($budgetCategorySpend))
            <p class="text-muted mb-0">No budget categories recorded yet.</p>
        @else
            <div class="list-group list-group-flush mt-3">
                @foreach($budgetCategorySpend as $category => $amount)
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

    <div class="card portal-card p-4 mb-4">
        <h5>Invoice summary</h5>
        <div class="row g-3 mt-3">
            <div class="col-md-4">
                <div class="card border-0 bg-warning-light p-3">
                    <p class="text-muted mb-1">Pending invoices (submitted)</p>
                    <h4>${{ number_format($submittedInvoicesCents / 100, 2) }}</h4>
                    <small class="text-muted">{{ $invoiceApprovalCounts['submitted'] ?? 0 }} invoice(s)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-info-light p-3">
                    <p class="text-muted mb-1">Approved invoices</p>
                    <h4>${{ number_format($approvedInvoicesCents / 100, 2) }}</h4>
                    <small class="text-muted">{{ $invoiceApprovalCounts['approved'] ?? 0 }} invoice(s)</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 bg-success-light p-3">
                    <p class="text-muted mb-1">Paid invoices</p>
                    <h4>${{ number_format($paidInvoicesCents / 100, 2) }}</h4>
                    <small class="text-muted">{{ $invoiceApprovalCounts['paid'] ?? 0 }} invoice(s)</small>
                </div>
            </div>
        </div>
    </div>

        <div class="mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Usage <strong>{{ number_format($budgetPercent, 1) }}%</strong></span>
                @if($overBudget)
                    <span class="badge bg-danger">Over budget</span>
                @else
                    <span class="badge bg-success">On track</span>
                @endif
            </div>
            <div class="progress" style="height: 12px;">
                <div class="progress-bar {{ $overBudget ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ $limitBudgetCents ? min(100, $budgetPercent) : 0 }}%;"></div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card portal-card p-4 h-100 border-start border-primary">
                <h6 class="mb-3">Pending commitments</h6>
                <p class="text-muted small mb-2">Pre-approvals awaiting review</p>
                <p class="display-6 mb-0">${{ number_format($pendingPreApprovalsCents / 100, 2) }}</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card portal-card p-4 h-100 border-start border-info">
                <h6 class="mb-3">Invoice pipeline</h6>
                <p class="text-muted small mb-2">Submitted, approved and paid invoices</p>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-warning text-dark">Submitted {{ $invoiceApprovalCounts['submitted'] ?? 0 }}</span>
                    <span class="badge bg-primary">Approved {{ $invoiceApprovalCounts['approved'] ?? 0 }}</span>
                    <span class="badge bg-success">Paid {{ $invoiceApprovalCounts['paid'] ?? 0 }}</span>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card portal-card p-4 h-100 border-start border-success">
                <h6 class="mb-3">Invoice spend</h6>
                <p class="text-muted small mb-2">Total approved and paid invoices</p>
                <p class="mb-1"><strong>Approved</strong> ${{ number_format($approvedInvoicesCents / 100, 2) }}</p>
                <p class="mb-0"><strong>Paid</strong> ${{ number_format($paidInvoicesCents / 100, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card portal-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Spending by care category</h5>
                    <span class="text-muted small">Worker role breakdown</span>
                </div>
                @if($invoiceCategorySpend->isEmpty())
                    <p class="text-muted">No invoice spending recorded yet.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($invoiceCategorySpend as $category => $amount)
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
        </div>

        <div class="col-lg-6">
            <div class="card portal-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Category commitments</h5>
                    <span class="text-muted small">Pre-approval service types</span>
                </div>
                @if($preApprovalCategorySpend->isEmpty())
                    <p class="text-muted">No pending service category spend yet.</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($preApprovalCategorySpend as $category => $amount)
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
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card portal-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Budget dashboard</h5>
                    <span class="text-muted small">Spreadsheet tracker summary</span>
                </div>
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Quarterly budget</td>
                            <td class="text-end">${{ number_format($limitBudgetCents / 100, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Budget used</td>
                            <td class="text-end">${{ number_format($usedBudgetCents / 100, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Remaining balance</td>
                            <td class="text-end">${{ number_format($remainingBudgetCents / 100, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Pending approvals</td>
                            <td class="text-end">${{ number_format($pendingPreApprovalsCents / 100, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Available after commitments</td>
                            <td class="text-end">${{ number_format($availableAfterPendingCents / 100, 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card portal-card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Invoice approval workflow</h5>
                    <span class="text-muted small">Track current invoice status</span>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Submitted</span>
                        <strong>{{ $invoiceApprovalCounts['submitted'] ?? 0 }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ ($invoiceApprovalCounts['submitted'] ?? 0) * 10 }}%;"></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Approved</span>
                        <strong>{{ $invoiceApprovalCounts['approved'] ?? 0 }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($invoiceApprovalCounts['approved'] ?? 0) * 10 }}%;"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Paid</span>
                        <strong>{{ $invoiceApprovalCounts['paid'] ?? 0 }}</strong>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ ($invoiceApprovalCounts['paid'] ?? 0) * 10 }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Mobile responsive styles for budget page */
        @media (max-width: 374px) {
            /* Header layout */
            .d-flex.justify-content-between.align-items-center {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .text-end {
                width: 100%;
            }

            h2 {
                font-size: 1.4rem;
            }

            p.text-muted {
                font-size: 0.85rem;
            }

            /* Cards */
            .card.portal-card {
                padding: 1rem !important;
                border-radius: 14px;
            }

            .card.p-4 {
                padding: 1rem !important;
            }

            .card.border-0.bg-light {
                padding: 0.85rem !important;
            }

            h5 {
                font-size: 0.95rem;
            }

            h6 {
                font-size: 0.9rem;
            }

            /* Grid adjustments */
            .row.g-3 {
                --bs-gutter-x: 0.75rem;
                --bs-gutter-y: 0.75rem;
            }

            .col-md-3, .col-md-4, .col-lg-4, .col-lg-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .row.g-3 > [class*="col-"] {
                padding-left: 0.375rem;
                padding-right: 0.375rem;
            }

            /* Budget cards */
            .card.border-0.bg-light h4 {
                font-size: 1.25rem;
                margin-bottom: 0.35rem;
            }

            .card.border-0.bg-light p {
                font-size: 0.8rem;
                margin-bottom: 0.5rem;
            }

            .card.border-0.bg-light small {
                font-size: 0.7rem;
            }

            /* Alert sizing */
            .alert {
                padding: 0.75rem;
                font-size: 0.85rem;
            }

            .alert h5 {
                font-size: 0.95rem;
                margin-bottom: 0.5rem;
            }

            .alert ul {
                margin-bottom: 0;
                padding-left: 1.25rem;
            }

            .alert li {
                font-size: 0.8rem;
                margin-bottom: 0.25rem;
            }

            /* List groups */
            .list-group-item {
                padding: 0.75rem 0;
                border: none;
                border-bottom: 1px solid rgba(14, 56, 99, 0.08);
            }

            .list-group-item:last-child {
                border-bottom: none;
            }

            .list-group-item span {
                font-size: 0.9rem;
            }

            .list-group-item strong {
                font-size: 0.95rem;
            }

            /* Badge sizing */
            .badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.55rem;
                margin-bottom: 0.25rem;
            }

            /* Progress bar */
            .progress {
                height: 10px !important;
                border-radius: 8px;
            }

            /* Border start indicator cards */
            .border-start {
                border-width: 3px !important;
                padding-left: 0.75rem !important;
            }

            /* Text styling */
            .display-6 {
                font-size: 1.5rem;
            }

            /* Utility classes */
            .d-flex.gap-2.flex-wrap {
                gap: 0.5rem;
            }

            /* Ensure no horizontal overflow */
            .card, .card-body {
                overflow-x: hidden;
            }
        }

        @media (max-width: 575px) and (min-width: 375px) {
            .col-md-3, .col-md-4, .col-lg-4, .col-lg-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .row.g-3 {
                --bs-gutter-x: 1rem;
                --bs-gutter-y: 1rem;
            }
        }

        /* Tablets and up - ensure proper layout */
        @media (max-width: 767px) {
            .col-lg-4, .col-lg-6 {
                max-width: 100%;
                flex: 0 0 100%;
            }

            .card.h-100 {
                min-height: auto;
            }
        }

        /* Touch-friendly targets */
        @media (hover: none) and (pointer: coarse) {
            button, a.btn, .btn {
                min-height: 44px;
                min-width: 44px;
            }

            .list-group-item {
                padding: 1rem 0;
            }
        }

        /* Landscape mode */
        @media (max-height: 600px) and (orientation: landscape) {
            .card.portal-card {
                padding: 0.75rem !important;
            }

            h5 {
                font-size: 0.85rem;
            }

            .row.g-3 {
                --bs-gutter-y: 0.5rem;
            }
        }

        /* Prevent overflow on all screens */
        * {
            max-width: 100%;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
@endsection
