@extends('layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Create Budget</h1>
    @php
        $preParticipant = request('participant_id') ? \App\Models\Participant::find(request('participant_id')) : null;
    @endphp
    @if($preParticipant)
        <div class="mb-3">
            <label class="form-label">Participant</label>
            <div class="form-control-plaintext">{{ $preParticipant->first_name }} {{ $preParticipant->last_name }} ({{ $preParticipant->participant_number ?? $preParticipant->id }})</div>
        </div>
    @endif
    <form method="POST" action="{{ route('budgets.store') }}">
        @csrf
        @if($preParticipant)
            <input type="hidden" name="participant_id" value="{{ $preParticipant->id }}">
        @endif
        <div class="mb-3">
            <label class="form-label">Quarter Start</label>
            <input type="date" name="quarter_start_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Quarter End</label>
            <input type="date" name="quarter_end_date" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Opening Budget</label>
            <input type="number" step="0.01" name="opening_budget" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Carry Over</label>
            <input type="number" step="0.01" name="carry_over" class="form-control">
        </div>
        <button class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
