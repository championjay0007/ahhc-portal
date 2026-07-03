@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Assignments</h4>
                <p class="text-muted mb-0">Manage participant-worker assignments, status and support coverage.</p>
            </div>
            <div>
                <a href="{{ route('portal.admin.assignments.create') }}" class="btn btn-sm btn-primary">New assignment</a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('portal.admin.assignments') }}" class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input name="search" value="{{ request('search') }}" class="form-control" placeholder="Search participants, workers or assignment type">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All statuses</option>
                            <option value="active"{{ request('status') === 'active' ? ' selected' : '' }}>Active</option>
                            <option value="inactive"{{ request('status') === 'inactive' ? ' selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Assignment type</label>
                        <select name="assignment_type" class="form-select">
                            <option value="">All types</option>
                            <option value="primary"{{ request('assignment_type') === 'primary' ? ' selected' : '' }}>Primary</option>
                            <option value="secondary"{{ request('assignment_type') === 'secondary' ? ' selected' : '' }}>Secondary</option>
                            <option value="temporary"{{ request('assignment_type') === 'temporary' ? ' selected' : '' }}>Temporary</option>
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
                            <th>Participant</th>
                            <th>Worker</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Primary</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->id }}</td>
                                <td>{{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}</td>
                                <td>{{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}</td>
                                <td>{{ ucfirst($assignment->assignment_type) }}</td>
                                <td>{{ ucfirst($assignment->status) }}</td>
                                <td>{{ optional($assignment->start_date)->format('Y-m-d') }}</td>
                                <td>{{ optional($assignment->end_date)->format('Y-m-d') ?? 'Ongoing' }}</td>
                                <td>{{ $assignment->is_primary ? 'Yes' : 'No' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('portal.admin.assignments.show', $assignment) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('portal.admin.assignments.edit', $assignment) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">{{ $assignments->links() }}</div>
    </div>
@endsection
