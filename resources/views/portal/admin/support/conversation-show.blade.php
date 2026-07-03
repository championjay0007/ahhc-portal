@extends('layouts.admin')

@section('title', 'Live Chat - ' . $conversation->subject)

@push('styles')
    <style>
        .admin-chat-card {
            display: flex;
            flex-direction: column;
            height: 75vh;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            background: white;
        }

        .admin-chat-header {
            background: #fff;
            padding: 18px 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-chat-header h4 {
            margin-bottom: 4px;
            font-size: 1.125rem;
            font-weight: 600;
        }

        .admin-chat-header small {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .admin-chat-window {
            flex: 1 1 auto;
            padding: 20px;
            background: #f9fafb;
            overflow-y: auto;
            min-height: 0;
            scroll-behavior: smooth;
        }

        .admin-chat-message {
            display: flex;
            margin-bottom: 16px;
        }

        .admin-chat-message-user {
            justify-content: flex-start;
        }

        .admin-chat-message-admin {
            justify-content: flex-end;
        }

        .admin-chat-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            line-height: 1.5;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .admin-chat-bubble-user {
            background: white;
            border: 1px solid #d1d5db;
            color: #1f2937;
            border-bottom-left-radius: 4px;
        }

        .admin-chat-bubble-admin {
            background: #3b82f6;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .admin-chat-meta {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 6px;
            margin-bottom: 0;
        }

        .admin-chat-form {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 16px 20px;
            position: sticky;
            bottom: 0;
            z-index: 10;
        }

        .admin-chat-input-group {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .admin-chat-input {
            border-radius: 12px;
            resize: none;
            min-height: 44px;
            flex: 1;
            border: 1px solid #d1d5db;
        }

        .admin-chat-send-btn {
            border-radius: 12px;
            min-width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .admin-chat-sidebar {
            flex-shrink: 0;
        }

        .admin-chat-sidebar-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Chat Card -->
            <div class="admin-chat-card">
                <div class="admin-chat-header">
                    <div>
                        <h4>{{ $conversation->subject }}</h4>
                        <small>Chat with {{ $conversation->user->name }}</small>
                    </div>
                    <span class="badge bg-{{ $conversation->status === 'open' ? 'danger' : ($conversation->status === 'in-progress' ? 'warning' : 'success') }}">
                        {{ ucfirst($conversation->status) }}
                    </span>
                </div>

                <!-- Messages Window -->
                <div class="admin-chat-window" id="chatWindow">
                    @foreach($messages as $msg)
                        <div class="admin-chat-message {{ $msg->is_admin ? 'admin-chat-message-admin' : 'admin-chat-message-user' }}">
                            <div>
                                <div class="admin-chat-bubble {{ $msg->is_admin ? 'admin-chat-bubble-admin' : 'admin-chat-bubble-user' }}">
                                    {{ $msg->message }}
                                </div>
                                <p class="admin-chat-meta">
                                    {{ ($msg->is_admin ? 'You' : $conversation->user->name) }} • {{ $msg->created_at->format('H:i') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Message Form -->
                <form id="chatForm" action="{{ route('portal.admin.support.conversation.message', $conversation) }}" method="POST" class="admin-chat-form">
                    @csrf
                    <div class="admin-chat-input-group">
                        <textarea id="messageInput" name="message" class="form-control admin-chat-input" rows="1" placeholder="Type your message..." autocomplete="off" required></textarea>
                        <button class="btn btn-primary admin-chat-send-btn" type="submit">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 admin-chat-sidebar">
            <!-- Status Update -->
            <div class="card admin-chat-sidebar-card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.admin.support.conversation.status', $conversation) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <option value="open" {{ $conversation->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in-progress" {{ $conversation->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="waiting" {{ $conversation->status === 'waiting' ? 'selected' : '' }}>Waiting for User</option>
                                <option value="resolved" {{ $conversation->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $conversation->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-check"></i> Update
                        </button>
                    </form>
                </div>
            </div>

            <!-- Conversation Info -->
            <div class="card admin-chat-sidebar-card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Conversation Info</h5>
                </div>
                <div class="card-body">
                    <dl class="row small">
                        <dt class="col-sm-6">User:</dt>
                        <dd class="col-sm-6">
                            <a href="{{ route('portal.admin.users.show', $conversation->user) }}">
                                {{ $conversation->user->name }}
                            </a>
                        </dd>

                        <dt class="col-sm-6">Email:</dt>
                        @php
                            $submittedEmail = $conversation->user->email;
                            if ($conversation->user->email === 'website-support@ahhc.com.au') {
                                // try to extract an email from subject like: "... <email>"
                                if (preg_match('/<([^>]+@[^>]+)>$/', $conversation->subject, $m)) {
                                    $submittedEmail = $m[1];
                                } else {
                                    // fallback: search initial message body for "Contact email:"
                                    $firstMsg = $conversation->messages()->orderBy('created_at')->first();
                                    if ($firstMsg && preg_match('/Contact email:\s*([^\s]+)/i', $firstMsg->message, $mm)) {
                                        $submittedEmail = $mm[1];
                                    }
                                }
                            }
                        @endphp
                        <dd class="col-sm-6">{{ $submittedEmail }}</dd>

                        <dt class="col-sm-6">Messages:</dt>
                        <dd class="col-sm-6" id="messageCount">{{ $messages->count() }}</dd>

                        <dt class="col-sm-6">Priority:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-{{ $conversation->priority === 'high' ? 'warning' : 'info' }}">
                                {{ ucfirst($conversation->priority) }}
                            </span>
                        </dd>

                        <dt class="col-sm-6">Started:</dt>
                        <dd class="col-sm-6">{{ $conversation->created_at->format('M d, Y') }}</dd>

                        @if($conversation->public_token)
                            <dt class="col-sm-6">Widget chat:</dt>
                            <dd class="col-sm-6">
                                <div class="d-flex align-items-center gap-2">
                                    <a id="widgetLink" href="{{ route('public.support.widget.view', $conversation) }}?token={{ $conversation->public_token }}" target="_blank" rel="noopener">
                                        Open widget view
                                    </a>
                                    <button type="button" id="copyWidgetLinkButton" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                            </dd>
                        @endif

                        @if($conversation->resolved_at)
                            <dt class="col-sm-6">Resolved:</dt>
                            <dd class="col-sm-6">{{ $conversation->resolved_at->format('M d, Y') }}</dd>
                        @endif
                    </dl>

                    <a href="{{ route('portal.admin.support.conversations') }}" class="btn btn-outline-secondary btn-sm w-100 mt-3">
                        <i class="bi bi-arrow-left"></i> Back to Conversations
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        const chatWindow = document.getElementById('chatWindow');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');
        const messageCount = document.getElementById('messageCount');
        const csrfToken = '{{ csrf_token() }}';

        function scrollChatToBottom() {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderMessages(messages) {
            chatWindow.innerHTML = messages.map(msg => {
                const isAdmin = msg.is_admin;
                const alignment = isAdmin ? 'admin-chat-message-admin' : 'admin-chat-message-user';
                const bubbleClass = isAdmin ? 'admin-chat-bubble-admin' : 'admin-chat-bubble-user';
                const author = isAdmin ? 'You' : '{{ $conversation->user->name }}';
                return `
                    <div class="admin-chat-message ${alignment}">
                        <div>
                            <div class="admin-chat-bubble ${bubbleClass}">
                                ${escapeHtml(msg.message)}
                            </div>
                            <p class="admin-chat-meta">${escapeHtml(author)} • ${escapeHtml(msg.created_at)}</p>
                        </div>
                    </div>
                `;
            }).join('');
            messageCount.textContent = messages.length;
            scrollChatToBottom();
        }

        async function fetchMessages() {
            try {
                const response = await fetch(`{{ route('portal.admin.support.conversation.messages', $conversation) }}`, {
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });
                if (!response.ok) return;
                const data = await response.json();
                renderMessages(data.messages || []);
            } catch (error) {
                console.error('Unable to refresh chat:', error);
            }
        }

        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const text = messageInput.value.trim();
            if (!text) return;

            const formData = new FormData();
            formData.append('message', text);

            try {
                const response = await fetch(`{{ route('portal.admin.support.conversation.message', $conversation) }}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                    credentials: 'same-origin',
                });

                const responseText = await response.text();
                let payload = null;
                if (responseText) {
                    try {
                        payload = JSON.parse(responseText);
                    } catch (error) {
                        console.error('Unable to parse chat response:', error);
                    }
                }

                if (!response.ok) {
                    throw new Error(payload?.message || 'Unable to send message right now.');
                }

                messageInput.value = '';
                messageInput.focus();
                await fetchMessages();
            } catch (error) {
                console.error('Unable to send message:', error);
            }
        });

        messageInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        });

        const copyWidgetLinkButton = document.getElementById('copyWidgetLinkButton');
        const widgetLink = document.getElementById('widgetLink');

        if (copyWidgetLinkButton && widgetLink) {
            copyWidgetLinkButton.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(widgetLink.href);
                    copyWidgetLinkButton.innerHTML = '<i class="bi bi-check-lg"></i>';
                    setTimeout(() => {
                        copyWidgetLinkButton.innerHTML = '<i class="bi bi-clipboard"></i>';
                    }, 2000);
                } catch (error) {
                    console.error('Unable to copy widget link:', error);
                    alert('Unable to copy the widget link. Please copy it manually.');
                }
            });
        }

        fetchMessages();
        setInterval(fetchMessages, 4000);
        scrollChatToBottom();
    </script>
@endpush

@endsection
