@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Data Retention</h2>
            <p class="text-muted mb-0">Set retention periods for audit logs, backup records and recovery test history.</p>
        </div>
        <a href="{{ route('portal.admin.system.dashboard') }}" class="btn btn-outline-secondary">System dashboard</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('portal.admin.system.data_retention.update') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Audit log retention (days)</label>
                    <input type="number" name="audit_logs_days" class="form-control" min="0" value="{{ old('audit_logs_days', $settings['audit_logs_days']) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Backup record retention (days)</label>
                    <input type="number" name="backup_records_days" class="form-control" min="0" value="{{ old('backup_records_days', $settings['backup_records_days']) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Restore record retention (days)</label>
                    <input type="number" name="restore_records_days" class="form-control" min="0" value="{{ old('restore_records_days', $settings['restore_records_days']) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Disaster recovery test retention (days)</label>
                    <input type="number" name="disaster_recovery_tests_days" class="form-control" min="0" value="{{ old('disaster_recovery_tests_days', $settings['disaster_recovery_tests_days']) }}">
                </div>

                <button type="submit" class="btn btn-primary">Save retention settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
