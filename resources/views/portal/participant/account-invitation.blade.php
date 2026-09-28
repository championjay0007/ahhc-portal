@extends('layouts.portal')

@section('content')
<div class="container py-5" style="max-width: 680px">
    <h1 class="h3 mb-3">Participant account invitation</h1>
    <p>{{ $invitation->participant->user->name }} invited you to manage their participant account.</p>
    <p class="text-muted">You will use your own sign-in and can switch back to your own account at any time. The account owner can revoke access.</p>

    <form method="POST" action="{{ route('portal.participant.accounts.accept', ['token' => request()->route('token')]) }}">
        @csrf
        <button type="submit" class="btn btn-primary">Accept invitation</button>
        <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary ms-2">Not now</a>
    </form>
</div>
@endsection