@extends('employee.layouts.app')

@section('title', $action->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('employee.discipline.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i>Назад
            </a>
            <h4 class="mb-1">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ $action->title }}
            </h4>
        </div>
        <div>
            <span class="badge bg-{{ $action->type_color }} me-2">{{ $action->type_label }}</span>
            <span class="badge bg-{{ $action->status_color }}">{{ $action->status_label }}</span>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Description Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Подробности</h5>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Описание</h6>
                        <p class="mb-0">{{ $action->description }}</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-2">Причина</h6>
                        <p class="mb-0">{{ $action->reason }}</p>
                    </div>

                    @if($action->fine_amount)
                        <div class="alert alert-danger">
                            <i class="bi bi-currency-exchange me-2"></i>
                            <strong>Сумма штрафа:</strong>
                            {{ number_format($action->fine_amount, 0, '.', ' ') }} {{ $action->fine_currency }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Appeal Section -->
            @if($action->appeal_text)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Жалоба</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">
                                Отправлена: {{ $action->appealed_at->format('d.m.Y H:i') }}
                            </small>
                        </div>
                        <p class="mb-3">{{ $action->appeal_text }}</p>

                        @if($action->appeal_status)
                            <div class="alert alert-{{ $action->appeal_status === 'approved' ? 'success' : ($action->appeal_status === 'rejected' ? 'danger' : 'info') }}">
                                <strong>Статус:</strong>
                                @switch($action->appeal_status)
                                    @case('pending')
                                        На рассмотрении
                                        @break
                                    @case('approved')
                                        Принята
                                        @break
                                    @case('rejected')
                                        Отклонена
                                        @break
                                @endswitch
                            </div>
                        @endif

                        @if($action->appeal_resolution)
                            <div class="mt-3">
                                <h6 class="text-muted mb-2">Решение</h6>
                                <p class="mb-0">{{ $action->appeal_resolution }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Acknowledgement Section -->
            @if(!$action->employee_acknowledged)
                <div class="card shadow-sm border-warning">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">Подтвердите ознакомление</h5>
                        <p class="text-muted mb-4">
                            Вам необходимо подтвердить ознакомление с данной дисциплинарной мерой.
                        </p>
                        <button class="btn btn-warning btn-lg" onclick="acknowledgeAction()">
                            <i class="bi bi-check-lg me-2"></i>Ознакомлен
                        </button>
                    </div>
                </div>
            @else
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    Вы подтвердили ознакомление с данной мерой
                    <small class="d-block mt-1">{{ $action->acknowledged_at->format('d.m.Y H:i') }}</small>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Даты</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Дата инцидента:</td>
                            <td class="text-end fw-medium">{{ $action->incident_date->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Дата меры:</td>
                            <td class="text-end fw-medium">{{ $action->action_date->format('d.m.Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Вступает в силу:</td>
                            <td class="text-end fw-medium">{{ $action->effective_from->format('d.m.Y') }}</td>
                        </tr>
                        @if($action->effective_until)
                            <tr>
                                <td class="text-muted">Срок действия:</td>
                                <td class="text-end fw-medium">{{ $action->effective_until->format('d.m.Y') }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-list-ul me-2"></i>Дополнительная информация</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Категория:</td>
                            <td class="text-end">
                                @switch($action->category)
                                    @case('attendance')
                                        Посещаемость
                                        @break
                                    @case('performance')
                                        Качество работы
                                        @break
                                    @case('conduct')
                                        Поведение
                                        @break
                                    @case('policy_violation')
                                        Нарушение политики
                                        @break
                                    @default
                                        {{ $action->category }}
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Степень тяжести:</td>
                            <td class="text-end">{{ $action->severity_label }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Создал:</td>
                            <td class="text-end">{{ $action->createdBy->name }}</td>
                        </tr>
                        @if($action->approvedBy)
                            <tr>
                                <td class="text-muted">Утвердил:</td>
                                <td class="text-end">{{ $action->approvedBy->name }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Appeal Card -->
            @if($action->can_still_appeal)
                <div class="card shadow-sm border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Подать жалобу</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">
                            Вы можете подать жалобу на данную меру.
                            @if($action->appeal_deadline)
                                <br>
                                <strong>Срок:</strong> до {{ $action->appeal_deadline->format('d.m.Y') }}
                            @endif
                        </p>
                        <button class="btn btn-warning w-100" onclick="showAppealModal()">
                            <i class="bi bi-send me-1"></i>Отправить жалобу
                        </button>
                    </div>
                </div>
            @endif
        </div>
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
    // Char count
    const appealText = document.getElementById('appealText');
    if (appealText) {
        appealText.addEventListener('input', function() {
            document.getElementById('charCount').textContent = this.value.length;
        });
    }

    // Appeal form
    const appealForm = document.getElementById('appealForm');
    if (appealForm) {
        appealForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const text = document.getElementById('appealText').value;

            try {
                const response = await fetch('{{ route("employee.discipline.appeal", $action) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ appeal_text: text })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.error || 'Произошла ошибка');
                }
            } catch (error) {
                alert('Ошибка соединения с сервером');
            }
        });
    }
});

function showAppealModal() {
    document.getElementById('appealText').value = '';
    document.getElementById('charCount').textContent = '0';
    new bootstrap.Modal(document.getElementById('appealModal')).show();
}

async function acknowledgeAction() {
    if (!confirm('Подтверждаете ознакомление?')) {
        return;
    }

    try {
        const response = await fetch('{{ route("employee.discipline.acknowledge", $action) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.error || 'Произошла ошибка');
        }
    } catch (error) {
        alert('Ошибка соединения с сервером');
    }
}
</script>
@endpush
@endsection
