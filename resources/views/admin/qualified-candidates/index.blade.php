@extends('layouts.admin')

@section('title', 'Подходящие кандидаты')
@section('header', 'Подходящие кандидаты')

@section('content')
<style>
    .filter-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid #e5e5e5;
        margin-bottom: 24px;
    }

    .filter-card .card-body {
        padding: 20px 24px;
    }

    .filter-form .form-label {
        font-weight: 500;
        color: #555;
    }

    .filter-form .form-select,
    .filter-form .form-control {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 10px 14px;
        font-size: 14px;
        height: auto;
        background-color: #fff;
        color: #333;
    }

    .filter-form .form-select:focus,
    .filter-form .form-control:focus {
        border-color: var(--brb-red);
        box-shadow: 0 0 0 3px rgba(214, 0, 28, 0.15);
    }

    .filter-form .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
    }

    .candidates-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid #e5e5e5;
        overflow: hidden;
    }

    .candidates-table {
        margin-bottom: 0;
    }

    .candidates-table thead th {
        background: #f5f5f5;
        border-bottom: 2px solid #ddd;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
        white-space: nowrap;
    }

    .candidates-table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
        background: #fff;
    }

    .candidates-table tbody tr:hover td {
        background-color: #fafafa;
    }

    .candidates-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Checkbox */
    .select-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    /* Candidate Cell */
    .candidate-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .candidate-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
        flex-shrink: 0;
        background: #f0f0f0;
    }

    .candidate-info {
        min-width: 0;
    }

    .candidate-name {
        font-weight: 600;
        color: #222;
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .candidate-phone {
        font-size: 13px;
        color: #777;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Vacancy Link */
    .vacancy-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .vacancy-link:hover {
        color: var(--brb-red);
        text-decoration: underline;
    }

    /* Match Score */
    .match-score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 56px;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .match-score-badge.high {
        background: #e6f4ea;
        color: #137333;
        border-color: #a8dab5;
    }

    .match-score-badge.medium {
        background: #fff8e6;
        color: #8a6d00;
        border-color: #ffe69c;
    }

    /* Test Score Badge */
    .test-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .test-badge.completed-high {
        background: #e6f4ea;
        color: #137333;
        border-color: #a8dab5;
    }

    .test-badge.completed-medium {
        background: #fff8e6;
        color: #8a6d00;
        border-color: #ffe69c;
    }

    .test-badge.completed-low {
        background: #fce8e8;
        color: #c5221f;
        border-color: #f5b7b7;
    }

    .test-badge.not-started {
        background: #f5f5f5;
        color: #999;
        border-color: #ddd;
    }

    /* Action Buttons */
    .btn-invite {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(40, 167, 69, 0.3);
        cursor: pointer;
    }

    .btn-invite:hover {
        background: linear-gradient(135deg, #218838 0%, #1abc9c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(40, 167, 69, 0.4);
    }

    .btn-invite:disabled {
        background: #ccc;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }

    .btn-view {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background-color: #d6001c !important;
        color: #ffffff !important;
        border: 1px solid #b8001a !important;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-view:hover {
        background-color: #b8001a !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .btn-chat {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background: #0d6efd;
        color: #ffffff !important;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
    }

    .btn-chat:hover {
        background: #0b5ed7;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.4);
    }

    .action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-badge.invited {
        background: #d1e7dd;
        color: #0f5132;
    }

    /* Bulk Actions */
    .bulk-actions {
        display: none;
        align-items: center;
        gap: 16px;
        padding: 16px 24px;
        background: linear-gradient(135deg, #e7f1ff 0%, #f0f7ff 100%);
        border-bottom: 1px solid #b8d4ff;
    }

    .bulk-actions.show {
        display: flex;
    }

    .bulk-count {
        font-weight: 600;
        color: #0055cc;
    }

    .btn-bulk-invite {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        transition: all 0.2s;
    }

    .btn-bulk-invite:hover {
        background: linear-gradient(135deg, #218838 0%, #1abc9c 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
    }

    /* Stats Header */
    .stats-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 24px;
        background: #f8f8f8;
        border-bottom: 1px solid #e5e5e5;
    }

    .stats-text {
        font-size: 14px;
        color: #666;
    }

    .stats-text strong {
        color: #333;
        font-weight: 600;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        background: #fafafa;
    }

    .empty-state i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 16px;
    }

    .empty-state p {
        color: #777;
        font-size: 15px;
        margin: 0;
        font-weight: 500;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 24px;
    }

    /* Info Alert */
    .info-alert {
        background: linear-gradient(135deg, #e7f1ff 0%, #f0f7ff 100%);
        border: 1px solid #b8d4ff;
        border-radius: 10px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-alert i {
        color: #0055cc;
        font-size: 20px;
    }

    .info-alert p {
        margin: 0;
        color: #333;
        font-size: 14px;
    }

    /* Modal */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e5e5e5;
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
    }

    .modal-title {
        font-weight: 600;
        color: #333;
    }

    .modal-body {
        padding: 24px;
    }

    .modal-footer {
        padding: 16px 24px;
        border-top: 1px solid #e5e5e5;
    }
</style>

<!-- Info Alert -->
<div class="info-alert">
    <i class="bi bi-info-circle-fill"></i>
    <p>Здесь отображаются кандидаты с match score от 50% и выше. Пригласите подходящих кандидатов в чат для дальнейшего общения.</p>
</div>

<!-- Filters -->
<div class="filter-card card">
    <div class="card-body">
        <form action="{{ route('admin.qualified.index') }}" method="GET" class="filter-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-4 col-sm-6">
                    <label class="form-label small mb-1">Вакансия</label>
                    <select name="vacancy_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Все вакансии</option>
                        @foreach($vacancies as $vacancy)
                            <option value="{{ $vacancy->id }}" {{ request('vacancy_id') == $vacancy->id ? 'selected' : '' }}>
                                {{ $vacancy->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small mb-1">Минимальный Match Score</label>
                    <select name="min_score" class="form-select" onchange="this.form.submit()">
                        <option value="50" {{ request('min_score', 50) == 50 ? 'selected' : '' }}>50% и выше</option>
                        <option value="60" {{ request('min_score') == 60 ? 'selected' : '' }}>60% и выше</option>
                        <option value="70" {{ request('min_score') == 70 ? 'selected' : '' }}>70% и выше</option>
                        <option value="80" {{ request('min_score') == 80 ? 'selected' : '' }}>80% и выше</option>
                        <option value="90" {{ request('min_score') == 90 ? 'selected' : '' }}>90% и выше</option>
                    </select>
                </div>
                @if(request()->hasAny(['vacancy_id', 'min_score']))
                    <div class="col-md-2 col-sm-6">
                        <a href="{{ route('admin.qualified.index') }}" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-lg me-1"></i> Сбросить
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Candidates Table -->
<div class="candidates-card card">
    <!-- Bulk Actions -->
    <div class="bulk-actions" id="bulkActions">
        <span class="bulk-count">Выбрано: <span id="selectedCount">0</span></span>
        <button type="button" class="btn-bulk-invite" onclick="bulkInvite()">
            <i class="bi bi-chat-dots-fill"></i> Пригласить всех в чат
        </button>
    </div>

    @if($candidates->count() > 0)
        <div class="stats-header">
            <span class="stats-text">
                Показано <strong>{{ $candidates->firstItem() }}–{{ $candidates->lastItem() }}</strong> из <strong>{{ $candidates->total() }}</strong> кандидатов
            </span>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table candidates-table">
            <thead>
                <tr>
                    <th width="40">
                        <input type="checkbox" class="select-checkbox" id="selectAll" onchange="toggleSelectAll()">
                    </th>
                    <th>Кандидат</th>
                    <th>Вакансия</th>
                    <th class="text-center">Match</th>
                    <th class="text-center">Тест</th>
                    <th>Дата заявки</th>
                    <th width="280">Действия</th>
                </tr>
            </thead>
            <tbody>
                @forelse($candidates as $application)
                    @php
                        $isInvited = in_array($application->status, [\App\Enums\ApplicationStatus::Invited, \App\Enums\ApplicationStatus::Hired]);
                    @endphp
                    <tr data-id="{{ $application->id }}">
                        <td>
                            @if(!$isInvited)
                                <input type="checkbox" class="select-checkbox candidate-checkbox"
                                       value="{{ $application->id }}" onchange="updateBulkActions()">
                            @endif
                        </td>
                        <td>
                            <div class="candidate-cell">
                                <img src="{{ $application->candidate->avatar_url }}"
                                     alt="{{ $application->candidate->name }}"
                                     class="candidate-avatar">
                                <div class="candidate-info">
                                    <div class="candidate-name">{{ $application->candidate->name }}</div>
                                    <div class="candidate-phone">{{ $application->candidate->phone ?? 'Телефон не указан' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.vacancies.show', $application->vacancy) }}" class="vacancy-link">
                                {{ Str::limit($application->vacancy->title, 35) }}
                            </a>
                        </td>
                        <td class="text-center">
                            @php
                                $scoreClass = $application->match_score >= 70 ? 'high' : 'medium';
                            @endphp
                            <span class="match-score-badge {{ $scoreClass }}">
                                {{ $application->match_score }}%
                            </span>
                        </td>
                        <td class="text-center">
                            @if($application->candidateTest)
                                @php $test = $application->candidateTest; @endphp
                                @if($test->status === 'completed')
                                    @php
                                        $testClass = $test->score >= 60 ? 'completed-high' : ($test->score >= 40 ? 'completed-medium' : 'completed-low');
                                    @endphp
                                    <span class="test-badge {{ $testClass }}">
                                        <i class="bi bi-check-circle-fill"></i> {{ $test->score }}%
                                    </span>
                                @else
                                    <span class="test-badge not-started">
                                        <i class="bi bi-clock"></i> {{ $test->status === 'in_progress' ? 'Идёт' : 'Ждёт' }}
                                    </span>
                                @endif
                            @else
                                <span class="test-badge not-started">—</span>
                            @endif
                        </td>
                        <td>
                            <span style="font-size: 13px; color: #666;">{{ $application->created_at->format('d.m.Y H:i') }}</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                @if($isInvited)
                                    <a href="{{ route('admin.chat.show', $application) }}" class="btn-chat">
                                        <i class="bi bi-chat-dots-fill"></i> Чат
                                    </a>
                                @else
                                    <button type="button" class="btn-invite" onclick="inviteToChat({{ $application->id }})">
                                        <i class="bi bi-chat-dots-fill"></i> Пригласить
                                    </button>
                                @endif
                                <a href="{{ route('admin.applications.show', $application) }}" class="btn-view">
                                    <i class="bi bi-eye-fill"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <p>Подходящих кандидатов не найдено</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($candidates->hasPages())
    <div class="pagination-wrapper">
        {{ $candidates->links() }}
    </div>
@endif

<!-- Invite Modal -->
<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots-fill text-success me-2"></i>
                    Приглашение в чат
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="inviteApplicationId">
                <div class="mb-3">
                    <label class="form-label">Приветственное сообщение</label>
                    <textarea id="welcomeMessage" class="form-control" rows="4">Здравствуйте! Мы рассмотрели вашу заявку и хотели бы пригласить вас на собеседование. Пожалуйста, напишите нам удобное для вас время.</textarea>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Кандидат получит SMS-уведомление о приглашении
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" onclick="confirmInvite()">
                    <i class="bi bi-send-fill me-1"></i> Отправить приглашение
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let inviteModal;

    document.addEventListener('DOMContentLoaded', function() {
        inviteModal = new bootstrap.Modal(document.getElementById('inviteModal'));
    });

    function inviteToChat(applicationId) {
        document.getElementById('inviteApplicationId').value = applicationId;
        inviteModal.show();
    }

    function confirmInvite() {
        const applicationId = document.getElementById('inviteApplicationId').value;
        const message = document.getElementById('welcomeMessage').value;

        fetch(`/admin/qualified-candidates/${applicationId}/invite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => {
            if (response.status === 419) {
                showNotification('error', 'Сессия истекла. Обновите страницу.');
                throw new Error('CSRF token expired');
            }
            if (response.status === 422) {
                return response.json().then(d => { throw {validation: true, data: d}; });
            }
            if (!response.ok) {
                return response.json().catch(() => ({})).then(d => {
                    throw {status: response.status, data: d};
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                inviteModal.hide();
                const row = document.querySelector(`tr[data-id="${applicationId}"]`);
                const actionButtons = row.querySelector('.action-buttons');
                actionButtons.innerHTML = `
                    <a href="${data.chat_url}" class="btn-chat">
                        <i class="bi bi-chat-dots-fill"></i> Чат
                    </a>
                    <a href="/admin/applications/${applicationId}" class="btn-view">
                        <i class="bi bi-eye-fill"></i>
                    </a>
                `;
                const checkbox = row.querySelector('.candidate-checkbox');
                if (checkbox) checkbox.remove();

                showNotification('success', data.message);
            } else {
                showNotification('error', data.message || 'Не удалось пригласить кандидата');
            }
        })
        .catch(error => {
            if (error.validation) {
                const msgs = Object.values(error.data.errors || {}).flat().join(', ');
                showNotification('error', msgs || 'Ошибка валидации');
            } else if (error.status) {
                showNotification('error', error.data?.message || `Ошибка сервера (${error.status})`);
            } else if (error.message !== 'CSRF token expired') {
                showNotification('error', 'Ошибка сети. Проверьте подключение.');
            }
            console.error('Invite error:', error);
        });
    }

    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.candidate-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkActions();
    }

    function updateBulkActions() {
        const checkboxes = document.querySelectorAll('.candidate-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        if (checkboxes.length > 0) {
            bulkActions.classList.add('show');
            selectedCount.textContent = checkboxes.length;
        } else {
            bulkActions.classList.remove('show');
        }
    }

    function bulkInvite() {
        const checkboxes = document.querySelectorAll('.candidate-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => parseInt(cb.value));

        if (ids.length === 0) return;

        if (!confirm(`Пригласить ${ids.length} кандидатов в чат?`)) return;

        fetch('/admin/qualified-candidates/bulk-invite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ application_ids: ids })
        })
        .then(response => {
            if (response.status === 419) {
                showNotification('error', 'Сессия истекла. Обновите страницу.');
                throw new Error('CSRF token expired');
            }
            if (!response.ok) {
                return response.json().catch(() => ({})).then(d => {
                    throw {status: response.status, data: d};
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showNotification('success', data.message);
                setTimeout(() => location.reload(), 1000);
            } else {
                showNotification('error', data.message || 'Не удалось отправить приглашения');
            }
        })
        .catch(error => {
            if (error.status) {
                showNotification('error', error.data?.message || `Ошибка сервера (${error.status})`);
            } else if (error.message !== 'CSRF token expired') {
                showNotification('error', 'Ошибка сети. Проверьте подключение.');
            }
            console.error('Bulk invite error:', error);
        });
    }

    function showNotification(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill';

        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
        const span = document.createElement('span');
        span.textContent = message;
        notification.innerHTML = `<i class="bi bi-${icon} me-2"></i>`;
        notification.appendChild(span);

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
</script>
@endpush
@endsection
