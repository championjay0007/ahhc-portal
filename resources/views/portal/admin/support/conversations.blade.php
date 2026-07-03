@extends('layouts.admin')

@section('title', 'Support Conversations')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">
                <i class="bi bi-chat-dots-fill"></i> Support Conversations
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('portal.admin.support.conversation.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Start New Conversation
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Subject, user..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in-progress" {{ request('status') === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Conversations List -->
    <div class="row">
        @forelse($conversations as $conv)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-2">{{ Str::limit($conv->subject, 40) }}</h5>
                                @php
                                    $isVisitor = optional($conv->user)->email === 'website-support@ahhc.com.au';
                                    $firstName = optional($conv->user)->first_name ?? (optional($conv->user)->name ? explode(' ', optional($conv->user)->name)[0] : null);
                                    $initial = $firstName ? strtoupper(substr($firstName, 0, 1)) : 'U';
                                @endphp
                                @if(! $isVisitor)
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;">
                                            {{ $initial }}
                                        </div>
                                        <div class="small text-muted mb-0">{{ $conv->user->name }}</div>
                                    </div>
                                @else
                                    <p class="text-muted mb-0"><i class="bi bi-person"></i> {{ $conv->user->name }}</p>
                                @endif
                            </div>
                            <span class="badge bg-{{ $conv->status === 'open' ? 'danger' : ($conv->status === 'in-progress' ? 'warning' : 'success') }}">
                                {{ ucfirst($conv->status) }}
                            </span>
                        </div>

                        <small class="text-muted">
                            <i class="bi bi-chat"></i> {{ $conv->messages->count() }} messages •
                            <i class="bi bi-clock"></i> {{ $conv->last_message_at?->diffForHumans() ?? 'No messages' }}
                        </small>

                        <div class="mt-3">
                            <a href="{{ route('portal.admin.support.conversation.show', $conv) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-chat-dots"></i> Open Chat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> No conversations found. <a href="{{ route('portal.admin.support.conversation.create') }}">Start a new one</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $conversations->links() }}
    </div>
</div>
@endsection
