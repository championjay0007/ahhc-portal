@extends('layouts.portal')

@section('title', 'Chat with ' . $recipient->name)

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card chat-card shadow-sm">
                <div class="card-header chat-card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1">{{ $recipient->name }}</h4>
                        <div class="text-muted small">Live chat with your assigned support contact</div>
                    </div>
                    <span class="badge bg-success">Live</span>
                </div>

                <div id="chatWindow" class="chat-window">
                    @foreach($messages as $message)
                        <div class="chat-message {{ $message->sender_id === auth()->id() ? 'chat-message-right' : 'chat-message-left' }}">
                            <div class="chat-bubble {{ $message->sender_id === auth()->id() ? 'chat-bubble-right' : 'chat-bubble-left' }}">
                                <div class="chat-meta small text-muted mb-2">
                                    {{ $message->sender->name }} • {{ $message->created_at->format('H:i') }}
                                </div>
                                <div>{!! nl2br(e($message->body)) !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <form id="chatForm" action="{{ route($messageRoutePrefix.'conversation.send', $recipient->id) }}" method="POST" class="chat-form">
                    @csrf
                    <div class="input-group chat-input-group">
                        <textarea id="messageInput" name="message" class="form-control chat-input" rows="2" placeholder="Type your message..." autocomplete="off" required></textarea>
                        <button class="btn btn-primary chat-send-btn" type="submit">
                            <i class="bi bi-send"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const recipientId = {{ $recipient->id }};
    const chatWindow = document.getElementById('chatWindow');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const messagesEndpoint = '{{ route($messageRoutePrefix.'conversation.messages', $recipient->id) }}';
    const sendEndpoint = '{{ route($messageRoutePrefix.'conversation.send', $recipient->id) }}';
    const csrfToken = '{{ csrf_token() }}';

    function scrollChatToBottom() {
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function renderMessages(messages) {
        chatWindow.innerHTML = messages.map(msg => {
            const isMe = msg.is_me;
            const alignment = isMe ? 'chat-message-right' : 'chat-message-left';
            const bubbleClass = isMe ? 'chat-bubble-right' : 'chat-bubble-left';
            return `
                <div class="chat-message ${alignment}">
                    <div class="chat-bubble ${bubbleClass}">
                        <div class="chat-meta small text-muted mb-2">${escapeHtml(msg.author)} • ${escapeHtml(msg.created_at)}</div>
                        <div>${escapeHtml(msg.text).replace(/\n/g, '<br>')}</div>
                    </div>
                </div>
            `;
        }).join('');
        scrollChatToBottom();
    }

    async function fetchMessages() {
        try {
            const response = await fetch(messagesEndpoint, {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            if (!response.ok) return;
            const data = await response.json();
            renderMessages(data.messages);
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
            const response = await fetch(sendEndpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
                credentials: 'same-origin',
            });

            if (!response.ok) {
                console.error('Unable to send chat message, status:', response.status);
                return;
            }

            messageInput.value = '';
            messageInput.focus();
            await fetchMessages();
        } catch (error) {
            console.error('Unable to send chat message:', error);
        }
    });

    messageInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            chatForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        }
    });

    fetchMessages();
    setInterval(fetchMessages, 4000);
    scrollChatToBottom();
</script>

<style>
    .chat-card {
        display: flex;
        flex-direction: column;
        min-height: 75vh;
        border-radius: 24px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .chat-card-header {
        background: #fff;
        padding: 22px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .chat-window {
        flex: 1 1 auto;
        padding: 24px;
        background: #f4f7fb;
        overflow-y: auto;
        min-height: 0;
        scroll-behavior: smooth;
    }

    .chat-message {
        display: flex;
        margin-bottom: 18px;
    }

    .chat-message-left {
        justify-content: flex-start;
    }

    .chat-message-right {
        justify-content: flex-end;
    }

    .chat-bubble {
        max-width: 78%;
        padding: 16px 18px;
        border-radius: 22px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
        line-height: 1.6;
    }

    .chat-bubble-left {
        background: #ffffff;
        border-bottom-left-radius: 6px;
    }

    .chat-bubble-right {
        background: #0d6efd;
        color: #fff;
        border-bottom-right-radius: 6px;
    }

    .chat-form {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        padding: 18px 22px;
        position: sticky;
        bottom: 0;
        z-index: 1;
    }

    .chat-input-group {
        gap: 0.5rem;
    }

    .chat-input {
        border-radius: 16px;
        resize: none;
        min-height: 50px;
    }

    .chat-send-btn {
        border-radius: 16px;
        min-width: 56px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .chat-window {
            padding: 16px;
        }

        .chat-card-header,
        .chat-form {
            padding-left: 16px;
            padding-right: 16px;
        }
    }
</style>
@endsection
