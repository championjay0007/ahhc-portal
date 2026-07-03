@extends('layouts.portal')

@section('title', 'Chat: ' . $conversation->subject)

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Main Chat Area -->
        <div class="col-lg-8">
            <!-- Chat Header Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-soft-primary text-primary me-3" style="width: 48px; height: 48px;">
                                <span class="fw-bold" style="font-size: 1.2rem;">
                                    {{ strtoupper(substr($conversation->user->name, 0, 2)) }}
                                </span>
                            </div>
                            <div>
                                <h4 class="mb-1 fw-bold">{{ $conversation->subject }}</h4>
                                <div class="d-flex align-items-center text-muted small">
                                    <i class="bi bi-person-circle me-1"></i>
                                    <span>{{ $conversation->user->name }}</span>
                                    <span class="mx-2">•</span>
                                    <i class="bi bi-clock me-1"></i>
                                    <span>{{ $conversation->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            @php
                                $statusColors = [
                                    'open' => 'success',
                                    'waiting' => 'warning',
                                    'in-progress' => 'info',
                                    'resolved' => 'secondary',
                                    'closed' => 'dark'
                                ];
                                $statusIcons = [
                                    'open' => 'bi-chat-dots',
                                    'waiting' => 'bi-hourglass-split',
                                    'in-progress' => 'bi-arrow-repeat',
                                    'resolved' => 'bi-check-circle',
                                    'closed' => 'bi-lock'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$conversation->status] ?? 'primary' }} px-3 py-2 rounded-pill d-flex align-items-center gap-1">
                                <i class="bi {{ $statusIcons[$conversation->status] ?? 'bi-chat' }}"></i>
                                {{ ucfirst($conversation->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Messages Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div id="chatWindow" class="chat-container p-4">
                        <div class="text-center mb-4">
                            <div class="chat-divider">
                                <span class="badge bg-light text-muted px-4 py-2 rounded-pill">
                                    <i class="bi bi-chat-dots me-1"></i>
                                    Conversation started {{ $conversation->created_at->format('M d, Y') }}
                                </span>
                            </div>
                        </div>
                        
                        @foreach($messages as $message)
                            <div class="message-wrapper mb-4 {{ $message->is_admin ? 'message-admin' : 'message-user' }}">
                                <div class="message-bubble {{ $message->is_admin ? 'bg-white border shadow-sm' : 'bg-primary text-white' }}">
                                    <div class="message-header d-flex align-items-center mb-2">
                                        <div class="avatar-sm rounded-circle {{ $message->is_admin ? 'bg-soft-primary text-primary' : 'bg-white bg-opacity-25 text-white' }} d-flex align-items-center justify-content-center me-2" 
                                             style="width: 28px; height: 28px; font-size: 0.7rem;">
                                            {{ strtoupper(substr($message->user->name, 0, 1)) }}
                                        </div>
                                        <span class="fw-semibold small {{ $message->is_admin ? 'text-dark' : 'text-white opacity-75' }}">
                                            {{ $message->user->name }}
                                        </span>
                                        <span class="ms-auto small {{ $message->is_admin ? 'text-muted' : 'text-white opacity-75' }}" 
                                              title="{{ $message->created_at->format('M d, Y H:i') }}">
                                            {{ $message->created_at->format('H:i') }}
                                        </span>
                                    </div>
                                    <div class="message-content">
                                        {!! nl2br(e($message->message)) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div id="typingIndicator" class="text-muted small ps-3" style="display: none;">
                            <div class="typing-dots">
                                <span></span><span></span><span></span>
                            </div>
                            Typing...
                        </div>
                    </div>

                    <!-- Chat Input -->
                    <div class="border-top p-4 bg-light rounded-bottom">
                        <form id="chatForm" action="{{ route('portal.support.conversations.message', $conversation) }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <textarea id="messageInput" 
                                          name="message" 
                                          class="form-control border-0 shadow-none bg-white" 
                                          rows="2" 
                                          placeholder="Type your message here..." 
                                          required
                                          style="resize: none; border-radius: 12px 0 0 12px;"></textarea>
                                <button id="sendButton" 
                                        class="btn btn-primary px-4" 
                                        type="submit"
                                        style="border-radius: 0 12px 12px 0;">
                                    <i class="bi bi-send-fill me-2"></i>Send
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Press Enter to send, Shift+Enter for new line
                                </small>
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>
                                    End-to-end encrypted
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Conversation Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-soft-info p-2 me-2">
                            <i class="bi bi-info-circle text-info"></i>
                        </div>
                        <h5 class="mb-0 fw-semibold">Conversation Details</h5>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-semibold mb-2">Subject</label>
                        <p class="fw-medium text-dark mb-0">{{ $conversation->subject }}</p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-6">
                            <label class="text-muted small text-uppercase fw-semibold mb-2">Status</label>
                            <div>
                                <span class="badge bg-{{ $statusColors[$conversation->status] ?? 'primary' }} px-2 py-1">
                                    {{ ucfirst($conversation->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="text-muted small text-uppercase fw-semibold mb-2">Priority</label>
                            <div>
                                @php
                                    $priorityColors = [
                                        'low' => 'success',
                                        'medium' => 'warning',
                                        'high' => 'danger',
                                        'urgent' => 'danger'
                                    ];
                                    $priorityIcons = [
                                        'low' => 'bi-arrow-down-circle',
                                        'medium' => 'bi-dash-circle',
                                        'high' => 'bi-arrow-up-circle',
                                        'urgent' => 'bi-exclamation-circle'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $priorityColors[$conversation->priority] ?? 'secondary' }} px-2 py-1 d-flex align-items-center gap-1 d-inline-flex">
                                    <i class="bi {{ $priorityIcons[$conversation->priority] ?? 'bi-circle' }}"></i>
                                    {{ ucfirst($conversation->priority) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-muted small text-uppercase fw-semibold mb-2">Timeline</label>
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-calendar-plus text-muted me-2"></i>
                            <div>
                                <small class="text-muted">Created</small>
                                <p class="mb-0 fw-medium small">{{ $conversation->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calendar-check text-muted me-2"></i>
                            <div>
                                <small class="text-muted">Last updated</small>
                                <p class="mb-0 fw-medium small">{{ $conversation->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">Quick Actions</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('portal.support.conversations.index') }}" 
                           class="btn btn-outline-secondary d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-arrow-left"></i>
                            Back to Conversations
                        </a>
                        @if($conversation->status === 'open')
                            <button class="btn btn-outline-info d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-hourglass-split"></i>
                                Mark as In Progress
                            </button>
                        @endif
                        @if(in_array($conversation->status, ['open', 'in-progress', 'waiting']))
                            <button class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle"></i>
                                Resolve Conversation
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Chat Container Styles */
    .chat-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 450px;
        max-height: 600px;
        overflow-y: auto;
    }

    /* Message Wrapper */
    .message-wrapper {
        display: flex;
        animation: slideIn 0.3s ease-out;
    }

    .message-admin {
        justify-content: flex-start;
    }

    .message-user {
        justify-content: flex-end;
    }

    /* Message Bubbles */
    .message-bubble {
        max-width: 75%;
        padding: 1rem 1.25rem;
        border-radius: 1.25rem;
        position: relative;
        transition: all 0.2s ease;
    }

    .message-bubble:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }

    .message-admin .message-bubble {
        border-radius: 1.25rem 1.25rem 1.25rem 0.25rem;
    }

    .message-user .message-bubble {
        border-radius: 1.25rem 1.25rem 0.25rem 1.25rem;
    }

    /* Message Content */
    .message-content {
        line-height: 1.6;
        font-size: 0.95rem;
    }

    /* Chat Divider */
    .chat-divider {
        position: relative;
    }

    /* Avatar Circles */
    .avatar-circle {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Typing Indicator */
    .typing-dots {
        display: inline-flex;
        gap: 4px;
        margin-right: 8px;
    }

    .typing-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #6c757d;
        animation: typing 1.4s infinite ease-in-out;
    }

    .typing-dots span:nth-child(1) { animation-delay: 0s; }
    .typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .typing-dots span:nth-child(3) { animation-delay: 0.4s; }

    /* Animations */
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes typing {
        0%, 60%, 100% {
            transform: translateY(0);
            opacity: 0.4;
        }
        30% {
            transform: translateY(-8px);
            opacity: 1;
        }
    }

    /* Scrollbar Styles */
    .chat-container::-webkit-scrollbar {
        width: 6px;
    }

    .chat-container::-webkit-scrollbar-track {
        background: transparent;
    }

    .chat-container::-webkit-scrollbar-thumb {
        background: rgba(0,0,0,0.1);
        border-radius: 3px;
    }

    .chat-container::-webkit-scrollbar-thumb:hover {
        background: rgba(0,0,0,0.2);
    }

    /* Soft Background Colors */
    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    .bg-soft-info {
        background-color: rgba(13, 202, 240, 0.1);
    }

    /* Input Focus Styles */
    #messageInput:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }

    /* Card Enhancements */
    .card {
        border-radius: 0.75rem;
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
    }
</style>

<script>
    const conversationId = {{ $conversation->id }};
    const messagesEndpoint = '{{ route('portal.support.conversations.messages', $conversation) }}';
    const sendEndpoint = '{{ route('portal.support.conversations.message', $conversation) }}';
    const csrfToken = '{{ csrf_token() }}';
    const chatWindow = document.getElementById('chatWindow');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    let isFirstLoad = true;

    // Smart scroll to bottom
    function scrollChatToBottom(smooth = true) {
        if (isFirstLoad || shouldAutoScroll()) {
            chatWindow.scrollTo({
                top: chatWindow.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }
        isFirstLoad = false;
    }

    // Check if user is near bottom
    function shouldAutoScroll() {
        const threshold = 150;
        const position = chatWindow.scrollTop + chatWindow.clientHeight;
        const height = chatWindow.scrollHeight;
        return (height - position) <= threshold;
    }

    // Escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Format timestamp
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // Fetch and render messages
    async function fetchMessages() {
        try {
            const response = await fetch(messagesEndpoint, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
            });

            if (!response.ok) return;

            const data = await response.json();
            const currentScroll = chatWindow.scrollTop;
            const wasAtBottom = shouldAutoScroll();

            const messagesHtml = data.messages.map(msg => {
                const alignment = msg.is_admin ? 'message-admin' : 'message-user';
                const bubbleStyle = msg.is_admin 
                    ? 'bg-white border shadow-sm' 
                    : 'bg-primary text-white';
                const avatarBg = msg.is_admin 
                    ? 'bg-soft-primary text-primary' 
                    : 'bg-white bg-opacity-25 text-white';
                const nameColor = msg.is_admin ? 'text-dark' : 'text-white opacity-75';
                const timeColor = msg.is_admin ? 'text-muted' : 'text-white opacity-75';

                return `
                    <div class="message-wrapper mb-4 ${alignment}">
                        <div class="message-bubble ${bubbleStyle}">
                            <div class="message-header d-flex align-items-center mb-2">
                                <div class="avatar-sm rounded-circle ${avatarBg} d-flex align-items-center justify-content-center me-2" 
                                     style="width: 28px; height: 28px; font-size: 0.7rem;">
                                    ${escapeHtml(msg.author.charAt(0).toUpperCase())}
                                </div>
                                <span class="fw-semibold small ${nameColor}">
                                    ${escapeHtml(msg.author)}
                                </span>
                                <span class="ms-auto small ${timeColor}" 
                                      title="${escapeHtml(msg.created_at)}">
                                    ${escapeHtml(formatTime(msg.created_at))}
                                </span>
                            </div>
                            <div class="message-content">
                                ${escapeHtml(msg.text).replace(/\n/g, '<br>')}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');

            chatWindow.innerHTML = `
                <div class="text-center mb-4">
                    <div class="chat-divider">
                        <span class="badge bg-light text-muted px-4 py-2 rounded-pill">
                            <i class="bi bi-chat-dots me-1"></i>
                            Conversation started {{ $conversation->created_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
                ${messagesHtml}
                <div id="typingIndicator" class="text-muted small ps-3" style="display: none;">
                    <div class="typing-dots">
                        <span></span><span></span><span></span>
                    </div>
                    Typing...
                </div>
            `;

            if (wasAtBottom) {
                scrollChatToBottom(false);
            }
        } catch (error) {
            console.error('Chat refresh failed:', error);
        }
    }

    // Send message
    chatForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        const text = messageInput.value.trim();
        if (!text) return;

        // Disable send button and show loading state
        sendButton.disabled = true;
        sendButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

        const formData = new FormData();
        formData.append('message', text);
        formData.append('_token', csrfToken);

        try {
            const response = await fetch(sendEndpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error('Send failed');
            }

            messageInput.value = '';
            messageInput.style.height = 'auto';
            await fetchMessages();
        } catch (error) {
            console.error('Send failed:', error);
            alert('Failed to send message. Please try again.');
        } finally {
            // Reset button state
            sendButton.disabled = false;
            sendButton.innerHTML = '<i class="bi bi-send-fill me-2"></i>Send';
            messageInput.focus();
        }
    });

    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Send on Enter (Shift+Enter for new line)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });

    // Initial load
    scrollChatToBottom();
    
    // Poll for new messages every 3 seconds
    setInterval(fetchMessages, 3000);
</script>
@endsection