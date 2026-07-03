@extends('layouts.portal')

@section('title', 'My Shifts')

@section('content')
    <div class="portal-page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1>My Shifts</h1>
            <p class="text-muted mb-0">Manage your scheduled services and attendance.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($shifts->isEmpty())
        <div class="card p-4 text-center text-muted">
            <i class="bi bi-calendar-x" style="font-size: 2rem;"></i>
            <p class="mb-0 mt-3">No scheduled shifts found.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Participant</th>
                        <th>Service</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shifts as $shift)
                        <tr>
                            <td>{{ $shift->shift_date?->format('d M Y') }}</td>
                            <td>{{ $shift->participant?->first_name }} {{ $shift->participant?->last_name }}</td>
                            <td>{{ $shift->service_type ?? 'Service' }}</td>
                            <td>{{ $shift->start_time }} &ndash; {{ $shift->end_time }}</td>
                            <td>
                                <span class="badge bg-{{ $shift->status === 'completed' ? 'success' : ($shift->status === 'cancelled' ? 'danger' : ($shift->status === 'in_progress' ? 'info' : ($shift->status === 'confirmed' ? 'primary' : ($shift->status === 'missed' ? 'warning' : 'secondary')))) }}">
                                    {{ ucfirst(str_replace('_', ' ', $shift->status)) }}
                                </span>
                                @if($shift->started_at)
                                    <div class="small text-muted mt-1">Started: {{ $shift->started_at->format('d M Y H:i') }}</div>
                                @endif
                                @if($shift->completed_at)
                                    <div class="small text-muted">Completed: {{ $shift->completed_at->format('d M Y H:i') }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($shift->status === 'scheduled')
                                    <form action="{{ route('portal.worker.shifts.confirm', $shift) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Confirm</button>
                                    </form>
                                @endif

                                @if(in_array($shift->status, ['scheduled', 'confirmed'], true))
                                    <form action="{{ route('portal.worker.shifts.start', $shift) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Start</button>
                                    </form>
                                @endif

                                @if($shift->status === 'in_progress')
                                    <form action="{{ route('portal.worker.shifts.complete', $shift) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success">Complete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
