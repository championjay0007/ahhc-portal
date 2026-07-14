@extends('layouts.admin')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Create Shift</h4>
            <p class="text-muted mb-0">Schedule a service shift for a participant and assign a worker.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('portal.admin.shifts.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Participant</label>
                        <select name="participant_id" class="form-select @error('participant_id') is-invalid @enderror" required>
                            <option value="">Select participant</option>
                            @foreach($participants as $participant)
                                <option value="{{ $participant->id }}" @selected(old('participant_id') == $participant->id)>{{ $participant->first_name }} {{ $participant->last_name }}</option>
                            @endforeach
                        </select>
                        @error('participant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Worker</label>
                        <select name="worker_id" class="form-select @error('worker_id') is-invalid @enderror">
                            <option value="">Select worker (optional)</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" @selected(old('worker_id') == $worker->id)>{{ $worker->first_name }} {{ $worker->last_name }}</option>
                            @endforeach
                        </select>
                        @error('worker_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Service type</label>
                        <input name="service_type" value="{{ old('service_type') }}" class="form-control @error('service_type') is-invalid @enderror" maxlength="150">
                        @error('service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Service category</label>
                        <input name="service_category" value="{{ old('service_category') }}" class="form-control @error('service_category') is-invalid @enderror" maxlength="150">
                        @error('service_category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Shift date</label>
                        <input type="date" name="shift_date" value="{{ old('shift_date') }}" class="form-control @error('shift_date') is-invalid @enderror" required>
                        @error('shift_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start time</label>
                        <input type="time" name="start_time" value="{{ old('start_time') }}" class="form-control @error('start_time') is-invalid @enderror" required>
                        @error('start_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">End time</label>
                        <input type="time" name="end_time" value="{{ old('end_time') }}" class="form-control @error('end_time') is-invalid @enderror" required>
                        @error('end_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <input name="location" value="{{ old('location') }}" class="form-control @error('location') is-invalid @enderror" maxlength="255">
                        @error('location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" rows="4" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', 'scheduled') === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Shift</button>
                    <a href="{{ route('portal.admin.shifts.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
