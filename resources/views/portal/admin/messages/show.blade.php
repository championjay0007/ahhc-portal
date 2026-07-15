@extends('layouts.admin')

@section('title', $message->subject)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="mb-3">
                <a href="{{ route('portal.messages.inbox') }}" class="btn btn-outline-secondary">
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
                                <a href="{{ route('portal.messages.conversation', $replyTarget->id) }}" class="btn btn-sm btn-primary">
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
                    @php
                        $decodedBody = html_entity_decode($message->body);
                        $bodyHasHtml = preg_match('/<\s*[^>]+>/', $decodedBody);
                    @endphp
                    @if($message->template_id || $bodyHasHtml)
                        {!! $decodedBody !!}
                    @else
                        {!! nl2br(e($decodedBody)) !!}
                    @endif
                </div>
                </div>

                <div class="card-footer bg-light">
                    <div class="btn-group" role="group">
                        @if(is_null($message->read_at))
                            <form action="{{ route('portal.messages.mark_read', $message) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-envelope-open"></i> Mark as Read
                                </button>
                            </form>
                        @else
                            <form action="{{ route('portal.messages.mark_unread', $message) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-envelope"></i> Mark as Unread
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('portal.messages.delete', $message) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this message?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
