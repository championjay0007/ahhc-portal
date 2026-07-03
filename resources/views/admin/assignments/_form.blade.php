@php
    $assignment = $assignment ?? null;
    $participantId = old('participant_id', $assignment?->participant_id);
    $workerId = old('worker_id', $assignment?->worker_id);
    $supportPersonId = old('support_person_id', $assignment?->support_person_id);
    $startDate = old('start_date', optional($assignment?->start_date)->format('Y-m-d'));
    $endDate = old('end_date', optional($assignment?->end_date)->format('Y-m-d'));
    $assignmentType = old('assignment_type', $assignment?->assignment_type ?? 'primary');
    $status = old('status', $assignment?->status ?? 'active');
    $isPrimary = old('is_primary', $assignment?->is_primary ? '1' : '0');
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Participant</label>
        <select name="participant_id" class="form-select" required>
            <option value="">Select participant</option>
            @foreach($participants as $participant)
                <option value="{{ $participant->id }}"{{ $participantId == $participant->id ? ' selected' : '' }}>
                    {{ $participant->first_name }} {{ $participant->last_name }} ({{ $participant->participant_number }})
                </option>
            @endforeach
        </select>
        @error('participant_id') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Worker</label>
        <select name="worker_id" class="form-select" required>
            <option value="">Select worker</option>
            @foreach($workers as $worker)
                <option value="{{ $worker->id }}"{{ $workerId == $worker->id ? ' selected' : '' }}>
                    {{ $worker->first_name }} {{ $worker->last_name }} ({{ $worker->worker_number }})
                </option>
            @endforeach
        </select>
        @error('worker_id') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">Support person</label>
        <select name="support_person_id" class="form-select">
            <option value="">None</option>
            @foreach($supportPeople as $supportPerson)
                <option value="{{ $supportPerson->id }}"{{ $supportPersonId == $supportPerson->id ? ' selected' : '' }}>
                    {{ $supportPerson->first_name }} {{ $supportPerson->last_name }}
                </option>
            @endforeach
        </select>
        @error('support_person_id') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Start date</label>
        <input name="start_date" type="date" value="{{ $startDate }}" class="form-control" required>
        @error('start_date') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">End date</label>
        <input name="end_date" type="date" value="{{ $endDate }}" class="form-control">
        @error('end_date') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Assignment type</label>
        <select name="assignment_type" class="form-select" required>
            <option value="primary"{{ $assignmentType === 'primary' ? ' selected' : '' }}>Primary</option>
            <option value="secondary"{{ $assignmentType === 'secondary' ? ' selected' : '' }}>Secondary</option>
            <option value="temporary"{{ $assignmentType === 'temporary' ? ' selected' : '' }}>Temporary</option>
        </select>
        @error('assignment_type') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            <option value="active"{{ $status === 'active' ? ' selected' : '' }}>Active</option>
            <option value="inactive"{{ $status === 'inactive' ? ' selected' : '' }}>Inactive</option>
        </select>
        @error('status') <div class="text-danger small">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <div class="form-check mt-4 pt-2">
            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary"{{ $isPrimary ? ' checked' : '' }}>
            <label class="form-check-label" for="is_primary">Primary support</label>
        </div>
    </div>
    <div class="col-12 text-end">
        <button type="submit" class="btn btn-primary">{{ $buttonText ?? 'Save assignment' }}</button>
    </div>
</div>
