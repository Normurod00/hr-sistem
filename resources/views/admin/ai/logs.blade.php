@extends('layouts.admin')

@section('title', 'Логи AI')

@push('styles')
<style>
    /* ===== AI Logs Page Styles ===== */

    .logs-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .logs-header h1 {
        font-size: 28px;
        font-weight: 800;
        color: var(--fg-1);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logs-header h1 i {
        color: var(--accent);
    }

    /* ===== Stats Cards ===== */
    .logs-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }

    @media (max-width: 1100px) {
        .logs-stats { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 600px) {
        .logs-stats { grid-template-columns: 1fr; }
    }

    .logs-stat-card {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 14px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.2s;
    }

    .logs-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }

    .logs-stat-card__icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .logs-stat-card__icon.total { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
    .logs-stat-card__icon.success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .logs-stat-card__icon.error { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .logs-stat-card__icon.time { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }

    .logs-stat-card__content {
        flex: 1;
    }

    .logs-stat-card__value {
        font-size: 28px;
        font-weight: 800;
        color: var(--fg-1);
        line-height: 1;
    }

    .logs-stat-card__label {
        font-size: 13px;
        color: var(--fg-3);
        margin-top: 4px;
    }

    /* ===== Filters ===== */
    .logs-filters {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 24px;
    }

    .logs-filters__row {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        align-items: center;
    }

    .logs-filters__group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .logs-filters__label {
        font-size: 12px;
        font-weight: 600;
        color: var(--fg-3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .logs-filters__select {
        padding: 10px 36px 10px 14px;
        border: 1px solid var(--br);
        border-radius: 10px;
        background: var(--panel);
        color: var(--fg-1);
        font-size: 14px;
        font-weight: 500;
        min-width: 180px;
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .logs-filters__select:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(229, 39, 22, 0.1);
    }

    .logs-filters__input {
        padding: 10px 14px;
        border: 1px solid var(--br);
        border-radius: 10px;
        background: var(--panel);
        color: var(--fg-1);
        font-size: 14px;
        min-width: 160px;
    }

    .logs-filters__input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(229, 39, 22, 0.1);
    }

    .logs-filters__reset {
        padding: 10px 16px;
        border: 1px solid var(--error);
        border-radius: 10px;
        background: transparent;
        color: var(--error);
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        margin-top: auto;
    }

    .logs-filters__reset:hover {
        background: var(--error);
        color: white;
    }

    /* ===== Logs Timeline ===== */
    .logs-container {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 14px;
        overflow: hidden;
    }

    .logs-table-header {
        display: grid;
        grid-template-columns: 160px 1fr 120px 100px 100px 50px;
        gap: 16px;
        padding: 16px 24px;
        background: var(--grid);
        border-bottom: 1px solid var(--br);
        font-size: 12px;
        font-weight: 700;
        color: var(--fg-3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    @media (max-width: 900px) {
        .logs-table-header {
            display: none;
        }
    }

    .log-item {
        display: grid;
        grid-template-columns: 160px 1fr 120px 100px 100px 50px;
        gap: 16px;
        padding: 18px 24px;
        border-bottom: 1px solid var(--br);
        align-items: center;
        transition: background 0.15s;
    }

    .log-item:last-child {
        border-bottom: none;
    }

    .log-item:hover {
        background: var(--grid);
    }

    @media (max-width: 900px) {
        .log-item {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }
    }

    .log-item__time {
        font-size: 13px;
        color: var(--fg-3);
        display: flex;
        flex-direction: column;
    }

    .log-item__time-date {
        font-weight: 600;
        color: var(--fg-2);
    }

    .log-item__time-hour {
        font-size: 12px;
    }

    .log-item__operation {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .log-item__operation-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .log-item__operation-icon.parse_resume { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .log-item__operation-icon.parse_file { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .log-item__operation-icon.analyze { background: rgba(236, 72, 153, 0.12); color: #ec4899; }
    .log-item__operation-icon.match_score { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .log-item__operation-icon.generate_questions { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .log-item__operation-icon.build_profile { background: rgba(6, 182, 212, 0.12); color: #06b6d4; }

    .log-item__operation-info {
        min-width: 0;
    }

    .log-item__operation-name {
        font-weight: 600;
        color: var(--fg-1);
    }

    .log-item__operation-message {
        font-size: 12px;
        color: var(--fg-3);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 300px;
    }

    .log-item__operation-message.error {
        color: var(--error);
    }

    .log-item__status {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .log-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .log-status-badge.success {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }

    .log-status-badge.error {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }

    .log-status-badge.pending {
        background: rgba(245, 158, 11, 0.12);
        color: #f59e0b;
    }

    .log-status-badge i {
        font-size: 10px;
    }

    .log-item__duration {
        font-size: 14px;
        font-weight: 600;
        color: var(--fg-2);
    }

    .log-item__application {
        font-size: 13px;
    }

    .log-item__application a {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: var(--grid);
        border-radius: 6px;
        color: var(--accent);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }

    .log-item__application a:hover {
        background: var(--accent);
        color: white;
    }

    .log-item__expand {
        width: 32px;
        height: 32px;
        border: 1px solid var(--br);
        border-radius: 8px;
        background: transparent;
        color: var(--fg-3);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .log-item__expand:hover {
        background: var(--grid);
        color: var(--fg-1);
    }

    .log-item__expand.active {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
    }

    .log-item__expand.active i {
        transform: rotate(180deg);
    }

    /* ===== Log Details ===== */
    .log-details {
        display: none;
        background: var(--grid);
        padding: 20px 24px;
        border-bottom: 1px solid var(--br);
    }

    .log-details.show {
        display: block;
    }

    .log-details__grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    @media (max-width: 768px) {
        .log-details__grid {
            grid-template-columns: 1fr;
        }
    }

    .log-details__block {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 10px;
        overflow: hidden;
    }

    .log-details__block-header {
        padding: 12px 16px;
        background: var(--grid);
        border-bottom: 1px solid var(--br);
        font-size: 12px;
        font-weight: 700;
        color: var(--fg-3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .log-details__block-body {
        padding: 16px;
        max-height: 200px;
        overflow: auto;
    }

    .log-details__block-body pre {
        margin: 0;
        font-family: 'Monaco', 'Menlo', monospace;
        font-size: 12px;
        color: var(--fg-2);
        white-space: pre-wrap;
        word-break: break-all;
    }

    /* ===== Empty State ===== */
    .logs-empty {
        padding: 80px 20px;
        text-align: center;
    }

    .logs-empty__icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 20px;
        background: var(--grid);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        color: var(--fg-3);
    }

    .logs-empty__title {
        font-size: 18px;
        font-weight: 700;
        color: var(--fg-1);
        margin-bottom: 8px;
    }

    .logs-empty__text {
        font-size: 14px;
        color: var(--fg-3);
    }

    /* ===== Pagination ===== */
    .logs-pagination {
        display: flex;
        justify-content: center;
        margin-top: 24px;
    }
</style>
@endpush

@section('content')
@php
    // Подсчёт статистики для текущих фильтров
    $totalLogs = $logs->total();
    $successLogs = \App\Models\AiLog::query()
        ->when(request('status'), fn($q) => $q->where('status', request('status')))
        ->when(request('operation'), fn($q) => $q->where('operation', request('operation')))
        ->when(request('date'), fn($q) => $q->whereDate('created_at', request('date')))
        ->where('status', 'success')
        ->count();
    $errorLogs = \App\Models\AiLog::query()
        ->when(request('status'), fn($q) => $q->where('status', request('status')))
        ->when(request('operation'), fn($q) => $q->where('operation', request('operation')))
        ->when(request('date'), fn($q) => $q->whereDate('created_at', request('date')))
        ->where('status', 'error')
        ->count();
    $avgDuration = \App\Models\AiLog::query()
        ->when(request('status'), fn($q) => $q->where('status', request('status')))
        ->when(request('operation'), fn($q) => $q->where('operation', request('operation')))
        ->when(request('date'), fn($q) => $q->whereDate('created_at', request('date')))
        ->where('status', 'success')
        ->avg('duration_ms');

    $operationIcons = [
        'parse_resume' => 'fa-file-alt',
        'parse_file' => 'fa-file-pdf',
        'analyze' => 'fa-brain',
        'match_score' => 'fa-percentage',
        'generate_questions' => 'fa-question-circle',
        'build_profile' => 'fa-user-cog',
    ];
@endphp

<!-- Header -->
<div class="logs-header">
    <h1>
        <i class="fa-solid fa-robot"></i>
        Логи AI-операций
    </h1>
    <a href="{{ route('admin.ai.settings') }}" class="btn btn-secondary">
        <i class="fa-solid fa-cog"></i> Настройки AI
    </a>
</div>

<!-- Stats -->
<div class="logs-stats">
    <div class="logs-stat-card">
        <div class="logs-stat-card__icon total">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div class="logs-stat-card__content">
            <div class="logs-stat-card__value">{{ number_format($totalLogs) }}</div>
            <div class="logs-stat-card__label">Всего операций</div>
        </div>
    </div>

    <div class="logs-stat-card">
        <div class="logs-stat-card__icon success">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="logs-stat-card__content">
            <div class="logs-stat-card__value">{{ number_format($successLogs) }}</div>
            <div class="logs-stat-card__label">Успешных</div>
        </div>
    </div>

    <div class="logs-stat-card">
        <div class="logs-stat-card__icon error">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div class="logs-stat-card__content">
            <div class="logs-stat-card__value">{{ number_format($errorLogs) }}</div>
            <div class="logs-stat-card__label">Ошибок</div>
        </div>
    </div>

    <div class="logs-stat-card">
        <div class="logs-stat-card__icon time">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="logs-stat-card__content">
            <div class="logs-stat-card__value">
                {{ $avgDuration ? round($avgDuration / 1000, 1) . 'с' : '—' }}
            </div>
            <div class="logs-stat-card__label">Среднее время</div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="logs-filters">
    <form action="{{ route('admin.ai.logs') }}" method="GET" class="logs-filters__row">
        <div class="logs-filters__group">
            <span class="logs-filters__label">Статус</span>
            <select name="status" class="logs-filters__select" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Успешно</option>
                <option value="error" {{ request('status') == 'error' ? 'selected' : '' }}>Ошибка</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>В процессе</option>
            </select>
        </div>

        <div class="logs-filters__group">
            <span class="logs-filters__label">Операция</span>
            <select name="operation" class="logs-filters__select" onchange="this.form.submit()">
                <option value="">Все операции</option>
                @foreach($operations as $key => $label)
                    <option value="{{ $key }}" {{ request('operation') == $key ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="logs-filters__group">
            <span class="logs-filters__label">Дата</span>
            <input type="date" name="date" class="logs-filters__input" value="{{ request('date') }}" onchange="this.form.submit()">
        </div>

        @if(request()->hasAny(['status', 'operation', 'date']))
            <a href="{{ route('admin.ai.logs') }}" class="logs-filters__reset">
                <i class="fa-solid fa-xmark"></i> Сбросить
            </a>
        @endif
    </form>
</div>

<!-- Logs Table -->
<div class="logs-container">
    <div class="logs-table-header">
        <div>Дата/время</div>
        <div>Операция</div>
        <div>Статус</div>
        <div>Время</div>
        <div>Заявка</div>
        <div></div>
    </div>

    @forelse($logs as $log)
        <div class="log-item" data-log-id="{{ $log->id }}">
            <div class="log-item__time">
                <span class="log-item__time-date">{{ $log->created_at->format('d.m.Y') }}</span>
                <span class="log-item__time-hour">{{ $log->created_at->format('H:i:s') }}</span>
            </div>

            <div class="log-item__operation">
                <div class="log-item__operation-icon {{ $log->operation }}">
                    <i class="fa-solid {{ $operationIcons[$log->operation] ?? 'fa-cog' }}"></i>
                </div>
                <div class="log-item__operation-info">
                    <div class="log-item__operation-name">{{ $log->operation_label }}</div>
                    @if($log->message)
                        <div class="log-item__operation-message {{ $log->is_error ? 'error' : '' }}">
                            {{ Str::limit($log->message, 60) }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="log-item__status">
                <span class="log-status-badge {{ $log->status }}">
                    @if($log->is_success)
                        <i class="fa-solid fa-circle"></i>
                    @elseif($log->is_error)
                        <i class="fa-solid fa-circle"></i>
                    @else
                        <i class="fa-solid fa-circle"></i>
                    @endif
                    {{ $log->status_label }}
                </span>
            </div>

            <div class="log-item__duration">
                {{ $log->duration_formatted }}
            </div>

            <div class="log-item__application">
                @if($log->application)
                    <a href="{{ route('admin.applications.show', $log->application) }}">
                        <i class="fa-solid fa-file-lines"></i>
                        #{{ $log->application_id }}
                    </a>
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>

            <div>
                @if($log->request_data || $log->response_data)
                    <button type="button" class="log-item__expand" onclick="toggleLogDetails({{ $log->id }})">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                @endif
            </div>
        </div>

        @if($log->request_data || $log->response_data)
            <div class="log-details" id="log-details-{{ $log->id }}">
                <div class="log-details__grid">
                    @if($log->request_data)
                        <div class="log-details__block">
                            <div class="log-details__block-header">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> Request Data
                            </div>
                            <div class="log-details__block-body">
                                <pre>{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif

                    @if($log->response_data)
                        <div class="log-details__block">
                            <div class="log-details__block-header">
                                <i class="fa-solid fa-arrow-down-to-bracket"></i> Response Data
                            </div>
                            <div class="log-details__block-body">
                                <pre>{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @empty
        <div class="logs-empty">
            <div class="logs-empty__icon">
                <i class="fa-solid fa-inbox"></i>
            </div>
            <h3 class="logs-empty__title">Логи не найдены</h3>
            <p class="logs-empty__text">
                @if(request()->hasAny(['status', 'operation', 'date']))
                    Попробуйте изменить фильтры
                @else
                    AI-операции появятся здесь, когда будут обработаны заявки
                @endif
            </p>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($logs->hasPages())
    <div class="logs-pagination">
        {{ $logs->links() }}
    </div>
@endif
@endsection

@push('scripts')
<script>
function toggleLogDetails(logId) {
    const details = document.getElementById('log-details-' + logId);
    const button = document.querySelector(`[data-log-id="${logId}"] .log-item__expand`);

    if (details) {
        details.classList.toggle('show');
        button?.classList.toggle('active');
    }
}
</script>
@endpush
