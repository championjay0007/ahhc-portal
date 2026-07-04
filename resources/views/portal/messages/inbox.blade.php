@extends('layouts.portal')

@section('title', 'Messages Inbox')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">
                Messages
                @if($unreadCount > 0)
                    <span class="badge bg-danger">{{ $unreadCount }} unread</span>
                @endif
            </h1>
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
                                <th></th>
                                <th>From</th>
                                <th>Subject</th>
                                <th>Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($messages as $message)
                            <tr {{ is_null($message->read_at) ? 'class=fw-bold' : '' }}>
                                <td style="width: 40px;">
                                    @if(is_null($message->read_at))
                                        <i class="bi bi-circle-fill text-primary" style="font-size: 0.5rem;"></i>
                                    @else
                                        <i class="bi bi-circle" style="font-size: 0.5rem;"></i>
                                    @endif
                                </td>
                                <td>{{ $message->sender->name }}</td>
                                <td>{{ Str::limit($message->subject, 50) }}</td>
                                <td>{{ $message->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route($messageRoutePrefix.'show', $message) }}" class="btn btn-sm btn-outline-primary">
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
            <i class="bi bi-inbox"></i> Your inbox is empty.
        </div>
    @endif
</div>
@endsection
