@extends('layouts.portal')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-1">Assigned participants</h1>
        <p class="text-muted mb-0">Open an assigned account to work in that participant's portal.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    @if($delegations->isEmpty())
        <p class="text-muted">You do not have any active participant assignments yet. Ask the participant to invite this email address.</p>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">Participant</th>
                        <th scope="col">Email</th>
                        <th scope="col"><span class="visually-hidden">Action</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($delegations as $delegation)
                        @php($participant = $delegation->participant)
                        <tr>
                            <td>{{ $participant->first_name }} {{ $participant->last_name }}</td>
                            <td>{{ $participant->user->email }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('portal.participant.accounts.switch') }}">
                                    @csrf
                                    <input type="hidden" name="participant_user_id" value="{{ $participant->user_id }}">
                                    <button type="submit" class="btn btn-primary btn-sm">Open participant</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
