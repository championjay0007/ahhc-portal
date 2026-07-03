@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">System Health</h2>
            <p class="text-muted mb-0">Monitor storage, queue status, failed jobs, and backup health in real time.</p>
        </div>
        <a href="{{ route('portal.admin.system.dashboard') }}" class="btn btn-outline-secondary">Back to system admin</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="mb-3">Storage usage</h5>
                <p class="mb-0">{{ number_format($healthMetrics['storage_usage_bytes'] / 1024 / 1024, 2) }} MB used.</p>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="mb-3">Queue status</h5>
                <p class="mb-2">Driver: <strong>{{ $healthMetrics['queue_driver'] }}</strong></p>
                <p class="mb-0">Pending jobs: <strong>{{ $healthMetrics['pending_jobs'] ?? 'N/A' }}</strong></p>
                <p class="mb-0">Failed jobs: <strong>{{ $healthMetrics['failed_jobs'] ?? 'N/A' }}</strong></p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="mb-3">Backup status</h5>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <div class="p-3 rounded-3 bg-light">
                            <p class="text-muted small mb-1">Last successful</p>
                            <h4 class="fw-bold mb-0">{{ $healthMetrics['backup']['last_successful'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 rounded-3 bg-light">
                            <p class="text-muted small mb-1">Failed backups</p>
                            <h4 class="fw-bold mb-0">{{ $healthMetrics['backup']['failed'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 rounded-3 bg-light">
                            <p class="text-muted small mb-1">In progress</p>
                            <h4 class="fw-bold mb-0">{{ $healthMetrics['backup']['in_progress'] }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="p-3 rounded-3 bg-light">
                            <p class="text-muted small mb-1">Total backups</p>
                            <h4 class="fw-bold mb-0">{{ $healthMetrics['backup']['total'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
