@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1">Backup Compliance Report</h2>
            <p class="text-muted mb-0">Review backup success rates, coverage by backup type, and recent failed backups.</p>
        </div>
        <a href="{{ route('portal.admin.backups.dashboard') }}" class="btn btn-outline-secondary">Dashboard</a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1">Total backups</p>
                    <h2 class="fw-bold mb-0">{{ $totalBackups }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1">Successful backups</p>
                    <h2 class="fw-bold mb-0">{{ $successfulBackups }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <p class="text-muted small mb-1">Failure rate</p>
                    <h2 class="fw-bold mb-0">{{ $failureRate }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Last successful backup by type</h5>
            <ul class="list-group list-group-flush">
                @foreach($lastSuccessfulByType as $type => $timestamp)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-0">
                        <span>{{ ucwords(str_replace('_', ' ', $type)) }}</span>
                        <span class="text-muted small">{{ $timestamp }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-3">Recent backup failures</h5>
            @if($recentFailures->isEmpty())
                <p class="text-muted mb-0">No recent failures.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentFailures as $backup)
                                <tr>
                                    <td>{{ $backup->backup_date->format('Y-m-d H:i') }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $backup->backup_type)) }}</td>
                                    <td>{{ $backup->storage_location ?? 'n/a' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($backup->notes, 120) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
