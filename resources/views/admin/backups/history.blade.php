@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1">Backup History</h2>
            <p class="text-muted mb-0">Review completed and failed backup jobs for database, file storage and audit archives.</p>
        </div>
        <a href="{{ route('portal.admin.backups.create') }}" class="btn btn-primary">Record New Backup</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Size</th>
                            <th>Location</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backups as $backup)
                            <tr>
                                <td>{{ $backup->backup_date->format('Y-m-d H:i') }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $backup->backup_type)) }}</td>
                                <td>{{ $backup->status }}</td>
                                <td>{{ number_format($backup->size / 1024 / 1024, 2) }} GB</td>
                                <td>{{ $backup->storage_location ?? 'n/a' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($backup->notes, 80) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-muted">No backup history records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $backups->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
