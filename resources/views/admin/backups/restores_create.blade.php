@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1">Record Restore</h2>
            <p class="text-muted mb-0">Document restore actions and who initiated the recovery.</p>
        </div>
        <a href="{{ route('portal.admin.backups.restores') }}" class="btn btn-outline-secondary">View Restore History</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('portal.admin.backups.restores.store') }}" method="post">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Backup source</label>
                        <select name="backup_record_id" class="form-select">
                            <option value="">Select a backup record</option>
                            @foreach($backups as $backup)
                                <option value="{{ $backup->id }}">{{ $backup->backup_date->format('Y-m-d') }} — {{ ucwords(str_replace('_', ' ', $backup->backup_type)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Restore date</label>
                        <input type="datetime-local" name="restore_date" class="form-control" value="{{ old('restore_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save restore log</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
