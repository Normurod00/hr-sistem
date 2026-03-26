@extends('layouts.admin')

@section('title', 'Чат: ' . $application->candidate->name)
@section('header', 'Чат с кандидатом')

@push('styles')
<style>
    .chat-layout {
        display: flex;
        gap: 20px;
        height: calc(100vh - 180px);
        min-height: 500px;
    }

    .chat-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 12px;
        overflow: hidden;
    }

    .chat-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--br);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chat-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--br);
    }

    .chat-user-info h4 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 2px 0;
        color: var(--fg-1);
    }

    .chat-user-info span {
        font-size: 13px;
        color: var(--fg-3);
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
        max-width: 70%;
        display: flex;
        flex-direction: column;
    }

    .message.mine {
        align-self: flex-end;
    }

    .message.theirs {
        align-self: flex-start;
    }

    .message.system {
        align-self: center;
        max-width: 85%;
    }

    .message-bubble {
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.5;
        word-wrap: break-word;
    }

    .message.mine .message-bubble {
        background: var(--accent);
        color: #fff;
        border-bottom-right-radius: 4px;
    }

    .message.theirs .message-bubble {
        background: var(--grid);
        color: var(--fg-1);
        border-bottom-left-radius: 4px;
    }

    .message.system .message-bubble {
        background: rgba(59, 130, 246, 0.1);
        color: var(--info);
        text-align: center;
        white-space: pre-line;
    }

    .message-meta {
        font-size: 11px;
        color: var(--fg-3);
        margin-top: 4px;
        padding: 0 4px;
    }

    .message.mine .message-meta {
        text-align: right;
    }

    .message-sender {
        font-weight: 600;
        color: var(--fg-2);
        font-size: 12px;
        margin-bottom: 2px;
        padding: 0 4px;
    }

    .chat-input-container {
        padding: 16px 20px;
        border-top: 1px solid var(--br);
    }

    .chat-input-form {
        display: flex;
        gap: 12px;
    }

    .chat-input {
        flex: 1;
        padding: 12px 16px;
        border: 1px solid var(--br);
        border-radius: 24px;
        font-size: 14px;
        outline: none;
        background: var(--grid);
        color: var(--fg-1);
    }

    .chat-input:focus {
        border-color: var(--accent);
    }

    .chat-send-btn {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .chat-send-btn:hover {
        opacity: 0.9;
    }

    .chat-send-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Sidebar */
    .chat-sidebar {
        width: 320px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .sidebar-card {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 12px;
        overflow: hidden;
    }

    .sidebar-card-header {
        padding: 14px 16px;
        border-bottom: 1px solid var(--br);
        font-weight: 700;
        font-size: 14px;
        color: var(--fg-1);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sidebar-card-body {
        padding: 16px;
    }

    .candidate-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .stat-item {
        text-align: center;
        padding: 12px;
        background: var(--grid);
        border-radius: 8px;
    }

    .stat-value {
        font-size: 20px;
        font-weight: 700;
        color: var(--fg-1);
    }

    .stat-label {
        font-size: 11px;
        color: var(--fg-3);
        text-transform: uppercase;
    }

    .meeting-item {
        padding: 12px;
        background: var(--grid);
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .meeting-item:last-child {
        margin-bottom: 0;
    }

    .meeting-title {
        font-weight: 600;
        font-size: 14px;
        color: var(--fg-1);
        margin-bottom: 4px;
    }

    .meeting-time {
        font-size: 12px;
        color: var(--fg-3);
        margin-bottom: 8px;
    }

    .meeting-actions {
        display: flex;
        gap: 8px;
    }

    .btn-meeting {
        padding: 6px 12px;
        font-size: 12px;
        border-radius: 6px;
    }

    .new-meeting-btn {
        width: 100%;
        padding: 10px;
        background: var(--good);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .new-meeting-btn:hover {
        opacity: 0.9;
    }

    /* Modal */
    .meeting-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .meeting-modal.active {
        display: flex;
    }

    .meeting-modal-content {
        background: var(--panel);
        border-radius: 16px;
        width: 100%;
        max-width: 480px;
        padding: 24px;
    }

    .meeting-modal h3 {
        margin: 0 0 20px 0;
        font-size: 18px;
        color: var(--fg-1);
    }

    .meeting-form-group {
        margin-bottom: 16px;
    }

    .meeting-form-group label {
        display: block;
        font-weight: 600;
        font-size: 13px;
        color: var(--fg-2);
        margin-bottom: 6px;
    }

    .meeting-form-group input,
    .meeting-form-group textarea,
    .meeting-form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--br);
        border-radius: 8px;
        font-size: 14px;
        background: var(--grid);
        color: var(--fg-1);
    }

    .meeting-modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .empty-meetings {
        text-align: center;
        padding: 20px 0;
        color: var(--fg-3);
    }

    @media (max-width: 992px) {
        .chat-layout {
            flex-direction: column;
            height: auto;
        }

        .chat-sidebar {
            width: 100%;
            order: -1;
        }

        .chat-main {
            height: 500px;
        }
    }
</style>
@endpush

@section('content')
<div class="chat-layout">
    <!-- Main Chat -->
    <div class="chat-main">
        <div class="chat-header">
            <img src="{{ $application->candidate->avatar_url }}" alt="" class="chat-avatar">
            <div class="chat-user-info">
                <h4>{{ $application->candidate->name }}</h4>
                <span>{{ $application->vacancy->title }}</span>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-earmark-text me-1"></i> Заявка
                </a>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            @forelse($messages as $message)
                <div class="message {{ $message->sender_type === 'hr' ? 'mine' : ($message->sender_type === 'system' ? 'system' : 'theirs') }}" data-id="{{ $message->id }}">
                    @if($message->sender_type === 'candidate')
                        <div class="message-sender">{{ $message->sender->name }}</div>
                    @endif
                    <div class="message-bubble">{{ $message->message }}</div>
                    <div class="message-meta">{{ $message->formatted_time }}</div>
                </div>
            @empty
                <div class="text-center py-5" style="color: var(--fg-3);">
                    <i class="bi bi-chat-dots" style="font-size: 48px; opacity: 0.3;"></i>
                    <p class="mt-3">Начните общение с кандидатом</p>
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
        <!-- Candidate Info -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="bi bi-person"></i> Кандидат
            </div>
            <div class="sidebar-card-body">
                <div class="candidate-stats">
                    <div class="stat-item">
                        <div class="stat-value {{ $application->match_score >= 60 ? 'text-success' : 'text-warning' }}">
                            {{ $application->match_score ?? '—' }}%
                        </div>
                        <div class="stat-label">Match</div>
                    </div>
                    @if($application->candidateTest)
                        <div class="stat-item">
                            <div class="stat-value {{ $application->candidateTest->score >= 60 ? 'text-success' : 'text-warning' }}">
                                {{ $application->candidateTest->score ?? '—' }}%
                            </div>
                            <div class="stat-label">Тест</div>
                        </div>
                    @endif
                </div>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-envelope me-1"></i> {{ $application->candidate->email }}<br>
                        @if($application->candidate->phone)
                            <i class="bi bi-telephone me-1"></i> {{ $application->candidate->phone }}
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Meetings -->
        <div class="sidebar-card">
            <div class="sidebar-card-header">
                <i class="bi bi-camera-video"></i> Видео-встречи
            </div>
            <div class="sidebar-card-body">
                <div id="meetingsList">
                    @forelse($meetings as $meeting)
                        <div class="meeting-item" data-id="{{ $meeting->id }}">
                            <div class="meeting-title">{{ $meeting->title }}</div>
                            <div class="meeting-time">
                                <i class="bi bi-calendar-event me-1"></i>
                                {{ $meeting->scheduled_at->format('d.m.Y H:i') }}
                                <span class="badge bg-{{ $meeting->status_color }} ms-2">{{ $meeting->status_label }}</span>
                            </div>
                            @if($meeting->meeting_link && $meeting->status !== 'cancelled')
                                <div class="meeting-actions">
                                    <a href="{{ $meeting->meeting_link }}" target="_blank" class="btn btn-sm btn-success btn-meeting">
                                        <i class="bi bi-camera-video"></i> Открыть
                                    </a>
                                    @if($meeting->status === 'scheduled')
                                        <button class="btn btn-sm btn-outline-danger btn-meeting" onclick="cancelMeeting({{ $meeting->id }})">
                                            <i class="bi bi-x"></i> Отмена
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="empty-meetings">
                            <i class="bi bi-calendar-x" style="font-size: 32px; opacity: 0.3;"></i>
                            <p class="mb-0 mt-2">Нет встреч</p>
                        </div>
                    @endforelse
                </div>
                <button class="new-meeting-btn mt-3" onclick="openMeetingModal()">
                    <i class="bi bi-plus-circle"></i> Назначить встречу
                </button>
            </div>
        </div>

        <!-- Back -->
        <a href="{{ route('admin.chat.index') }}" class="btn btn-outline-secondary w-100">
            <i class="bi bi-arrow-left me-2"></i> Все чаты
        </a>
    </div>
</div>

<!-- Meeting Modal -->
<div class="meeting-modal" id="meetingModal">
    <div class="meeting-modal-content">
        <h3><i class="bi bi-camera-video me-2"></i>Назначить видео-встречу</h3>
        <form id="meetingForm">
            <div class="meeting-form-group">
                <label>Название</label>
                <input type="text" name="title" required placeholder="Собеседование">
            </div>
            <div class="meeting-form-group">
                <label>Дата и время</label>
                <input type="datetime-local" name="scheduled_at" required>
            </div>
            <div class="meeting-form-group">
                <label>Длительность (минуты)</label>
                <select name="duration_minutes">
                    <option value="15">15 минут</option>
                    <option value="30" selected>30 минут</option>
                    <option value="45">45 минут</option>
                    <option value="60">1 час</option>
                    <option value="90">1.5 часа</option>
                </select>
            </div>
            <div class="meeting-form-group">
                <label>Описание (опционально)</label>
                <textarea name="description" rows="2" placeholder="Дополнительная информация..."></textarea>
            </div>
            <div class="meeting-modal-actions">
                <button type="button" class="btn btn-outline-secondary flex-fill" onclick="closeMeetingModal()">Отмена</button>
                <button type="submit" class="btn btn-brb flex-fill">Создать</button>
            </div>
        </form>
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

    // Scroll to bottom
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    // Add message to chat
    function addMessage(msg) {
        const div = document.createElement('div');
        div.className = `message ${msg.sender_type === 'hr' ? 'mine' : (msg.sender_type === 'system' ? 'system' : 'theirs')}`;
        div.dataset.id = msg.id;

        let html = '';
        if (msg.sender_type === 'candidate') {
            html += `<div class="message-sender">${msg.sender_name}</div>`;
        }
        html += `<div class="message-bubble">${msg.message}</div>`;
        html += `<div class="message-meta">${msg.formatted_time}</div>`;

        div.innerHTML = html;
        chatMessages.appendChild(div);

        const emptyState = chatMessages.querySelector('.text-center.py-5');
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
            const response = await fetch('{{ route("admin.chat.send", $application) }}', {
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
            console.error('Error:', error);
            messageInput.value = message;
        } finally {
            sendBtn.disabled = false;
            messageInput.focus();
        }
    });

    // Poll for new messages
    async function pollMessages() {
        try {
            const response = await fetch(`{{ route("admin.chat.messages", $application) }}?last_id=${lastMessageId}`);
            const data = await response.json();

            if (data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    if (msg.sender_type !== 'hr') {
                        addMessage(msg);
                    }
                    if (msg.id > lastMessageId) {
                        lastMessageId = msg.id;
                    }
                });
            }
        } catch (error) {
            console.error('Error polling:', error);
        }

        setTimeout(pollMessages, 3000);
    }

    setTimeout(pollMessages, 3000);
    messageInput.focus();
});

// Meeting modal
function openMeetingModal() {
    document.getElementById('meetingModal').classList.add('active');
    // Set min datetime to now
    const now = new Date();
    now.setMinutes(now.getMinutes() + 30);
    document.querySelector('input[name="scheduled_at"]').min = now.toISOString().slice(0, 16);
}

function closeMeetingModal() {
    document.getElementById('meetingModal').classList.remove('active');
    document.getElementById('meetingForm').reset();
}

// Create meeting
document.getElementById('meetingForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    try {
        const response = await fetch('{{ route("admin.chat.meeting.create", $application) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify(data),
        });

        const result = await response.json();

        if (result.success) {
            closeMeetingModal();
            location.reload();
        } else {
            alert(result.error || 'Ошибка создания встречи');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Ошибка создания встречи');
    }
});

// Cancel meeting
async function cancelMeeting(id) {
    if (!confirm('Отменить эту встречу?')) return;

    try {
        const response = await fetch(`{{ url('admin/chat/meeting') }}/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

// Close modal on click outside
document.getElementById('meetingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMeetingModal();
    }
});
</script>
@endpush
@endsection
