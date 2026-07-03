@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1">Record Backup</h2>
            <p class="text-muted mb-0">Add a new backup event for database, file storage or audit log backups.</p>
        </div>
        <a href="{{ route('portal.admin.backups.history') }}" class="btn btn-outline-secondary">View Backup History</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('portal.admin.backups.store') }}" method="post">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Backup type</label>
                        <select name="backup_type" class="form-select">
                            @foreach($backupTypes as $type)
                                <option value="{{ $type }}">{{ ucwords(str_replace('_', ' ', $type)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Backup date</label>
                        <input type="datetime-local" name="backup_date" class="form-control" value="{{ old('backup_date') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Backup size (bytes)</label>
                        <input type="number" name="size" class="form-control" value="{{ old('size') }}" min="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Storage location</label>
                        <input type="text" name="storage_location" class="form-control" value="{{ old('storage_location') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save backup</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
