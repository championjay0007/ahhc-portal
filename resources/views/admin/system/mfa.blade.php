@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">MFA Management</h2>
            <p class="text-muted mb-0">Manage enrolled users and reset multi-factor authentication for accounts.</p>
        </div>
        <a href="{{ route('portal.admin.settings') }}" class="btn btn-outline-secondary">Security settings</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>MFA</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
                                <td class="text-capitalize">{{ $user->status }}</td>
                                <td>
                                    @if($user->mfa_enabled)
                                        <span class="badge bg-success">Enabled</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($user->mfa_enabled)
                                        <form method="POST" action="{{ route('portal.admin.users.mfa.reset', $user) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning">Reset MFA</button>
                                        </form>
                                    @else
                                        <span class="text-muted small">No action</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
