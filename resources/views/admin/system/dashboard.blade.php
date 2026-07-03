@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">System Administration Dashboard</h2>
            <p class="text-muted mb-0">Manage users, security, notifications, retention, backups and system health from one dedicated control panel.</p>
        </div>
        <a href="{{ route('portal.admin.system.health') }}" class="btn btn-outline-secondary">System Health</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div>
                    <span class="text-muted small">Users & Roles</span>
                    <h3 class="fw-bold mt-2">{{ $usersCount }}</h3>
                    <p class="text-muted mb-0">Active accounts across roles.</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('portal.admin.system.users') }}" class="btn btn-sm btn-primary">Manage users</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div>
                    <span class="text-muted small">Permission groups</span>
                    <h3 class="fw-bold mt-2">{{ $permissionGroupsCount }}</h3>
                    <p class="text-muted mb-0">Configured access groups and permissions.</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('portal.admin.system.permission_groups') }}" class="btn btn-sm btn-primary">Manage groups</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div>
                    <span class="text-muted small">Backup health</span>
                    <h3 class="fw-bold mt-2">{{ $backupSummary['failed'] }} failed</h3>
                    <p class="text-muted mb-0">Last successful backup: {{ $backupSummary['last_successful'] }}</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('portal.admin.backups.dashboard') }}" class="btn btn-sm btn-primary">Open backup management</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div>
                    <span class="text-muted small">MFA Management</span>
                    <h3 class="fw-bold mt-2">{{ $usersCount }}</h3>
                    <p class="text-muted mb-0">Review all user MFA enrollment states.</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('portal.admin.system.mfa') }}" class="btn btn-sm btn-primary">Manage MFA</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div>
                    <span class="text-muted small">Security settings</span>
                    <h3 class="fw-bold mt-2">Portal config</h3>
                    <p class="text-muted mb-0">Control MFA, portal configuration and security enforcement.</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('portal.admin.settings') }}" class="btn btn-sm btn-primary">Open security settings</a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div>
                    <span class="text-muted small">Notifications</span>
                    <h3 class="fw-bold mt-2">Rule engine</h3>
                    <p class="text-muted mb-0">Define delivery and alerting policy for critical system events.</p>
                </div>
                <div class="mt-3">
                    <a href="{{ route('portal.admin.system.notification_rules') }}" class="btn btn-sm btn-primary">Edit notification rules</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="mb-3">Role count</h5>
                <div class="table-responsive">
                    <table class="table table-borderless mb-0">
                        <thead>
                            <tr>
                                <th>Role</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roleBreakdown as $roleRow)
                                <tr>
                                    <td class="text-capitalize">{{ str_replace('_', ' ', $roleRow->role) }}</td>
                                    <td>{{ $roleRow->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="mb-3">System health summary</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <strong>Storage usage:</strong>
                        <div class="text-muted">{{ number_format($healthMetrics['storage_usage_bytes'] / 1024 / 1024, 2) }} MB</div>
                    </li>
                    <li class="mb-3">
                        <strong>Queue driver:</strong>
                        <div class="text-muted">{{ $healthMetrics['queue_driver'] }}</div>
                    </li>
                    <li class="mb-3">
                        <strong>Pending jobs:</strong>
                        <div class="text-muted">{{ $healthMetrics['pending_jobs'] ?? 'N/A' }}</div>
                    </li>
                    <li class="mb-3">
                        <strong>Failed jobs:</strong>
                        <div class="text-muted">{{ $healthMetrics['failed_jobs'] ?? 'N/A' }}</div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
