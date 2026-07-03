@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1">Restore History</h2>
            <p class="text-muted mb-0">Track restoration activity and ensure recovery events are fully documented.</p>
        </div>
        <a href="{{ route('portal.admin.backups.restores.create') }}" class="btn btn-primary">Record Restore</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Restore date</th>
                            <th>Status</th>
                            <th>Backup source</th>
                            <th>Initiated by</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($restores as $restore)
                            <tr>
                                <td>{{ $restore->restore_date->format('Y-m-d H:i') }}</td>
                                <td>{{ $restore->status }}</td>
                                <td>{{ $restore->backupRecord ? ucwords(str_replace('_', ' ', $restore->backupRecord->backup_type)) : 'n/a' }}</td>
                                <td>{{ $restore->initiatedBy?->name ?? 'system' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($restore->notes, 80) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted">No restore records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $restores->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
