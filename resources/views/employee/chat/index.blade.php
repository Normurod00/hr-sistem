@extends('employee.layouts.app')

@section('title', 'AI Ассистент')
@section('page-title', 'AI Ассистент')

@section('content')
<div class="row g-4">
    <!-- New Conversation -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Новый разговор</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Задайте вопрос AI-ассистенту по любой теме: KPI, отпуск, бонусы, политики банка.
                </p>

                <form id="newConversationForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Тема разговора</label>
                        <select name="context_type" class="form-select" required>
                            <option value="general">Общий вопрос</option>
                            <option value="kpi">KPI и эффективность</option>
                            <option value="leave">Отпуск и отсутствие</option>
                            <option value="bonus">Бонусы и премии</option>
                            <option value="policy">Политики и регламенты</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ваш вопрос</label>
                        <textarea name="message" class="form-control" rows="4"
                                  placeholder="Например: Почему мой KPI за октябрь ниже ожидаемого?" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-brb w-100">
                        <i class="bi bi-send me-2"></i>
                        Отправить
                    </button>
                </form>
            </div>
        </div>

        <!-- Quick Questions -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0">Частые вопросы</h6>
            </div>
            <div class="card-body p-0">
                <button class="quick-question btn btn-link text-start w-100 p-3 border-bottom" data-context="kpi"
                        data-message="Почему мой KPI за этот месяц ниже, чем в прошлом?">
                    <i class="bi bi-graph-down text-danger me-2"></i>
                    Почему упал KPI?
                </button>
                <button class="quick-question btn btn-link text-start w-100 p-3 border-bottom" data-context="bonus"
                        data-message="Когда будет начислен бонус за текущий квартал?">
                    <i class="bi bi-currency-dollar text-success me-2"></i>
                    Когда будет бонус?
                </button>
                <button class="quick-question btn btn-link text-start w-100 p-3 border-bottom" data-context="leave"
                        data-message="Сколько дней отпуска у меня осталось?">
                    <i class="bi bi-calendar-check text-info me-2"></i>
                    Остаток отпуска
                </button>
                <button class="quick-question btn btn-link text-start w-100 p-3" data-context="policy"
                        data-message="Какой порядок оформления больничного листа?">
                    <i class="bi bi-file-text text-warning me-2"></i>
                    Правила больничного
                </button>
            </div>
        </div>
    </div>

    <!-- Conversations List -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">История разговоров</h5>
                <span class="badge bg-secondary">{{ $conversations->total() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($conversations as $conv)
                    <a href="{{ route('employee.chat.show', $conv) }}"
                       class="d-flex align-items-start gap-3 p-3 border-bottom text-decoration-none text-dark conversation-item">
                        <div class="conversation-icon rounded-circle bg-light p-2 flex-shrink-0">
                            <i class="bi {{ $conv->context_type->icon() }} fs-4"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0 text-truncate">{{ $conv->display_title }}</h6>
                                <small class="text-muted flex-shrink-0 ms-2">
                                    {{ $conv->last_message_at?->diffForHumans() ?? $conv->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark">{{ $conv->context_label }}</span>
                                <span class="text-muted small">{{ $conv->message_count }} сообщений</span>
                                @if($conv->status->value === 'active')
                                    <span class="badge bg-success-subtle text-success">Активен</span>
                                @endif
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-muted"></i>
                    </a>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-chat-dots fs-1 text-muted d-block mb-3"></i>
                        <h5>Нет разговоров</h5>
                        <p class="text-muted">Начните новый разговор с AI-ассистентом</p>
                    </div>
                @endforelse
            </div>

            @if($conversations->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $conversations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .conversation-item:hover {
        background: #f8f9fa;
    }

    .quick-question {
        text-decoration: none;
        color: var(--brb-dark);
    }

    .quick-question:hover {
        background: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
    // Quick questions
    document.querySelectorAll('.quick-question').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelector('[name="context_type"]').value = this.dataset.context;
            document.querySelector('[name="message"]').value = this.dataset.message;
            document.querySelector('[name="message"]').focus();
        });
    });

    // New conversation form
    document.getElementById('newConversationForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Отправка...';

        try {
            const response = await fetch('{{ route("employee.chat.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    context_type: formData.get('context_type'),
                    message: formData.get('message'),
                }),
            });

            const data = await response.json();

            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                throw new Error(data.error || 'Ошибка при создании разговора');
            }
        } catch (error) {
            alert(error.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send me-2"></i>Отправить';
        }
    });
</script>
@endpush
