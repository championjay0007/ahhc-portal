@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Care Notes for {{ $participant->first_name }} {{ $participant->last_name }}</h4>
                <p class="text-muted mb-0">Create new notes and review existing records for this participant.</p>
            </div>
            <a href="{{ route('portal.admin.participants.show', $participant) }}" class="btn btn-sm btn-outline-secondary">Back to participant</a>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Add care note</h5>
                <form method="POST" action="{{ route('portal.admin.participants.care_notes.store', $participant) }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Visit date</label>
                            <input type="date" name="shift_date" class="form-control" value="{{ old('shift_date') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Service type</label>
                            <input type="text" name="service_type" class="form-control" value="{{ old('service_type') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Care summary</label>
                            <textarea name="care_summary" rows="4" class="form-control" required>{{ old('care_summary') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Observations</label>
                            <textarea name="observations" rows="3" class="form-control">{{ old('observations') }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mood</label>
                            <input type="text" name="mood" class="form-control" value="{{ old('mood') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Medication administered</label>
                            <input type="text" name="medication_administered" class="form-control" value="{{ old('medication_administered') }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="incident_reported" value="0">
                                <input type="checkbox" name="incident_reported" value="1" class="form-check-input" id="incident_reported" {{ old('incident_reported') ? 'checked' : '' }}>
                                <label class="form-check-label" for="incident_reported">Incident reported</label>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">Save care note</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Care note history</h5>
                @if($notes->isEmpty())
                    <p class="text-muted">No care notes recorded yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Worker</th>
                                    <th>Summary</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notes as $note)
                                    <tr>
                                        <td>{{ optional($note->shift_date)->format('Y-m-d') }}</td>
                                        <td>{{ optional($note->worker)->first_name ?? '—' }} {{ optional($note->worker)->last_name ?? '' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($note->care_summary, 100) }}</td>
                                        <td>{{ ucfirst($note->status) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
