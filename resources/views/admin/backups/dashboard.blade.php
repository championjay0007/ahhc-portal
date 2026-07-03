@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="dashboard-hero mb-4 p-4 p-lg-5">
        <div class="row align-items-center">
            <div class="col-lg">
                <span class="badge-soft mb-3 d-inline-flex align-items-center">Backup & recovery management</span>
                <h2 class="fw-bold mb-2">Disaster Recovery Command Centre</h2>
                <p class="text-muted mb-0">Track backup status, storage retention, restore history and recovery test compliance from one operational dashboard.</p>
            </div>
            <div class="col-lg-auto mt-3 mt-lg-0">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="{{ route('portal.admin.backups.create') }}" class="btn btn-primary btn-sm">Record Backup</a>
                    <a href="{{ route('portal.admin.backups.restores') }}" class="btn btn-outline-secondary btn-sm">Restore History</a>
                    <a href="{{ route('portal.admin.backups.tests') }}" class="btn btn-outline-secondary btn-sm">Recovery Tests</a>
                    <a href="{{ route('portal.admin.backups.compliance') }}" class="btn btn-outline-secondary btn-sm">Compliance Report</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1">Last successful backup</p>
                    <h3 class="fw-bold mb-1">{{ $lastSuccessful ? $lastSuccessful->backup_date->format('Y-m-d H:i') : 'None' }}</h3>
                    <p class="mb-0 text-muted">{{ $lastSuccessful ? ucwords(str_replace('_', ' ', $lastSuccessful->backup_type)) : 'No successful backup recorded' }}</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1">Failed backups</p>
                    <h3 class="fw-bold mb-1">{{ $failedBackups }}</h3>
                    <p class="mb-0 text-muted">Total failures recorded</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1">Storage usage</p>
                    <h3 class="fw-bold mb-1">{{ number_format($storageUsage / 1024 / 1024, 2) }} GB</h3>
                    <p class="mb-0 text-muted">Total recorded backup size</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1">Pending backup activity</p>
                    <h3 class="fw-bold mb-1">{{ $inProgressBackups }}</h3>
                    <p class="mb-0 text-muted">In progress records</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Recent Backup Activity</h5>
                    @if($recentBackups->isEmpty())
                        <p class="text-muted mb-0">No backup records have been added yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Size</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBackups as $backup)
                                        <tr>
                                            <td>{{ $backup->backup_date->format('Y-m-d H:i') }}</td>
                                            <td>{{ ucwords(str_replace('_', ' ', $backup->backup_type)) }}</td>
                                            <td>{{ $backup->status }}</td>
                                            <td>{{ number_format($backup->size / 1024 / 1024, 2) }} GB</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Recent Restoration & Test Log</h5>
                    <div class="row gy-3">
                        <div class="col-12">
                            <p class="text-muted small mb-2">Recent restores</p>
                            <ul class="list-unstyled mb-3">
                                @foreach($recentRestores as $restore)
                                    <li class="mb-2">
                                        <strong>{{ $restore->restore_date->format('Y-m-d') }}</strong>
                                        — {{ $restore->status }}
                                        @if($restore->backupRecord)
                                            ({{ ucwords(str_replace('_', ' ', $restore->backupRecord->backup_type)) }})
                                        @endif
                                    </li>
                                @endforeach
                                @if($recentRestores->isEmpty())
                                    <li class="text-muted">No restoration logs yet.</li>
                                @endif
                            </ul>
                        </div>
                        <div class="col-12">
                            <p class="text-muted small mb-2">Recent recovery tests</p>
                            <ul class="list-unstyled mb-0">
                                @foreach($recentTests as $test)
                                    <li class="mb-2">
                                        <strong>{{ $test->test_date->format('Y-m-d') }}</strong>
                                        — {{ $test->status }}
                                    </li>
                                @endforeach
                                @if($recentTests->isEmpty())
                                    <li class="text-muted">No disaster recovery tests recorded yet.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Disaster recovery cadence</h5>
                    <p class="text-muted mb-0">Last recovery test was recorded on <strong>{{ $lastTest ? $lastTest->test_date->format('Y-m-d') : 'no record' }}</strong>.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
