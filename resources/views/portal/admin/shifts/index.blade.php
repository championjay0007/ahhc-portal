@extends('layouts.portal')

@section('title', 'Shift Scheduling')

@section('content')
    <div class="portal-page-header d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1>Shift Scheduling</h1>
            <p class="text-muted mb-0">Create, assign and manage participant service shifts.</p>
        </div>
        <a href="{{ route('portal.admin.shifts.create') }}" class="btn btn-primary">Create Shift</a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Participant</label>
                    <select name="participant_id" class="form-select">
                        <option value="">All participants</option>
                        @foreach($participants as $participant)
                            <option value="{{ $participant->id }}" @selected(request('participant_id') == $participant->id)>
                                {{ $participant->first_name }} {{ $participant->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Worker</label>
                    <select name="worker_id" class="form-select">
                        <option value="">All workers</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}" @selected(request('worker_id') == $worker->id)>
                                {{ $worker->first_name }} {{ $worker->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(request('status') == $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">Filter</button>
                    <a href="{{ route('portal.admin.shifts.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Participant</th>
                        <th>Worker</th>
                        <th>Service</th>
                        <th>Category</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td>{{ $shift->shift_date?->format('d M Y') }}</td>
                            <td>{{ $shift->participant?->first_name }} {{ $shift->participant?->last_name }}</td>
                            <td>{{ $shift->worker?->first_name ?? 'Unassigned' }} {{ $shift->worker?->last_name ?? '' }}</td>
                            <td>{{ $shift->service_type ?? 'N/A' }}</td>
                            <td>{{ $shift->service_category ?? 'N/A' }}</td>
                            <td>{{ $shift->start_time }} &ndash; {{ $shift->end_time }}</td>
                            <td><span class="badge bg-{{ $shift->status === 'completed' ? 'success' : ($shift->status === 'cancelled' ? 'danger' : ($shift->status === 'in_progress' ? 'info' : ($shift->status === 'missed' ? 'warning' : 'secondary'))) }}">{{ ucfirst(str_replace('_', ' ', $shift->status)) }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('portal.admin.shifts.edit', $shift) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form action="{{ route('portal.admin.shifts.cancel', $shift) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Cancel this shift?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">No shifts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $shifts->links() }}
        </div>
    </div>
@endsection
