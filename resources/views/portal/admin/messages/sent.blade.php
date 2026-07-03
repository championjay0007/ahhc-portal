@extends('layouts.admin')

@section('title', 'Sent Messages')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">Sent Messages</h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('portal.admin.messages.send.index') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Send Message
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($messages->count() > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>To</th>
                                <th>Subject</th>
                                <th>Sent</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                            <tr>
                                <td>
                                    <strong>{{ $message->recipient->name }}</strong><br>
                                    <small class="text-muted">{{ $message->recipient->email }}</small>
                                </td>
                                <td>{{ Str::limit($message->subject, 50) }}</td>
                                <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('portal.messages.show', $message) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $messages->links() }}
        </div>
    @else
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> No sent messages yet.
        </div>
    @endif
</div>
@endsection
