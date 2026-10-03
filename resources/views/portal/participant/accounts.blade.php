@extends('layouts.portal')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Manage account access</h1>
            <p class="text-muted mb-0">Invite someone you trust to manage this participant account.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="mb-5" aria-labelledby="invite-manager-heading">
        <h2 id="invite-manager-heading" class="h5 mb-3">Invite a manager</h2>
        <form method="POST" action="{{ route('portal.participant.accounts.invite') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-sm-8 col-lg-5">
                <label for="manager-email" class="form-label">Their account email</label>
                <input id="manager-email" name="email" type="email" value="{{ old('email') }}" class="form-control" required maxlength="255" autocomplete="email">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary"><i class="bi bi-envelope-plus me-1" aria-hidden="true"></i>Send invitation</button>
            </div>
        </form>
    </section>

    <section aria-labelledby="access-list-heading">
        <h2 id="access-list-heading" class="h5 mb-3">Invitations and access</h2>
        @if($delegations->isEmpty())
            <p class="text-muted">No invitations or delegated managers yet.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Email</th>
                            <th scope="col">Status</th>
                            <th scope="col">Expires</th>
                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($delegations as $delegation)
                            <tr>
                                <td>{{ $delegation->manager?->name ?? $delegation->invited_email }}</td>
                                <td>
                                    @if($delegation->revoked_at)
                                        Revoked
                                    @elseif($delegation->accepted_at)
                                        Active
                                    @elseif($delegation->expires_at->isPast())
                                        Expired
                                    @else
                                        Invitation pending
                                    @endif
                                </td>
                                <td>{{ \App\Support\DateTimeDisplay::format($delegation->expires_at, 'M j, Y', $displayTimezone ?? 'UTC') }}</td>
                                <td class="text-end">
                                    @unless($delegation->revoked_at)
                                        <form method="POST" action="{{ route('portal.participant.accounts.revoke', $delegation) }}" onsubmit="return confirm('Revoke this invitation or access?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Revoke</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>
@endsection