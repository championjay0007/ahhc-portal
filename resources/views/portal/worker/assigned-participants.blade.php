@extends('layouts.portal')

@section('title', 'Assigned Participants')

@section('content')
    <div class="portal-page-header">
        <h1>Assigned Participants</h1>
        <p>Participants currently assigned to you.</p>
    </div>

    @if($assignments->isEmpty())
        <div class="portal-empty-state">
            <p>You do not have any active participant assignments.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Address</th>
                        <th>Mobile</th>
                        <th>Shift(s)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assignments as $assignment)
                        <tr>
                            <td>{{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}</td>
                            <td>{{ $assignment->participant->address ?? '—' }}</td>
                            <td>{{ $assignment->participant->phone ?? '—' }}</td>
                            <td>
                                @if($assignment->start_date)
                                    {{ optional($assignment->start_date)->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2 justify-content-end">
                                    @if($assignment->participant->user)
                                        <a href="{{ route('portal.messages.compose', $assignment->participant->user->id) }}" class="btn btn-sm btn-primary">Chat</a>
                                    @endif
                                    <a href="{{ route('portal.worker.participants.show', $assignment->participant) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
