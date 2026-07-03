@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">User profile</h2>
            <p class="text-muted mb-0">Review user details, account status, and related participant or worker records.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('portal.admin.users') }}" class="btn btn-outline-secondary">Back to users</a>
            <a href="{{ route('portal.admin.users.edit', $user) }}" class="btn btn-primary">Edit user</a>
            <form method="POST" action="{{ route('portal.admin.users.dashboard.login', $user) }}" class="m-0">
                @csrf
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-outline-success">Force dashboard login</button>
            </form>
            <form method="POST" action="{{ route('portal.admin.users.destroy', $user) }}" class="m-0">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this user? This action cannot be undone.')">Delete user</button>
            </form>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Account details</h5>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Name</label>
                            <div class="form-control-plaintext">{{ $user->name }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Email</label>
                            <div class="form-control-plaintext">{{ $user->email }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Phone</label>
                            <div class="form-control-plaintext">{{ $user->phone ?? '-' }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Role</label>
                            <div class="form-control-plaintext text-capitalize">{{ $user->role }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Status</label>
                            <div class="form-control-plaintext">
                                <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'secondary' }} text-capitalize">{{ $user->status }}</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Created</label>
                            <div class="form-control-plaintext">{{ $user->created_at->format('M j, Y h:ia') }}</div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Last updated</label>
                            <div class="form-control-plaintext">{{ $user->updated_at->format('M j, Y h:ia') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($user->participant)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="mb-3">Participant record</h5>
                        <dl class="row">
                            <dt class="col-sm-4 text-muted">Participant number</dt>
                            <dd class="col-sm-8">{{ $user->participant->participant_number }}</dd>
                            <dt class="col-sm-4 text-muted">Name</dt>
                            <dd class="col-sm-8">{{ $user->participant->first_name }} {{ $user->participant->last_name }}</dd>
                            <dt class="col-sm-4 text-muted">Status</dt>
                            <dd class="col-sm-8">{{ $user->participant->status }}</dd>
                            <dt class="col-sm-4 text-muted">Assigned support</dt>
                            <dd class="col-sm-8">{{ optional($user->participant->supportPerson)->first_name ? $user->participant->supportPerson->first_name . ' ' . $user->participant->supportPerson->last_name : 'Unassigned' }}</dd>
                        </dl>
                    </div>
                </div>
            @endif

            @if($user->worker)
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="mb-3">Worker record</h5>
                        <dl class="row">
                            <dt class="col-sm-4 text-muted">Worker number</dt>
                            <dd class="col-sm-8">{{ $user->worker->worker_number }}</dd>
                            <dt class="col-sm-4 text-muted">Name</dt>
                            <dd class="col-sm-8">{{ $user->worker->first_name }} {{ $user->worker->last_name }}</dd>
                            <dt class="col-sm-4 text-muted">Status</dt>
                            <dd class="col-sm-8">{{ $user->worker->status }}</dd>
                            <dt class="col-sm-4 text-muted">Role type</dt>
                            <dd class="col-sm-8">{{ $user->worker->role_type }}</dd>
                        </dl>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="mb-3">Manage status</h5>

                    <form method="POST" action="{{ route('portal.admin.users.status', $user) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Account status</label>
                            <select name="status" class="form-select">
                                <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update status</button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="mb-3">Security settings</h5>
                    <div class="mb-3">
                        <label class="form-label">Two-factor authentication</label>
                        <div class="form-control-plaintext">
                            @if($user->mfa_enabled)
                                <span class="badge bg-success">Enabled</span>
                                <div class="text-muted small mt-1">Enrolled at {{ optional($user->mfa_enrolled_at)->format('M j, Y h:ia') ?? 'unknown' }}</div>
                            @else
                                <span class="badge bg-secondary">Disabled</span>
                            @endif
                        </div>
                    </div>
                    @if($user->mfa_enabled)
                        <form method="POST" action="{{ route('portal.admin.users.mfa.reset', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100">Reset user's MFA</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="mb-3">Activity</h5>
                    @if($user->auditLogs->isEmpty())
                        <div class="text-muted">No recent audit log entries for this user.</div>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($user->auditLogs->take(5) as $log)
                                <li class="list-group-item py-2">
                                    <div class="small text-muted">{{ $log->created_at->diffForHumans() }}</div>
                                    <div>{{ \Illuminate\Support\Str::limit($log->action, 80) }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
