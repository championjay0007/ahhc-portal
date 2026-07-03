@extends('layouts.portal')

@section('title', 'Live Support Chats')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><i class="bi bi-chat-dots-fill"></i> Live Support Chats</h1>
            <p class="text-muted">Track your active support conversations and reply in real time.</p>
        </div>
        <div>
            <a href="{{ route('portal.support.create') }}" class="btn btn-primary me-2">
                <i class="bi bi-plus-circle"></i> New Ticket
            </a>
            <a href="{{ route('portal.support.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-ticket"></i> My Tickets
            </a>
        </div>
    </div>

    @if($conversations->count() > 0)
        <div class="row g-4">
            @foreach($conversations as $conversation)
                <div class="col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1">{{ Str::limit($conversation->subject, 60) }}</h5>
                                    <p class="text-muted mb-2">Last updated {{ $conversation->last_message_at?->diffForHumans() ?? 'just now' }}</p>
                                </div>
                                <span class="badge bg-{{ $conversation->status === 'open' ? 'success' : ($conversation->status === 'waiting' ? 'warning' : ($conversation->status === 'in-progress' ? 'info' : 'secondary')) }}">
                                    {{ ucfirst($conversation->status) }}
                                </span>
                            </div>

                            <div class="mb-3">
                                <span class="badge bg-light text-dark">Priority: {{ ucfirst($conversation->priority) }}</span>
                                <span class="badge bg-light text-dark">{{ $conversation->messages->count() }} messages</span>
                                @if($conversation->unread_messages_count > 0)
                                    <span class="badge bg-danger text-white">{{ $conversation->unread_messages_count }} new</span>
                                @endif
                            </div>

                            <a href="{{ route('portal.support.conversations.show', $conversation) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-chat-dots"></i> Open Chat
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $conversations->links() }}
        </div>
    @else
        <div class="card p-5 text-center">
            <h4>No active chats yet</h4>
            <p class="text-muted">Support conversations will appear here once an admin starts a live chat with you.</p>
            <a href="{{ route('portal.support.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle"></i> Open a Support Ticket
            </a>
        </div>
    @endif
</div>
@endsection
