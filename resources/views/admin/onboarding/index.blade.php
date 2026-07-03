@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">Onboarding Submissions</h1>
            <p class="text-muted">Review participant submissions and manage approval decisions.</p>
        </div>
        <a href="{{ route('admin.onboarding.index') }}" class="btn btn-outline-primary">Refresh</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row gy-3 gx-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="changes_requested" {{ request('status') === 'changes_requested' ? 'selected' : '' }}>Changes Requested</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Search participant</label>
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
                        <th>Participant</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                        <tr>
                            <td>{{ $submission->participant->first_name }} {{ $submission->participant->last_name }}</td>
                            <td>{{ $submission->participant->email }}</td>
                            <td>
                                <span class="badge bg-{{ $submission->status === 'approved' ? 'success' : ($submission->status === 'rejected' ? 'danger' : ($submission->status === 'changes_requested' ? 'warning' : 'secondary')) }} text-white">
                                    {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                                </span>
                            </td>
                            <td>{{ optional($submission->submitted_at)->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.onboarding.show', $submission) }}" class="btn btn-sm btn-outline-primary">Review</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No onboarding submissions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white py-3">
            {{ $submissions->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
