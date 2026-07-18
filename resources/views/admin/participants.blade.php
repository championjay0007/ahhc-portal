@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Participants</h4>
                <p class="text-muted mb-0">Manage participant records, budgets, assignments and portal access.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.admin.budgets') }}" class="btn btn-sm btn-outline-secondary">View budgets</a>
                <a href="{{ route('portal.admin.participants.create') }}" class="btn btn-sm btn-primary">Add participant</a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('portal.admin.participants') }}" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search</label>
                        <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, number, email or phone">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="active"{{ request('status') === 'active' ? ' selected' : '' }}>Active</option>
                            <option value="inactive"{{ request('status') === 'inactive' ? ' selected' : '' }}>Inactive</option>
                            <option value="onboarding"{{ request('status') === 'onboarding' ? ' selected' : '' }}>Onboarding</option>
                            <option value="closed"{{ request('status') === 'closed' ? ' selected' : '' }}>Closed</option>
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
                            <th>Available</th>
                            <th>Used</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participants as $p)
                            @php
                                $remaining = max(0, ($p->remaining_budget ?? 0));
                            @endphp
                            <tr>
                                <td>{{ $p->id }}</td>
                                <td>{{ $p->first_name }} {{ $p->last_name }}</td>
                                <td>{{ $p->participant_number }}</td>
                                <td>${{ number_format(($p->budget_limit_cents ?? 0) / 100, 2) }}</td>
                                <td>${{ number_format(($p->current_budget_used_cents ?? 0) / 100, 2) }}</td>
                                <td>${{ number_format($remaining / 100, 2) }}</td>
                                <td>{{ ucfirst($p->status) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('portal.admin.participants.show', $p) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('portal.admin.participants.edit', $p) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    @if($p->user)
                                        <form method="POST" action="{{ route('portal.admin.users.dashboard.login', $p->user) }}" class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="confirm" value="1">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Force dashboard login</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No participants found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            @include('components.admin-pagination', ['paginator' => $participants])
        </div>
    </div>
@endsection
