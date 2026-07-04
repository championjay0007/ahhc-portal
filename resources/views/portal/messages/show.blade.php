@extends('layouts.portal')

@section('title', $message->subject)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="mb-3">
                <a href="{{ route($messageRoutePrefix.'inbox') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inbox
                </a>
            </div>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header bg-light">
                    <div class="row">
                        <div class="col-md-8">
                            <h3 class="mb-0">{{ $message->subject }}</h3>
                            <small class="text-muted">From: <strong>{{ $message->sender->name }}</strong></small>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">
                                {{ $message->created_at->format('M d, Y \a\t H:i') }}
                            </small>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            @if(auth()->id() === $message->sender_id)
                                <p class="mb-1"><strong>You sent this message to {{ $message->recipient->name }}.</strong></p>
                            @elseif(auth()->id() === $message->recipient_id)
                                <p class="mb-1"><strong>{{ $message->sender->name }} sent this message to you.</strong></p>
                            @else
                                <p class="mb-1"><strong>This message was sent by {{ $message->sender->name }} to {{ $message->recipient->name }}.</strong></p>
                            @endif
                            <p class="text-muted small mb-0">Reply target: {{ $replyTarget->name }} &lt;{{ $replyTarget->email }}&gt;</p>
                        </div>
                        <div>
                            @if($canChat)
                                <a href="{{ route($messageRoutePrefix.'conversation', $replyTarget->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-chat-left-text"></i> Reply in portal
                                </a>
                            @else
                                <a href="{{ $replyEmailUrl }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-envelope"></i> Reply by email
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="message-content">
                        @if($message->template_id)
                            {!! $message->body !!}
                        @else
                            {!! nl2br(e($message->body)) !!}
                        @endif
                    </div>
                </div>

                <div class="card-footer bg-light">
                    <div class="btn-group" role="group">
                        @if(is_null($message->read_at))
                            <form action="{{ route($messageRoutePrefix.'mark_read', $message) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-envelope-open"></i> Mark as Read
                                </button>
                            </form>
                        @else
                            <form action="{{ route($messageRoutePrefix.'mark_unread', $message) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-envelope"></i> Mark as Unread
                                </button>
                            </form>
                        @endif

                        @if($message->recipient_id === auth()->id() || $message->sender_id === auth()->id())
                            <form action="{{ route($messageRoutePrefix.'delete', $message) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .message-content {
        line-height: 1.6;
        color: #333;
    }
</style>
@endsection
