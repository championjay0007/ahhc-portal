@extends('layouts.admin')

@section('content')
    @php
        $displayShiftDate = $careNote->shift_date
            ? (is_string($careNote->shift_date)
                ? \Illuminate\Support\Carbon::parse($careNote->shift_date)->format('Y-m-d')
                : $careNote->shift_date->format('Y-m-d'))
            : '—';
        $displayStartTime = $careNote->start_time
            ? (is_string($careNote->start_time)
                ? substr($careNote->start_time, 0, 5)
                : $careNote->start_time->format('H:i'))
            : '—';
        $displayEndTime = $careNote->end_time
            ? (is_string($careNote->end_time)
                ? substr($careNote->end_time, 0, 5)
                : $careNote->end_time->format('H:i'))
            : '—';
        $displaySubmittedAt = $careNote->submitted_at
            ? (is_string($careNote->submitted_at)
                ? \Illuminate\Support\Carbon::parse($careNote->submitted_at)->format('Y-m-d H:i')
                : $careNote->submitted_at->format('Y-m-d H:i'))
            : '—';
    @endphp

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Care Note {{ $careNote->id }}</h4>
                <p class="text-muted mb-0">Review this care note in detail.</p>
            </div>
            <div>
                <a href="{{ route('portal.admin.care_notes') }}" class="btn btn-sm btn-outline-secondary">Back to care notes</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>Care note details</h5>
                <dl class="row mb-4">
                    <dt class="col-sm-3">Participant</dt>
                    <dd class="col-sm-9">{{ optional($careNote->participant)->first_name }} {{ optional($careNote->participant)->last_name }}</dd>
                    <dt class="col-sm-3">Worker</dt>
                    <dd class="col-sm-9">{{ optional($careNote->worker)->first_name ?? '—' }} {{ optional($careNote->worker)->last_name ?? '' }}</dd>
                    <dt class="col-sm-3">Shift date</dt>
                    <dd class="col-sm-9">{{ $displayShiftDate }}</dd>
                    <dt class="col-sm-3">Start / End</dt>
                    <dd class="col-sm-9">{{ $displayStartTime }} — {{ $displayEndTime }}</dd>
                    <dt class="col-sm-3">Service type</dt>
                    <dd class="col-sm-9">{{ $careNote->service_type ?? '—' }}</dd>
                    <dt class="col-sm-3">Risk flagged</dt>
                    <dd class="col-sm-9">{{ $careNote->risks_flag ? 'Yes' : 'No' }}</dd>
                    <dt class="col-sm-3">Service confirmed</dt>
                    <dd class="col-sm-9">{{ $careNote->service_confirmed ? 'Yes' : 'No' }}</dd>
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">{{ ucfirst($careNote->status) }}</dd>
                    <dt class="col-sm-3">Submitted</dt>
                    <dd class="col-sm-9">{{ $displaySubmittedAt }}</dd>
                    <dt class="col-sm-3">Approved by</dt>
                    <dd class="col-sm-9">{{ optional($careNote->approver)->name ?? '—' }}</dd>
                    <dt class="col-sm-3">Created by</dt>
                    <dd class="col-sm-9">{{ optional($careNote->creator)->name ?? '—' }}</dd>
                </dl>

                <div class="mb-4">
                    <h6>Tasks completed</h6>
                    <p>{{ $careNote->tasks_completed }}</p>
                </div>

                <div class="mb-4">
                    <h6>Observations</h6>
                    <p>{{ $careNote->observations ?? 'None' }}</p>
                </div>

                @if($careNote->attachment_path)
                    <div class="mb-4">
                        <h6>Attachment</h6>
                        <p><a href="{{ \Illuminate\Support\Facades\Storage::url($careNote->attachment_path) }}" target="_blank">Download attachment</a></p>
                    </div>
                @endif

                @if($careNote->status !== 'approved')
                    <form method="POST" action="{{ route('portal.admin.care_notes.approve', ['careNote' => $careNote]) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Approve care note</button>
                    </form>
                @else
                    <div class="alert alert-success">This care note has been approved.</div>
                @endif
            </div>
        </div>
    </div>
@endsection
