@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Assignment Details</h4>
                <p class="text-muted mb-0">Review assignment details and participant-worker coverage.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.admin.assignments') }}" class="btn btn-sm btn-outline-secondary">Back to assignments</a>
                <a href="{{ route('portal.admin.assignments.edit', $assignment) }}" class="btn btn-sm btn-primary">Edit</a>
                <form method="POST" action="{{ route('portal.admin.assignments.destroy', $assignment) }}" class="d-inline-block" onsubmit="return confirm('Delete this assignment?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                </form>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card p-3 mb-3">
                    <h6>Participant</h6>
                    <p class="mb-1"><strong>Name</strong> {{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}</p>
                    <p class="mb-1"><strong>Participant #</strong> {{ $assignment->participant->participant_number }}</p>
                    <p class="mb-1"><strong>Email</strong> {{ $assignment->participant->email }}</p>
                    <p class="mb-1"><strong>Phone</strong> {{ $assignment->participant->phone }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 mb-3">
                    <h6>Worker</h6>
                    <p class="mb-1"><strong>Name</strong> {{ $assignment->worker->first_name }} {{ $assignment->worker->last_name }}</p>
                    <p class="mb-1"><strong>Worker #</strong> {{ $assignment->worker->worker_number }}</p>
                    <p class="mb-1"><strong>Email</strong> {{ $assignment->worker->email }}</p>
                    <p class="mb-1"><strong>Phone</strong> {{ $assignment->worker->phone }}</p>
                </div>
            </div>
        </div>

        <div class="card p-3">
            <h6>Assignment summary</h6>
            <p class="mb-1"><strong>Type</strong> {{ ucfirst($assignment->assignment_type) }}</p>
            <p class="mb-1"><strong>Status</strong> {{ ucfirst($assignment->status) }}</p>
            <p class="mb-1"><strong>Primary support</strong> {{ $assignment->is_primary ? 'Yes' : 'No' }}</p>
            <p class="mb-1"><strong>Start date</strong> {{ optional($assignment->start_date)->format('Y-m-d') }}</p>
            <p class="mb-1"><strong>End date</strong> {{ optional($assignment->end_date)->format('Y-m-d') ?? 'Ongoing' }}</p>
            <p class="mb-1"><strong>Support person</strong> {{ optional($assignment->supportPerson)->first_name ? optional($assignment->supportPerson)->first_name . ' ' . optional($assignment->supportPerson)->last_name : 'None' }}</p>
        </div>
    </div>
@endsection
