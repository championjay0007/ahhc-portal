@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">Participant Applications</h1>
            <p class="text-muted">Review incoming applications and send onboarding invitations.</p>
        </div>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-primary">Refresh</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row gy-3 gx-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="new_application" {{ request('status') === 'new_application' ? 'selected' : '' }}>New Application</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name or email">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $application)
                        <tr>
                            <td>{{ $application->first_name }} {{ $application->last_name }}</td>
                            <td>{{ $application->email }}</td>
                            <td>{{ $application->phone }}</td>
                            <td>
                                <span class="badge bg-{{ $application->status === 'approved' ? 'success' : ($application->status === 'rejected' ? 'danger' : ($application->status === 'under_review' ? 'warning' : 'secondary')) }} text-white">
                                    {{ ucfirst(str_replace('_', ' ', $application->status)) }}
                                </span>
                            </td>
                            <td>{{ optional($application->submitted_at)->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3">
            {{ $applications->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
