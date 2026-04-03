@extends('employee.layouts.app')

@section('title', 'Чат — ' . $chat->hr->name)

@section('content')
<style>
    .chat-window { display:flex; flex-direction:column; height:calc(100vh - 140px); background:var(--panel); border:1px solid var(--br); border-radius:16px; overflow:hidden; }

    .chat-header { display:flex; align-items:center; gap:14px; padding:16px 20px; border-bottom:1px solid var(--br); background:var(--panel); }
    .chat-header .back-btn { width:36px; height:36px; border-radius:10px; border:1px solid var(--br); background:transparent; color:var(--fg-3); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; text-decoration:none; }
    .chat-header .back-btn:hover { border-color:var(--accent); color:var(--accent); }
    .chat-header .avatar { width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; color:#fff; font-size:1rem; flex-shrink:0; }
    .chat-header .info h6 { margin:0; font-weight:700; font-size:15px; color:var(--fg-1); }
    .chat-header .info small { color:var(--fg-3); font-size:12px; }
    .chat-header .status-badge { font-size:11px; padding:3px 10px; border-radius:8px; background:rgba(34,197,94,0.15); color:#22c55e; font-weight:600; }

    .chat-messages { flex:1; overflow-y:auto; padding:20px; display:flex; flex-direction:column; gap:8px; }
    .chat-messages .date-divider { text-align:center; margin:12px 0; font-size:12px; color:var(--fg-3); }
    .chat-messages .date-divider span { background:var(--panel); padding:4px 14px; border-radius:8px; border:1px solid var(--br); }

    .msg { display:flex; max-width:75%; }
    .msg.mine { align-self:flex-end; }
    .msg.theirs { align-self:flex-start; }
    .msg .bubble { padding:10px 16px; border-radius:16px; font-size:14px; line-height:1.45; word-break:break-word; }
    .msg.mine .bubble { background:var(--accent); color:#fff; border-bottom-right-radius:4px; }
    .msg.theirs .bubble { background:var(--grid, #f0f0f5); color:var(--fg-1); border-bottom-left-radius:4px; }
    .msg .time { font-size:11px; color:var(--fg-3); margin-top:3px; padding:0 4px; }
    .msg.mine .time { text-align:right; }

    .chat-empty { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:var(--fg-3); }
    .chat-empty i { font-size:48px; opacity:0.15; margin-bottom:12px; }

    .chat-input { display:flex; gap:10px; padding:14px 20px; border-top:1px solid var(--br); background:var(--panel); }
    .chat-input input { flex:1; padding:12px 18px; border:1px solid var(--br); border-radius:12px; background:transparent; color:var(--fg-1); font-size:14px; outline:none; transition:border 0.2s; }
    .chat-input input:focus { border-color:var(--accent); }
    .chat-input button { width:48px; height:48px; border-radius:12px; border:none; background:var(--accent); color:#fff; font-size:1.1rem; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; }
    .chat-input button:hover { opacity:0.9; transform:scale(1.05); }
</style>

@php
    $colors = ['#3B82F6','#8B5CF6','#E52716','#22c55e','#f59e0b','#ec4899','#06b6d4'];
    $color = $colors[($chat->hr?->id ?? 0) % count($colors)];
@endphp

<div class="chat-window">
    {{-- Header --}}
    <div class="chat-header">
        <a href="{{ route('employee.staff-chat.index') }}" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="avatar" style="background:{{ $color }};">
            {{ mb_strtoupper(mb_substr($chat->hr->name, 0, 1)) }}
        </div>
        <div class="info">
            <h6>{{ $chat->hr->name }}</h6>
            <small>{{ $chat->hr->email }}</small>
        </div>
        <span class="status-badge">HR менеджер</span>
    </div>

    {{-- Messages --}}
    <div class="chat-messages" id="messagesContainer">
        @forelse($messages as $msg)
            <div class="msg {{ $msg->sender_id === auth()->id() ? 'mine' : 'theirs' }}">
                <div>
                    <div class="bubble">{{ $msg->message }}</div>
                    <div class="time">{{ $msg->formatted_time }}</div>
                </div>
            </div>
        @empty
            <div class="chat-empty">
                <i class="fa-solid fa-paper-plane"></i>
                <p>Напишите первое сообщение</p>
            </div>
        @endforelse
    </div>

    {{-- Input --}}
    <form id="messageForm" class="chat-input">
        <input type="text" id="messageInput" placeholder="Напишите сообщение..." autocomplete="off" autofocus>
        <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
</div>

<script>
const CHAT_ID = @json($chat->id);
const MY_ID = @json(auth()->id());
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;
let lastMessageId = @json($messages->last()?->id ?? 0);

const container = document.getElementById('messagesContainer');
container.scrollTop = container.scrollHeight;

document.getElementById('messageForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const input = document.getElementById('messageInput');
    const text = input.value.trim();
    if (!text) return;
    input.value = '';

    appendMessage({ message: text, formatted_time: 'Сейчас', is_mine: true });

    try {
        const res = await fetch(`/staff-chat/${CHAT_ID}/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        if (data.message) lastMessageId = data.message.id;
    } catch (e) {
        console.error('Send error:', e);
    }
});

function appendMessage(msg) {
    // Remove empty state
    const empty = container.querySelector('.chat-empty');
    if (empty) empty.remove();

    const div = document.createElement('div');
    div.className = 'msg ' + (msg.is_mine ? 'mine' : 'theirs');
    div.innerHTML = `<div><div class="bubble">${escapeHtml(msg.message)}</div><div class="time">${msg.formatted_time}</div></div>`;
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

// Real-time via Echo
if (typeof Echo !== 'undefined') {
    Echo.private(`staff-chat.${CHAT_ID}`)
        .listen('.message.sent', (e) => {
            if (e.senderId === MY_ID) return;
            appendMessage({ message: e.body, formatted_time: 'Сейчас', is_mine: false });
            lastMessageId = e.messageId;
        });
} else {
    // Polling fallback
    setInterval(async () => {
        try {
            const res = await fetch(`/staff-chat/${CHAT_ID}/messages?last_id=${lastMessageId}`);
            const { messages } = await res.json();
            messages.forEach(m => {
                if (!m.is_mine) appendMessage(m);
                lastMessageId = m.id;
            });
        } catch(e) {}
    }, 3000);
}
</script>
@endsection
