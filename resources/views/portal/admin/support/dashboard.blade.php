@extends('layouts.admin')

@section('title', 'Support Center Dashboard')

@push('styles')
    <style>
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .stat-card.tickets {
            border-left-color: #667eea;
        }
        .stat-card.conversations {
            border-left-color: #17a2b8;
        }
        .stat-card.messages {
            border-left-color: #28a745;
        }
        .stat-card h6 {
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .stat-card .number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        .card-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .card-section h5 {
            color: #333;
            font-weight: 700;
            margin-bottom: 1rem;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.75rem;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h2">
                <i class="bi bi-headset"></i> Support Center
            </h1>
            <p class="text-muted">Manage support tickets, live conversations, and user messages</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card tickets">
                <h6><i class="bi bi-ticket"></i> Total Tickets</h6>
                <div class="number">{{ $stats['total_tickets'] }}</div>
                <small class="text-muted">{{ $stats['open_tickets'] }} open</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card conversations">
                <h6><i class="bi bi-chat-dots"></i> Conversations</h6>
                <div class="number">{{ $stats['total_conversations'] }}</div>
                <small class="text-muted">{{ $stats['open_conversations'] }} active</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card messages">
                <h6><i class="bi bi-envelope"></i> Unread Messages</h6>
                <div class="number">{{ $stats['unread_messages'] }}</div>
                <small class="text-muted">Waiting for response</small>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card" style="border-left-color: #ffc107;">
                <h6><i class="bi bi-clock"></i> Pending</h6>
                <div class="number">{{ $stats['pending_responses'] }}</div>
                <small class="text-muted">Needs attention</small>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Tickets -->
        <div class="col-lg-6 mb-4">
            <div class="card-section">
                <h5>
                    <i class="bi bi-ticket-fill"></i> Recent Support Tickets
                </h5>
                @if($recentTickets->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentTickets as $ticket)
                            <a href="{{ route('portal.admin.support.ticket.show', $ticket) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ Str::limit($ticket->subject, 50) }}</h6>
                                        @php
                                            $isVisitorTicket = optional($ticket->user)->email === 'website-support@ahhc.com.au';
                                            $ticketFirstName = optional($ticket->user)->first_name ?? (optional($ticket->user)->name ? explode(' ', optional($ticket->user)->name)[0] : null);
                                            $ticketInitial = $ticketFirstName ? strtoupper(substr($ticketFirstName, 0, 1)) : 'U';
                                        @endphp
                                        <small class="text-muted">
                                            @if(! $isVisitorTicket)
                                                <span class="d-inline-flex align-items-center me-2">
                                                    <span class="avatar-sm rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center me-2" style="width:28px;height:28px;">{{ $ticketInitial }}</span>
                                                    {{ $ticket->user->name }}
                                                </span>
                                            @else
                                                <i class="bi bi-person"></i> {{ $ticket->user->name }}
                                            @endif
                                            • <i class="bi bi-clock"></i> {{ $ticket->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $ticket->status === 'open' ? 'danger' : ($ticket->status === 'in-progress' ? 'warning' : 'success') }}">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('portal.admin.support.tickets') }}" class="btn btn-sm btn-outline-primary">
                            View All Tickets <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <p class="text-muted mb-0">No recent tickets</p>
                @endif
            </div>
        </div>

        <!-- Active Conversations -->
        <div class="col-lg-6 mb-4">
            <div class="card-section">
                <h5>
                    <i class="bi bi-chat-dots-fill"></i> Active Conversations
                    <a href="{{ route('portal.admin.support.conversation.create') }}" class="btn btn-sm btn-outline-primary float-end">
                        <i class="bi bi-plus-circle"></i> New
                    </a>
                </h5>
                @if($activeConversations->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($activeConversations as $conv)
                            <a href="{{ route('portal.admin.support.conversation.show', $conv) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ Str::limit($conv->subject, 50) }}</h6>
                                        @php
                                            $isVisitorConv = optional($conv->user)->email === 'website-support@ahhc.com.au';
                                            $convFirstName = optional($conv->user)->first_name ?? (optional($conv->user)->name ? explode(' ', optional($conv->user)->name)[0] : null);
                                            $convInitial = $convFirstName ? strtoupper(substr($convFirstName, 0, 1)) : 'U';
                                        @endphp
                                        <small class="text-muted">
                                            @if(! $isVisitorConv)
                                                <span class="d-inline-flex align-items-center me-2">
                                                    <span class="avatar-sm rounded-circle bg-soft-primary text-primary d-flex align-items-center justify-content-center me-2" style="width:28px;height:28px;">{{ $convInitial }}</span>
                                                    {{ $conv->user->name }}
                                                </span>
                                            @else
                                                <i class="bi bi-person"></i> {{ $conv->user->name }}
                                            @endif
                                            • <i class="bi bi-chat"></i> {{ $conv->messages->count() }} messages
                                        </small>
                                    </div>
                                    <span class="badge bg-info">
                                        {{ ucfirst($conv->status) }}
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('portal.admin.support.conversations') }}" class="btn btn-sm btn-outline-primary">
                            View All Conversations <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                @else
                    <p class="text-muted mb-0">No active conversations</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Unread Messages -->
    @if($unreadMessages->count() > 0)
        <div class="row">
            <div class="col-lg-12">
                <div class="card-section">
                    <h5>
                        <i class="bi bi-envelope-open-fill"></i> Unread Messages
                    </h5>
                    <div class="list-group list-group-flush">
                        @foreach($unreadMessages as $msg)
                            <a href="{{ route('portal.admin.support.conversation.show', $msg->conversation) }}" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">{{ $msg->user->name }}: {{ Str::limit($msg->conversation->subject, 50) }}</h6>
                                        <p class="mb-1 text-muted">{{ Str::limit($msg->message, 100) }}</p>
                                        <small class="text-muted">{{ $msg->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
