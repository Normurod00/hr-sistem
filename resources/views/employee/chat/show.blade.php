@extends('employee.layouts.app')

@section('title', 'Чат с AI')
@section('page-title', $conversation->display_title)

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card border-0 shadow-sm">
            <!-- Header -->
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <a href="{{ route('employee.chat.index') }}" class="btn btn-link p-0 text-muted">
                            <i class="bi bi-arrow-left fs-5"></i>
                        </a>
                        <div>
                            <h5 class="mb-0">{{ $conversation->context_label }}</h5>
                            <small class="text-muted">{{ $conversation->message_count }} сообщений</small>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @if($conversation->status->value === 'active')
                            <span class="badge bg-success">Активен</span>
                        @else
                            <span class="badge bg-secondary">{{ $conversation->status->label() }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="card-body chat-messages" id="chatMessages" style="height: 500px; overflow-y: auto;">
                @foreach($messages as $message)
                    <div class="message mb-4 {{ $message->role->value === 'user' ? 'message-user' : 'message-assistant' }}">
                        <div class="d-flex gap-3 {{ $message->role->value === 'user' ? 'flex-row-reverse' : '' }}">
                            <div class="message-avatar flex-shrink-0">
                                @if($message->role->value === 'user')
                                    <div class="avatar bg-primary text-white">
                                        {{ auth()->user()->initials }}
                                    </div>
                                @else
                                    <div class="avatar bg-dark text-white">
                                        <i class="bi bi-robot"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="message-content {{ $message->role->value === 'user' ? 'text-end' : '' }}">
                                <div class="message-bubble p-3 rounded-3 {{ $message->role->value === 'user' ? 'bg-primary text-white' : 'bg-light' }}">
                                    {!! nl2br(e($message->content)) !!}
                                </div>
                                <div class="message-meta small text-muted mt-1">
                                    {{ $message->created_at->format('H:i') }}
                                    @if($message->intent_label)
                                        · {{ $message->intent_label }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div id="typingIndicator" class="message message-assistant d-none">
                    <div class="d-flex gap-3">
                        <div class="message-avatar flex-shrink-0">
                            <div class="avatar bg-dark text-white">
                                <i class="bi bi-robot"></i>
                            </div>
                        </div>
                        <div class="message-content">
                            <div class="message-bubble p-3 rounded-3 bg-light">
                                <div class="typing-dots">
                                    <span></span><span></span><span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            @if($conversation->status->value === 'active')
                <div class="card-footer bg-white border-top">
                    <form id="messageForm" class="d-flex gap-2">
                        @csrf
                        <input type="text" name="message" class="form-control"
                               placeholder="Введите сообщение..." autocomplete="off" required>
                        <button type="submit" class="btn btn-brb px-4">
                            <i class="bi bi-send"></i>
                        </button>
                    </form>
                </div>
            @else
                <div class="card-footer bg-light text-center py-3">
                    <span class="text-muted">Разговор закрыт</span>
                    <a href="{{ route('employee.chat.index') }}" class="btn btn-sm btn-outline-brb ms-2">
                        Новый разговор
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .message-bubble {
        max-width: 80%;
        display: inline-block;
    }

    .message-user .message-bubble {
        border-bottom-right-radius: 4px !important;
    }

    .message-assistant .message-bubble {
        border-bottom-left-radius: 4px !important;
    }

    .typing-dots {
        display: flex;
        gap: 4px;
    }

    .typing-dots span {
        width: 8px;
        height: 8px;
        background: #6c757d;
        border-radius: 50%;
        animation: typing 1.4s infinite;
    }

    .typing-dots span:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing-dots span:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes typing {
        0%, 60%, 100% { opacity: 0.3; transform: translateY(0); }
        30% { opacity: 1; transform: translateY(-4px); }
    }
</style>
@endpush

@push('scripts')
<script>
    const chatMessages = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const typingIndicator = document.getElementById('typingIndicator');
    let lastMessageId = @json($messages->last()?->id ?? 0);

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Scroll to bottom
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    scrollToBottom();

    // Send message
    messageForm?.addEventListener('submit', async function(e) {
        e.preventDefault();

        const input = this.querySelector('[name="message"]');
        const message = input.value.trim();

        if (!message) return;

        // Add user message
        addMessage('user', message);
        input.value = '';

        // Show typing indicator
        typingIndicator.classList.remove('d-none');
        scrollToBottom();

        try {
            const response = await fetch('{{ route("employee.chat.message", $conversation) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message }),
            });

            if (response.status === 419) {
                typingIndicator.classList.add('d-none');
                addMessage('assistant', 'Сессия истекла. Обновите страницу (Ctrl+R).');
                return;
            }

            const data = await response.json();

            typingIndicator.classList.add('d-none');

            if (data.response) {
                addMessage('assistant', data.response, data.intent);
            } else if (data.error) {
                addMessage('assistant', data.error);
            } else {
                addMessage('assistant', 'Извините, произошла ошибка. Попробуйте ещё раз.');
            }
            if (data.message?.id) {
                lastMessageId = data.message.id;
            }
        } catch (error) {
            typingIndicator.classList.add('d-none');
            console.error('Chat error:', error);
            if (!navigator.onLine) {
                addMessage('assistant', 'Нет подключения к интернету. Проверьте соединение.');
            } else {
                addMessage('assistant', 'Ошибка сервера. Попробуйте ещё раз через несколько секунд.');
            }
        }
    });

    function addMessage(role, content, intent = null) {
        const isUser = role === 'user';
        const now = new Date();
        const time = now.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });

        const html = `
            <div class="message mb-4 message-${role}">
                <div class="d-flex gap-3 ${isUser ? 'flex-row-reverse' : ''}">
                    <div class="message-avatar flex-shrink-0">
                        ${isUser
                            ? `<div class="avatar bg-primary text-white">{{ auth()->user()->initials }}</div>`
                            : `<div class="avatar bg-dark text-white"><i class="bi bi-robot"></i></div>`
                        }
                    </div>
                    <div class="message-content ${isUser ? 'text-end' : ''}">
                        <div class="message-bubble p-3 rounded-3 ${isUser ? 'bg-primary text-white' : 'bg-light'}">
                            ${escapeHtml(content).replace(/\n/g, '<br>')}
                        </div>
                        <div class="message-meta small text-muted mt-1">
                            ${time}
                            ${intent ? ` · ${intent}` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        typingIndicator.insertAdjacentHTML('beforebegin', html);
        scrollToBottom();
    }

    // Poll for new messages (optional, for multi-tab support)
    // setInterval(async () => {
    //     try {
    //         const response = await fetch(`{{ route('employee.chat.messages', $conversation) }}?after_id=${lastMessageId}`);
    //         const data = await response.json();
    //         // Handle new messages...
    //     } catch (e) {}
    // }, 5000);
</script>
@endpush
