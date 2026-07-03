@extends('layouts.portal')

@section('title', 'Support Ticket #' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT))

@push('styles')
    <style>
        .ticket-header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
        }
        .ticket-header-section h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .header-badges {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: center;
        }
        .badge-large {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .ticket-details-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .detail-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-item label {
            font-weight: 700;
            color: #667eea;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            display: block;
        }
        .detail-item value {
            color: #333;
            font-size: 1rem;
            display: block;
        }
        .conversation-thread {
            background: white;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .conversation-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #667eea;
        }
        .message {
            margin-bottom: 1.5rem;
            padding: 1.25rem;
            border-radius: 12px;
            display: flex;
            gap: 1rem;
        }
        .message.user {
            background: #f0f2f5;
            border-left: 4px solid #667eea;
        }
        .message.admin {
            background: #e8f4f8;
            border-left: 4px solid #17a2b8;
        }
        .message-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            flex-shrink: 0;
        }
        .message.admin .message-avatar {
            background: #17a2b8;
        }
        .message-content {
            flex: 1;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.5rem;
        }
        .message-author {
            font-weight: 700;
            color: #333;
        }
        .message-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.25rem 0.6rem;
            background: rgba(102, 126, 234, 0.2);
            color: #667eea;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .message.admin .message-badge {
            background: rgba(23, 162, 184, 0.2);
            color: #17a2b8;
        }
        .message-time {
            color: #999;
            font-size: 0.85rem;
        }
        .message-text {
            color: #666;
            line-height: 1.6;
            margin: 0;
            word-break: break-word;
        }
        .reply-form {
            background: white;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            padding: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .reply-form h5 {
            color: #333;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .form-control {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .btn-secondary {
            background: #f0f0f0;
            border: 1px solid #ddd;
            color: #333;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
        }
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 8px;
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="ticket-header-section">
        <h1><i class="bi bi-ticket-detailed"></i> {{ $ticket->subject }}</h1>
        <div class="header-badges">
            <span class="badge-large">
                <i class="bi bi-hash"></i> #{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}
            </span>
            <span class="badge-large">
                <i class="bi bi-circle-fill"></i> {{ ucfirst($ticket->status) }}
            </span>
            <span class="badge-large">
                <i class="bi bi-exclamation-lg"></i> {{ ucfirst($ticket->priority) }} Priority
            </span>
        </div>
        </div>

        <div class="action-buttons" style="justify-content: flex-end; margin-bottom: 1.5rem;">
            <a href="{{ route('portal.support.conversations.index') }}" class="btn btn-secondary">
                <i class="bi bi-chat-dots"></i> View Live Chat
            </a>
        </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Ticket Details -->
    <div class="ticket-details-card">
        <h5 style="color: #667eea; font-weight: 700; margin-bottom: 1.5rem;"><i class="bi bi-info-circle"></i> Ticket Information</h5>
        
        <div class="detail-row">
            <div class="detail-item">
                <label>Category</label>
                <value>{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</value>
            </div>
            <div class="detail-item">
                <label>Status</label>
                <value>
                    <span class="status-indicator">
                        <i class="bi bi-circle-fill"></i> {{ ucfirst($ticket->status) }}
                    </span>
                </value>
            </div>
            <div class="detail-item">
                <label>Priority</label>
                <value>{{ ucfirst($ticket->priority) }}</value>
            </div>
        </div>

        <div class="detail-row">
            <div class="detail-item">
                <label>Created On</label>
                <value>{{ $ticket->created_at->format('M d, Y \a\t H:i A') }}</value>
            </div>
            <div class="detail-item">
                <label>Last Updated</label>
                <value>{{ $ticket->updated_at->format('M d, Y \a\t H:i A') }}</value>
            </div>
            <div class="detail-item">
                <label>Responses</label>
                <value>{{ $responses->count() }} {{ $responses->count() === 1 ? 'Response' : 'Responses' }}</value>
            </div>
        </div>

        @if($ticket->resolved_at)
            <div class="detail-row">
                <div class="detail-item">
                    <label>Resolved On</label>
                    <value>{{ $ticket->resolved_at->format('M d, Y \a\t H:i A') }}</value>
                </div>
            </div>
        @endif

        <div class="detail-row">
            <div class="detail-item" style="grid-column: 1 / -1;">
                <label>Description</label>
                <value style="white-space: pre-wrap; line-height: 1.6; color: #666;">{{ $ticket->description }}</value>
            </div>
        </div>
    </div>

    <!-- Conversation Thread -->
    @if($responses->count() > 0)
        <div class="conversation-thread">
            <h5 class="conversation-title"><i class="bi bi-chat-dots"></i> Conversation</h5>
            
            @foreach($responses as $response)
                <div class="message {{ $response->is_admin ? 'admin' : 'user' }}">
                    <div class="message-avatar">
                        {{ substr($response->user->name, 0, 1) }}
                    </div>
                    <div class="message-content">
                        <div class="message-header">
                            <div>
                                <span class="message-author">{{ $response->user->name }}</span>
                                @if($response->is_admin)
                                    <span class="message-badge"><i class="bi bi-shield-check"></i> Support Staff</span>
                                @endif
                            </div>
                            <span class="message-time">{{ $response->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="message-text">{{ $response->message }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div style="background: #f8f9fa; border-radius: 16px; padding: 3rem; text-align: center; margin-bottom: 2rem;">
            <i class="bi bi-chat-dots" style="font-size: 2.5rem; color: #ccc; margin-bottom: 1rem; display: block;"></i>
            <p style="color: #999; margin-bottom: 0;">No responses yet. Our support team will get back to you soon!</p>
        </div>
    @endif

    <!-- Reply Form (if ticket is open) -->
    @if($ticket->isOpen())
        <div class="reply-form">
            <h5><i class="bi bi-reply"></i> Add Your Response</h5>
            
            <form action="{{ route('portal.support.add-response', $ticket) }}" method="POST">
                @csrf

                <div class="mb-3">
                    <textarea class="form-control @error('message') is-invalid @enderror" 
                              name="message" rows="5" 
                              placeholder="Type your message here..."
                              required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send"></i> Send Response
                </button>
            </form>
        </div>

        <div class="action-buttons">
            <a href="{{ route('portal.support.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Tickets
            </a>
            <form action="{{ route('portal.support.close', $ticket) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-secondary" onclick="return confirm('Are you sure you want to close this ticket?')">
                    <i class="bi bi-x-circle"></i> Close Ticket
                </button>
            </form>
        </div>
    @else
        <div class="alert alert-info" role="alert">
            <i class="bi bi-info-circle"></i>
            <strong>Ticket Closed:</strong> This ticket has been {{ $ticket->status }}. 
            @if($ticket->status === 'closed')
                <a href="{{ route('portal.support.reopen', $ticket) }}" onclick="return confirm('Reopen this ticket?')" style="text-decoration: underline;">Reopen it</a> if you need further assistance.
            @endif
        </div>

        <a href="{{ route('portal.support.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Tickets
        </a>
    @endif
</div>
@endsection
