@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">User Management</h2>
            <p class="text-muted mb-0">View, search, and manage portal users across participant, worker, and admin roles.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('portal.admin.users.create') }}" class="btn btn-primary">Create user</a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('portal.admin.users') }}" class="row gx-2 gy-3 align-items-end">
                <div class="col-sm-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Name, email, phone, role">
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        <option value="participant" {{ request('role') === 'participant' ? 'selected' : '' }}>Participant</option>
                        <option value="worker" {{ request('role') === 'worker' ? 'selected' : '' }}>Worker</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <div class="small text-muted">{{ $user->phone ?? 'No phone' }}</div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td class="text-capitalize">{{ $user->role }}</td>
                                <td>
                                    <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }} text-capitalize">{{ $user->status }}</span>
                                </td>
                                <td>{{ $user->created_at->format('M j, Y') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('portal.admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    <a href="{{ route('portal.admin.users.edit', $user) }}" class="btn btn-sm btn-outline-secondary ms-1">Edit</a>
                                    <form method="POST" action="{{ route('portal.admin.users.destroy', $user) }}" class="d-inline-block ms-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user? This action cannot be undone.')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer border-top py-3">
                @include('components.admin-pagination', ['paginator' => $users->withQueryString()])
            </div>
        @endif
    </div>
</div>
@endsection
