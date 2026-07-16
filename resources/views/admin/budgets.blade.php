@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Budgets</h4>
                <p class="text-muted mb-0">Track participant quarterly budget availability, commitments, approved spend, and remaining balances.</p>
                <p class="text-muted small mb-0">Current quarter: {{ $currentQuarterLabel ?? 'N/A' }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.admin.participants') }}" class="btn btn-sm btn-outline-secondary">View participants</a>
                <a href="{{ route('portal.admin.invoices') }}" class="btn btn-sm btn-outline-secondary">View invoices</a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <div class="text-muted small">Total available</div>
                    <div class="fw-bold display-6">${{ number_format(($totalBudget ?? 0) / 100, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <div class="text-muted small">Committed</div>
                    <div class="fw-bold display-6">${{ number_format(($totalCommitted ?? 0) / 100, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <div class="text-muted small">Used</div>
                    <div class="fw-bold display-6">${{ number_format(($totalUsed ?? 0) / 100, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 h-100">
                    <div class="text-muted small">Remaining budget</div>
                    <div class="fw-bold display-6">${{ number_format(($totalRemaining ?? 0) / 100, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Committed shows the remaining invoice commitment balance still attached to submitted invoices. Paid shows the portion already paid out, while Used is the total approved and paid invoice amount.
                </p>
                <form method="GET" action="{{ route('portal.admin.budgets') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, number or email">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="active"{{ request('status') === 'active' ? ' selected' : '' }}>Active</option>
                            <option value="inactive"{{ request('status') === 'inactive' ? ' selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Participant #</th>
                            <th>Status</th>
                            <th class="text-end">Quarter total</th>
                            <th class="text-end">Committed</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Used</th>
                            <th class="text-end">Approved</th>
                            <th class="text-end">Remaining</th>
                            <th class="text-end">Utilization</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $participant)
                            @php
                                $total = $participant->current_budget ?? 0;
                                $committed = $participant->committed ?? 0;
                                $approved = $participant->approved_spend ?? 0;
                                $paid = $participant->paid_spend ?? 0;
                                $used = $approved + $paid;
                                $remaining = $participant->remaining_budget ?? 0;
                                $utilization = $participant->utilization ?? 0;
                            @endphp
                            <tr>
                                <td>{{ $participant->id }}</td>
                                <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                                <td>{{ $participant->participant_number }}</td>
                                <td>{{ ucfirst($participant->status) }}</td>
                                <td class="text-end">${{ number_format($total / 100, 2) }}</td>
                                <td class="text-end">${{ number_format($committed / 100, 2) }}</td>
                                <td class="text-end">${{ number_format($paid / 100, 2) }}</td>
                                <td class="text-end">${{ number_format($used / 100, 2) }}</td>
                                <td class="text-end">${{ number_format($approved / 100, 2) }}</td>
                                <td class="text-end">${{ number_format($remaining / 100, 2) }}</td>
                                <td class="text-end">{{ $utilization }}%</td>
                                <td class="text-end">
                                    @if(!empty($participant->budget))
                                        <a href="{{ route('budgets.show', $participant->budget) }}" class="btn btn-sm btn-outline-primary">View budget</a>
                                    @else
                                        <a href="{{ route('portal.admin.participants.show', $participant) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No participants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $participants->links() }}</div>
    </div>
@endsection
