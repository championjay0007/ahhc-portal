@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Incident {{ $incident->id }}</h4>
                <p class="text-muted mb-0">Review and manage this incident.</p>
            </div>
            <div>
                <a href="{{ route('portal.admin.incidents') }}" class="btn btn-sm btn-outline-secondary">Back to incidents</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>Details</h5>
                <dl class="row mb-4">
                    <dt class="col-sm-3">Participant</dt>
                    <dd class="col-sm-9">{{ optional($incident->participant)->first_name }} {{ optional($incident->participant)->last_name }}</dd>
                    <dt class="col-sm-3">Reported by</dt>
                    <dd class="col-sm-9">{{ optional($incident->reporter)->name ?? '—' }}</dd>
                    <dt class="col-sm-3">Type</dt>
                    <dd class="col-sm-9">{{ ucfirst($incident->incident_type ?? $incident->category) }}</dd>
                    <dt class="col-sm-3">Severity</dt>
                    <dd class="col-sm-9">{{ ucfirst($incident->severity) }}</dd>
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">{{ ucfirst($incident->status) }}</dd>
                    <dt class="col-sm-3">Occurred</dt>
                    <dd class="col-sm-9">{{ optional($incident->occurred_at)->format('Y-m-d H:i') ?? optional($incident->created_at)->format('Y-m-d H:i') }}</dd>
                </dl>

                <div class="mb-4">
                    <h6>Description</h6>
                    <p>{{ $incident->description }}</p>
                </div>

                <div class="mb-4">
                    <h6>Action taken</h6>
                    <p>{{ $incident->action_taken ?? 'None' }}</p>
                </div>

                <form method="POST" action="{{ route('portal.admin.incidents.status', $incident) }}">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="open" {{ $incident->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="investigating" {{ $incident->status === 'investigating' ? 'selected' : '' }}>Investigating</option>
                                <option value="closed" {{ $incident->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="action_taken" class="form-label">Action taken</label>
                            <input type="text" id="action_taken" name="action_taken" class="form-control" value="{{ old('action_taken', $incident->action_taken) }}">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update incident</button>
                </form>
            </div>
        </div>
    </div>
@endsection
