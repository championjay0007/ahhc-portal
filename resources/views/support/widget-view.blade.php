<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Support Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { background:#f3f4f6; }
        .chat-card { max-width:900px;margin:3rem auto;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.08); }
        .chat-header { padding:1rem 1.25rem;border-bottom:1px solid #e6e7ea;background:#fff; }
        .chat-window { height:60vh;padding:1rem;overflow-y:auto;background:#f9fafb; }
        .chat-bubble { max-width:70%;padding:10px 14px;border-radius:14px;margin-bottom:8px;display:inline-block }
        .chat-bubble.user { background:#fff;border:1px solid #e5e7eb;color:#111 }
        .chat-bubble.admin { background:#0b5ed7;color:#fff }
        .chat-form { padding:1rem;border-top:1px solid #e6e7ea;background:#fff }
    </style>
</head>
<body>
<div class="chat-card">
    <div class="chat-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">{{ $conversation->subject }}</h5>
            <small class="text-muted">Messages: {{ $messages->count() }}</small>
        </div>
        <div>
            <small class="text-muted">Started: {{ $conversation->created_at->format('M d, Y') }}</small>
        </div>
    </div>

    <div id="chatWindow" class="chat-window">
        @foreach($messages as $msg)
            <div class="d-flex {{ $msg->is_admin ? 'justify-content-end' : 'justify-content-start' }}">
                <div class="chat-bubble {{ $msg->is_admin ? 'admin' : 'user' }}">
                    {!! nl2br(e($msg->message)) !!}
                    <div class="small text-muted mt-1">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
        @endforeach
    </div>

    <form id="widgetForm" class="chat-form">
        @csrf
        <div class="input-group">
            <textarea id="widgetMessage" name="message" class="form-control" rows="1" placeholder="Type your message..." required></textarea>
            <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i></button>
        </div>
    </form>
</div>

<script>
    const conversationId = {{ $conversation->id }};
    const token = encodeURIComponent('{{ $conversation->public_token }}');
    const chatWindow = document.getElementById('chatWindow');
    const widgetForm = document.getElementById('widgetForm');
    const messageInput = document.getElementById('widgetMessage');

    function scrollBottom(){ chatWindow.scrollTop = chatWindow.scrollHeight; }

    async function fetchMessages(){
        try{
            const res = await fetch(`/support/widget/${conversationId}?token=${token}`);
            if(!res.ok) return;
            const payload = await res.json();
            if(!payload || !payload.messages) return;
            chatWindow.innerHTML = payload.messages.map(m => {
                const side = m.is_admin ? 'justify-content-end' : 'justify-content-start';
                const cls = m.is_admin ? 'admin' : 'user';
                return `<div class="d-flex ${side}"><div class="chat-bubble ${cls}">${escapeHtml(m.text).replace(/\n/g,'<br>')}<div class="small text-muted mt-1">${m.created_at}</div></div></div>`;
            }).join('');
            scrollBottom();
        }catch(e){ console.error(e); }
    }

    function escapeHtml(text){ const div=document.createElement('div'); div.textContent=text; return div.innerHTML; }

    widgetForm.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const text = messageInput.value.trim(); if(!text) return;
        try{
            const fd = new FormData(widgetForm);
            const res = await fetch(`/support/widget/${conversationId}/message?token=${token}`, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if(!res.ok){ alert('Unable to send message'); return; }
            messageInput.value = '';
            await fetchMessages();
        }catch(err){ console.error(err); }
    });

    fetchMessages();
    setInterval(fetchMessages, 4000);
    scrollBottom();
</script>
</body>
</html>
