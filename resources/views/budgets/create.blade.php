@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Create Budget</h1>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if($participant)
        <div class="mb-3">
            <label class="form-label">Participant</label>
            <div class="form-control-plaintext">{{ $participant->first_name }} {{ $participant->last_name }} ({{ $participant->participant_number ?? $participant->id }})</div>
        </div>
    @elseif($participants)
        <div class="mb-3">
            <label class="form-label">Participant</label>
            <select name="participant_id" class="form-select" required>
                <option value="">Select participant</option>
                @foreach($participants as $option)
                    <option value="{{ $option->id }}">{{ $option->first_name }} {{ $option->last_name }} ({{ $option->participant_number ?? $option->id }})</option>
                @endforeach
            </select>
        </div>
    @endif

    <form method="POST" action="{{ route('budgets.store') }}">
        @csrf
        @if($participant)
            <input type="hidden" name="participant_id" value="{{ $participant->id }}">
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-3">
        <label class="form-label">Quarter Start</label>
        <input type="date" class="form-control" value="{{ old($hasQuarterStartColumn ? 'quarter_start' : 'quarter_start_date', $period[$hasQuarterStartColumn ? 'quarter_start' : 'quarter_start_date'] ?? '') }}" readonly>
        <input type="hidden" name="{{ $hasQuarterStartColumn ? 'quarter_start' : 'quarter_start_date' }}" value="{{ old($hasQuarterStartColumn ? 'quarter_start' : 'quarter_start_date', $period[$hasQuarterStartColumn ? 'quarter_start' : 'quarter_start_date'] ?? '') }}">
    </div>
        <div class="mb-3">
            <label class="form-label">Quarter End</label>
            <input type="date" class="form-control" value="{{ old($hasQuarterStartColumn ? 'quarter_end' : 'quarter_end_date', $period[$hasQuarterStartColumn ? 'quarter_end' : 'quarter_end_date'] ?? '') }}" readonly>
            <input type="hidden" name="{{ $hasQuarterStartColumn ? 'quarter_end' : 'quarter_end_date' }}" value="{{ old($hasQuarterStartColumn ? 'quarter_end' : 'quarter_end_date', $period[$hasQuarterStartColumn ? 'quarter_end' : 'quarter_end_date'] ?? '') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Opening Budget</label>
            <input type="number" step="0.01" name="opening_budget" class="form-control" required value="{{ old('opening_budget') }}">
        </div>
        <div class="mb-3">
            <label class="form-label">Carry Over</label>
            <input type="number" step="0.01" name="carry_over" class="form-control" value="{{ old('carry_over', '0.00') }}">
        </div>
        <button class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
