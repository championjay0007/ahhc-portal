@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Workers</h4>
                <p class="text-muted mb-0">Manage worker profiles, assignments and compliance details.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.admin.assignments') }}" class="btn btn-sm btn-outline-secondary">View assignments</a>
                <a href="{{ route('portal.admin.workers.create') }}" class="btn btn-sm btn-primary">Add worker</a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('portal.admin.workers') }}" class="row g-2 align-items-end">
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
                            <th>Worker #</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workers as $worker)
                            <tr>
                                <td>{{ $worker->id }}</td>
                                <td>{{ $worker->first_name }} {{ $worker->last_name }}</td>
                                <td>{{ $worker->worker_number }}</td>
                                <td>{{ $worker->email }}</td>
                                <td>{{ $worker->phone }}</td>
                                <td>{{ ucfirst($worker->status) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('portal.admin.workers.show', $worker) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('portal.admin.workers.edit', $worker) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    @if($worker->user)
                                        <form method="POST" action="{{ route('portal.admin.users.dashboard.login', $worker->user) }}" class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="confirm" value="1">
                                            <button type="submit" class="btn btn-sm btn-outline-success">Force dashboard login</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No workers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            @include('components.admin-pagination', ['paginator' => $workers])
        </div>
    </div>
@endsection
