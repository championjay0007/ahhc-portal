@extends('layouts.portal')

@section('title', 'Submit Care Note')

@section('content')
    <div class="portal-page-header">
        <h1>Submit Care Note</h1>
        <p>Capture care and support details for a participant.</p>
    </div>

    <form method="POST" action="{{ route('portal.worker.care_notes.store') }}" enctype="multipart/form-data">
        @csrf

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="mb-3">
            <label for="participant_id" class="form-label">Participant</label>
            <select id="participant_id" name="participant_id" class="form-control" required>
                <option value="">Select a participant</option>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->participant->id }}"
                        {{ old('participant_id', optional($selectedShift)->participant_id ?? request('participant_id')) == $assignment->participant->id ? 'selected' : '' }}>
                        {{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}
                    </option>
                @endforeach
            </select>
        </div>

        @if(isset($shifts) && $shifts->isNotEmpty())
            <div class="mb-3">
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

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="shift_date" class="form-label">Shift Date</label>
                <input type="date" id="shift_date" name="shift_date" class="form-control" value="{{ old('shift_date', optional($selectedShift)->shift_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
            </div>
            <div class="col-md-4">
                <label for="start_time" class="form-label">Start Time</label>
                <input type="time" id="start_time" name="start_time" class="form-control" value="{{ old('start_time', optional($selectedShift)->start_time ?? '') }}" required>
            </div>
            <div class="col-md-4">
                <label for="end_time" class="form-label">End Time</label>
                <input type="time" id="end_time" name="end_time" class="form-control" value="{{ old('end_time', optional($selectedShift)->end_time ?? '') }}" required>
        </div>

        <div class="mb-3">
            <label for="observations" class="form-label">Observations</label>
            <textarea id="observations" name="observations" rows="4" class="form-control">{{ old('observations') }}</textarea>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <label for="mood" class="form-label">Mood</label>
                <input type="text" id="mood" name="mood" class="form-control" value="{{ old('mood') }}" placeholder="Calm, anxious, alert">
            </div>
            <div class="col-md-8">
                <label for="medication_administered" class="form-label">Medication Administered</label>
                <input type="text" id="medication_administered" name="medication_administered" class="form-control" value="{{ old('medication_administered') }}" placeholder="Medication given during shift">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="risks_flag" name="risks_flag" {{ old('risks_flag') ? 'checked' : '' }}>
                    <label class="form-check-label" for="risks_flag">Risks flagged</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="1" id="service_confirmed" name="service_confirmed" {{ old('service_confirmed') ? 'checked' : '' }}>
                    <label class="form-check-label" for="service_confirmed">I confirm the service was delivered</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="attachment" class="form-label">Attachment</label>
            <input type="file" id="attachment" name="attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xlsx,.xls">
        </div>

        <button type="submit" class="btn btn-primary">Submit Care Note</button>
    </form>
@endsection
