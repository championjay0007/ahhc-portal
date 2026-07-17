@extends('layouts.portal')

@section('title', 'Incident / Risk Form')

@section('content')
    <div class="portal-page-header">
        <div>
            <h1>Incident / Risk Form</h1>
            <p>Record an incident or risk event for a participant.</p>
        </div>
        <div>
            <a href="{{ route('portal.worker.assigned_participants') }}" class="btn btn-outline-secondary">Back to participants</a>
        </div>
    </div>

    <div class="card p-4 shadow-sm">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the errors below.</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('portal.worker.incidents.store') }}">
            @csrf

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="participant_id" class="form-label">Participant</label>
                    <select id="participant_id" name="participant_id" class="form-control" required>
                        <option value="">Select a participant</option>
                        @foreach($assignments as $assignment)
                            <option value="{{ $assignment->participant->id }}" {{ old('participant_id', optional($selectedShift)->participant_id ?? request('participant_id')) == $assignment->participant->id ? 'selected' : '' }}>
                                {{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(isset($shifts) && $shifts->isNotEmpty())
                    <div class="col-md-4">
                        <label for="shift_id" class="form-label">Related Shift (optional)</label>
                        <select id="shift_id" name="shift_id" class="form-control">
                            <option value="">Link to a shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id', optional($selectedShift)->id ?? request('shift_id')) == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->shift_date?->format('d M Y') }} {{ $shift->start_time }}-{{ $shift->end_time }} — {{ $shift->participant?->first_name }} {{ $shift->participant?->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-4">
                    <label for="type" class="form-label">Type</label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="">Select a type</option>
                        <option value="incident" {{ old('type') === 'incident' ? 'selected' : '' }}>Incident</option>
                        <option value="hazard" {{ old('type') === 'hazard' ? 'selected' : '' }}>Hazard</option>
                        <option value="near_miss" {{ old('type') === 'near_miss' ? 'selected' : '' }}>Near miss</option>
                        <option value="complaint" {{ old('type') === 'complaint' ? 'selected' : '' }}>Complaint</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="severity" class="form-label">Severity</label>
                    <select id="severity" name="severity" class="form-control" required>
                        <option value="low" {{ old('severity') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('severity', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('severity') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" rows="6" class="form-control" required>{{ old('description') }}</textarea>
            </div>

            <div class="form-text text-muted mb-3">
                Provide as much detail as possible so the support team can review and action the incident promptly.
            </div>

            <button type="submit" class="btn btn-danger">Submit Incident</button>
        </form>
    </div>
@endsection
