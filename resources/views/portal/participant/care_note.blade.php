@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Care Note</h2>
            <p class="text-muted">Details of the submitted care note.</p>
        </div>
    </div>

    <div class="card portal-card p-4">
        <h5>{{ optional($careNote->shift_date)->format('Y-m-d') }} - {{ $careNote->service_type ?? 'General' }}</h5>
        <p class="mb-1"><strong>Worker:</strong> {{ optional($careNote->worker)->first_name }} {{ optional($careNote->worker)->last_name }}</p>
        <p class="mb-1"><strong>Status:</strong> {{ ucfirst($careNote->status) }}</p>
        @if($careNote->approved_at)
            <p class="mb-1"><strong>Approved:</strong> {{ \Illuminate\Support\Carbon::parse($careNote->approved_at)->format('Y-m-d H:i') }}</p>
        @endif
        @if($careNote->approved_by)
            <p class="mb-1"><strong>Approved by:</strong> {{ optional($careNote->approved_by)->name ?? 'Administrator' }}</p>
        @endif
        <hr />
        <h6>Summary</h6>
        <p>{{ $careNote->care_summary }}</p>

        @if($careNote->observations)
            <h6>Observations</h6>
            <p>{{ $careNote->observations }}</p>
        @endif

        @if($careNote->attachment_path)
            <p class="mt-3"><a href="{{ route('portal.participant.care_notes.attachment.download', $careNote) }}" class="btn btn-sm btn-outline-secondary">Download attachment</a></p>
        @endif
    </div>

    <p class="mt-3"><a href="{{ route('portal.participant.care_notes.index') }}" class="link-secondary">Back to care notes</a></p>

@endsection
