@extends('layouts.portal')

@section('title', 'Participant Details')

@section('content')
    <div class="portal-page-header">
        <div>
            <h1>{{ $participant->first_name }} {{ $participant->last_name }}</h1>
            <p>Details and related records for this participant.</p>
        </div>
        <div>
            <a href="{{ route('portal.worker.assigned_participants') }}" class="btn btn-outline-secondary">Back to assignments</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card p-4">
                <h3 class="h5 mb-3">Participant summary</h3>
                <ul class="list-unstyled">
                    <li><strong>Name:</strong> {{ $participant->first_name }} {{ $participant->last_name }}</li>
                    <li>
                        <strong>Address:</strong> 
                        {{ $participant->address ?? '—' }}
                        @if($participant->city), {{ $participant->city }}@endif
                        @if($participant->state) {{ $participant->state }}@endif
                        @if($participant->postcode) {{ $participant->postcode }}@endif
                    </li>
                    <li><strong>Mobile:</strong> {{ $participant->phone ?? '—' }}</li>
                    <li><strong>Assignment status:</strong> {{ ucfirst($assignment->status) }}</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card p-4">
                <h3 class="h5 mb-3">Assigned shifts</h3>
                @if($shifts->isEmpty())
                    <p class="mb-0">No active shifts assigned.</p>
                @else
                    <ul class="list-unstyled">
                        @foreach($shifts as $s)
                            <li>
                                <strong>{{ optional($s->shift_date)->format('d M Y') ?? 'TBA' }}</strong>
                                <div class="small text-muted">
                                    {{ $s->start_time }} &ndash; {{ $s->end_time }}
                                    @if($s->service_type)
                                        · {{ $s->service_type }}
                                    @endif
                                </div>
                                @if($s->location)
                                    <div class="small text-muted">{{ $s->location }}</div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <!-- Workers can take actions, but sensitive participant data is hidden from view. -->

    <div class="row g-4">
        <div class="col-md-4">
            <a href="{{ route('portal.worker.care_notes.create') }}" class="btn btn-primary w-100">Create Care Note</a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('portal.worker.incidents.create') }}" class="btn btn-danger w-100">Report Incident</a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('portal.worker.documents.upload') }}" class="btn btn-secondary w-100">Upload Document</a>
        </div>
    </div>
@endsection
