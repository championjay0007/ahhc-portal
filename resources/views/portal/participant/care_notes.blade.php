@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Care Notes</h2>
            <p class="text-muted">Record and review care notes related to your participant account.</p>
        </div>
    </div>

    <!-- Participants cannot create care notes; workers submit care notes. -->

    @if(! empty($missingEvidence) && count($missingEvidence) > 0)
        <div class="card portal-card mb-4 p-4 border-warning">
            <h5 class="mb-2">Missing Service Evidence</h5>
            <p class="text-muted small">The following assigned workers have not uploaded service evidence in the last 30 days.</p>
            <ul class="mb-0">
                @foreach($missingEvidence as $me)
                    <li>{{ $me['worker_name'] ?? 'Worker' }} @if($me['start_date']) - from {{ optional($me['start_date'])->format('Y-m-d') }}@endif</li>
                @endforeach
            </ul>
            <p class="mt-2"><a href="{{ route('portal.participant.documents.index') }}" class="link-primary">Upload or request evidence</a></p>
        </div>
    @endif

    <div class="card portal-card p-4">
        <h5 class="mb-3">Care note history</h5>
        @if($notes->isEmpty())
            <p class="text-muted">No care notes have been recorded yet.</p>
        @else
            <div class="list-group">
                @foreach($notes as $note)
                    <a href="{{ route('portal.participant.care_notes.show', $note) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <strong>{{ optional($note->shift_date)->format('Y-m-d') }}</strong>
                                <div class="small text-muted">{{ $note->service_type ?? 'General care note' }}</div>
                                <div class="small text-muted">Worker: {{ optional($note->worker)->first_name }} {{ optional($note->worker)->last_name }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ $note->status === 'approved' ? 'success' : ($note->status === 'rejected' ? 'danger' : 'secondary') }}">{{ ucfirst($note->status) }}</span>
                                <div class="small text-muted mt-1">View</div>
                            </div>
                        </div>
                        <p class="mt-2 mb-0">{{ \Illuminate\Support\Str::limit($note->care_summary, 140) }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
