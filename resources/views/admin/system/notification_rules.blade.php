@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Notification Rules</h2>
            <p class="text-muted mb-0">Configure event-driven notification behavior for system alerts.</p>
        </div>
        <a href="{{ route('portal.admin.system.dashboard') }}" class="btn btn-outline-secondary">System dashboard</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('portal.admin.system.notification_rules.update') }}">
                @csrf

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="notify_on_backup_failure" name="notify_on_backup_failure" {{ $settings['notify_on_backup_failure'] === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_on_backup_failure">Notify on backup failure</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="notify_on_failed_jobs" name="notify_on_failed_jobs" {{ $settings['notify_on_failed_jobs'] === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_on_failed_jobs">Notify on failed queue jobs</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="notify_on_new_user" name="notify_on_new_user" {{ $settings['notify_on_new_user'] === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_on_new_user">Notify on new user creation</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" value="1" id="notify_on_user_status_change" name="notify_on_user_status_change" {{ $settings['notify_on_user_status_change'] === '1' ? 'checked' : '' }}>
                    <label class="form-check-label" for="notify_on_user_status_change">Notify on user status changes</label>
                </div>

                <button type="submit" class="btn btn-primary">Save notification rules</button>
            </form>
        </div>
    </div>
</div>
@endsection
