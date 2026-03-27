@extends('layouts.app')

@section('title', 'Чат с HR — ' . $application->vacancy->title)

@section('content')
<style>
    .chat-page {
        background: #f5f6f8;
        min-height: calc(100vh - 80px);
    }

    .chat-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 20px;
    }

    .chat-header {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .chat-header h1 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 8px 0;
    }

    .chat-header-meta {
        display: flex;
        align-items: center;
        gap: 16px;
        font-size: 14px;
        color: #666;
    }

    .chat-main {
        display: flex;
        gap: 20px;
    }

    .chat-messages-container {
        flex: 1;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        height: 600px;
    }

    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .message {
        max-width: 75%;
        display: flex;
        flex-direction: column;
    }

    .message.mine {
        align-self: flex-end;
    }

    .message.theirs {
        align-self: flex-start;
    }

    .message-bubble {
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .message.mine .message-bubble {
        background: #d6001c;
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .message.theirs .message-bubble {
        background: #f0f0f0;
        color: #333;
        border-bottom-left-radius: 4px;
    }

    .message.system .message-bubble {
        background: #e3f2fd;
        color: #1976d2;
        max-width: 90%;
        align-self: center;
        text-align: center;
        white-space: pre-line;
    }

    .message-meta {
        font-size: 11px;
        color: #999;
        margin-top: 4px;
        padding: 0 4px;
    }

    .message.mine .message-meta {
        text-align: right;
    }

    .message-sender {
        font-weight: 600;
        color: #666;
        font-size: 12px;
        margin-bottom: 2px;
        padding: 0 4px;
    }

    .chat-input-container {
        padding: 16px 20px;
        border-top: 1px solid #eee;
    }

    .chat-input-form {
        display: flex;
        gap: 12px;
    }

    .chat-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid #ddd;
        border-radius: 24px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
    }

    .chat-input:focus {
        border-color: #d6001c;
    }

    .chat-send-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #d6001c;
        color: #fff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }

    .chat-send-btn:hover {
        background: #b8001a;
    }

    .chat-send-btn:disabled {
        background: #ccc;
        cursor: not-allowed;
    }

    /* Sidebar */
    .chat-sidebar {
        width: 300px;
    }

    .sidebar-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .sidebar-card h3 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 16px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .meeting-item {
        padding: 12px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .meeting-item:last-child {
        margin-bottom: 0;
    }

    .meeting-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .meeting-time {
        font-size: 13px;
        color: #666;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 8px;
    }

    .meeting-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        background: #28a745;
        color: #fff;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    .meeting-link:hover {
        background: #218838;
        color: #fff;
    }

    .empty-meetings {
        text-align: center;
        color: #999;
        font-size: 14px;
        padding: 20px 0;
    }

    @media (max-width: 768px) {
        .chat-main {
            flex-direction: column;
        }

        .chat-sidebar {
            width: 100%;
            order: -1;
        }

        .chat-messages-container {
            height: 450px;
        }
    }
</style>

<div class="chat-page">
    <div class="chat-container">
        <!-- Header -->
        <div class="chat-header">
            <h1><i class="bi bi-chat-dots me-2"></i>Чат с HR</h1>
            <div class="chat-header-meta">
                <span><i class="bi bi-briefcase me-1"></i> {{ $application->vacancy->title }}</span>
                <span class="badge bg-{{ $application->status->value === 'invited' ? 'primary' : 'success' }}">
                    {{ $application->status_label }}
                </span>
            </div>
        </div>

        <div class="chat-main">
            <!-- Messages -->
            <div class="chat-messages-container">
                <div class="chat-messages" id="chatMessages">
                    @forelse($messages as $message)
                        <div class="message {{ $message->sender_id === auth()->id() ? 'mine' : ($message->sender_type === 'system' ? 'system' : 'theirs') }}" data-id="{{ $message->id }}">
                            @if($message->sender_type !== 'system' && $message->sender_id !== auth()->id())
                                <div class="message-sender">{{ $message->sender->name }} (HR)</div>
                            @endif
                            <div class="message-bubble">{{ $message->message }}</div>
                            <div class="message-meta">{{ $message->formatted_time }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-chat-dots" style="font-size: 48px; opacity: 0.3;"></i>
                            <p class="mt-3">Начните общение с HR-специалистом</p>
                        </div>
                    @endforelse
                </div>

                <div class="chat-input-container">
                    <form class="chat-input-form" id="chatForm">
                        <input type="text" class="chat-input" id="messageInput" placeholder="Напишите сообщение..." autocomplete="off">
                        <button type="submit" class="chat-send-btn" id="sendBtn">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="chat-sidebar">
                <!-- Upcoming Meetings -->
                <div class="sidebar-card">
                    <h3><i class="bi bi-camera-video text-primary"></i> Видео-встречи</h3>
                    @if($upcomingMeetings->count())
                        @foreach($upcomingMeetings as $meeting)
                            <div class="meeting-item">
                                <div class="meeting-title">{{ $meeting->title }}</div>
                                <div class="meeting-time">
                                    <i class="bi bi-calendar-event"></i>
                                    {{ $meeting->scheduled_at->format('d.m.Y H:i') }}
                                </div>
                                @if($meeting->meeting_link)
                                    <a href="{{ $meeting->meeting_link }}" target="_blank" class="meeting-link">
                                        <i class="bi bi-camera-video"></i> Присоединиться
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="empty-meetings">
                            <i class="bi bi-calendar-x" style="font-size: 32px; opacity: 0.3;"></i>
                            <p class="mb-0 mt-2">Нет запланированных встреч</p>
                        </div>
                    @endif
                </div>

                <!-- Back link -->
                <div class="sidebar-card">
                    <a href="{{ route('profile.applications.show', $application) }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left me-2"></i> К заявке
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessages');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');

    let lastMessageId = {{ $messages->last()?->id ?? 0 }};
    let isPolling = true;

    // Scroll to bottom
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Add message to chat
    function addMessage(msg) {
        const div = document.createElement('div');
        div.className = `message ${msg.is_mine ? 'mine' : (msg.sender_type === 'system' ? 'system' : 'theirs')}`;
        div.dataset.id = msg.id;

        let html = '';
        if (msg.sender_type !== 'system' && !msg.is_mine) {
            html += `<div class="message-sender">${escapeHtml(msg.sender_name)} (HR)</div>`;
        }
        html += `<div class="message-bubble">${escapeHtml(msg.message)}</div>`;
        html += `<div class="message-meta">${escapeHtml(msg.formatted_time)}</div>`;

        div.innerHTML = html;
        chatMessages.appendChild(div);

        // Remove empty state if exists
        const emptyState = chatMessages.querySelector('.text-center.text-muted');
        if (emptyState) emptyState.remove();

        scrollToBottom();
    }

    // Send message
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const message = messageInput.value.trim();
        if (!message) return;

        sendBtn.disabled = true;
        messageInput.value = '';

        try {
            const response = await fetch('{{ route("chat.send", $application) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ message }),
            });

            const data = await response.json();

            if (data.success) {
                addMessage(data.message);
                lastMessageId = data.message.id;
            }
        } catch (error) {
            console.error('Error sending message:', error);
            messageInput.value = message;
        } finally {
            sendBtn.disabled = false;
            messageInput.focus();
        }
    });

    // Poll for new messages
    async function pollMessages() {
        if (!isPolling) return;

        try {
            const response = await fetch(`{{ route("chat.messages", $application) }}?last_id=${lastMessageId}`);
            const data = await response.json();

            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    if (!msg.is_mine) {
                        addMessage(msg);
                    }
                    if (msg.id > lastMessageId) {
                        lastMessageId = msg.id;
                    }
                });
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }

        setTimeout(pollMessages, 3000);
    }

    // Start polling
    setTimeout(pollMessages, 3000);

    // Focus input
    messageInput.focus();
});
</script>
@endpush
@endsection
