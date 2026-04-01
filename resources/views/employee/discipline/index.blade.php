@extends('employee.layouts.app')

@section('title', 'Дисциплинарные меры')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Дисциплинарные меры
            </h4>
            <p class="text-muted mb-0">Список ваших дисциплинарных мер</p>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-primary">{{ $statistics['total'] }}</div>
                    <small class="text-muted">Всего</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-danger">{{ $statistics['active'] }}</div>
                    <small class="text-muted">Активные</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-warning">{{ $statistics['appealed'] }}</div>
                    <small class="text-muted">Обжалованные</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-success">{{ $statistics['revoked'] }}</div>
                    <small class="text-muted">Отменённые</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions List -->
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Список</h5>
            <div>
                <select class="form-select form-select-sm" id="statusFilter" style="width: auto;">
                    <option value="">Все</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Активные</option>
                    <option value="appealed" {{ request('status') === 'appealed' ? 'selected' : '' }}>Обжалование</option>
                    <option value="revoked" {{ request('status') === 'revoked' ? 'selected' : '' }}>Отменённые</option>
                </select>
            </div>
        </div>

        @if($actions->count() > 0)
            <div class="list-group list-group-flush">
                @foreach($actions as $action)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-{{ $action->type_color }} me-2">
                                        {{ $action->type_label }}
                                    </span>
                                    <span class="badge bg-{{ $action->status_color }}">
                                        {{ $action->status_label }}
                                    </span>
                                    @if(!$action->employee_acknowledged)
                                        <span class="badge bg-warning ms-2">
                                            <i class="bi bi-exclamation-triangle"></i> Не ознакомлен
                                        </span>
                                    @endif
                                </div>
                                <h6 class="mb-1">{{ $action->title }}</h6>
                                <p class="text-muted small mb-2">{{ Str::limit($action->description, 150) }}</p>
                                <div class="small text-muted">
                                    <i class="bi bi-calendar me-1"></i>{{ $action->incident_date->format('d.m.Y') }}
                                    <span class="mx-2">|</span>
                                    <i class="bi bi-person me-1"></i>{{ $action->createdBy->name }}
                                </div>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('employee.discipline.show', $action) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Просмотр
                                </a>
                                @if($action->can_still_appeal)
                                    <button class="btn btn-sm btn-outline-warning ms-1"
                                            onclick="showAppealModal({{ $action->id }})">
                                        <i class="bi bi-chat-left-text"></i> Жалоба
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card-footer">
                {{ $actions->links() }}
            </div>
        @else
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3">Дисциплинарных мер нет</h5>
                <p class="text-muted">У вас нет дисциплинарных мер</p>
            </div>
        @endif
    </div>
</div>

<!-- Appeal Modal -->
<div class="modal fade" id="appealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подать жалобу</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="appealForm">
                <div class="modal-body">
                    <input type="hidden" id="appealActionId">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Опишите вашу жалобу подробно. Минимум 50 символов.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Текст жалобы</label>
                        <textarea class="form-control" id="appealText" rows="5"
                                  minlength="50" maxlength="5000" required
                                  placeholder="Опишите вашу жалобу подробно..."></textarea>
                        <div class="form-text">
                            <span id="charCount">0</span>/5000
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-send me-1"></i>Отправить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status filter
    document.getElementById('statusFilter').addEventListener('change', function() {
        const url = new URL(window.location.href);
        if (this.value) {
            url.searchParams.set('status', this.value);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    });

    // Char count
    document.getElementById('appealText').addEventListener('input', function() {
        document.getElementById('charCount').textContent = this.value.length;
    });

    // Appeal form
    document.getElementById('appealForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const actionId = document.getElementById('appealActionId').value;
        const appealText = document.getElementById('appealText').value;

        try {
            const response = await fetch(`/discipline/${actionId}/appeal`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ appeal_text: appealText })
            });

            if (response.status === 419) { alert('Сессия истекла. Обновите страницу.'); return; }
            const data = await response.json();

            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.error || 'Произошла ошибка');
            }
        } catch (error) {
            console.error('Appeal error:', error);
            alert(navigator.onLine ? 'Ошибка сервера. Попробуйте позже.' : 'Нет подключения к интернету.');
        }
    });
});

function showAppealModal(actionId) {
    document.getElementById('appealActionId').value = actionId;
    document.getElementById('appealText').value = '';
    document.getElementById('charCount').textContent = '0';
    new bootstrap.Modal(document.getElementById('appealModal')).show();
}
</script>
@endpush
@endsection
