@extends('layouts.admin')

@section('title', 'Дашборд')

@push('styles')
<style>
    /* ===== GXON-Style Dashboard ===== */

    .dashboard-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .dashboard-header h1 {
        font-size: 28px;
        font-weight: 800;
        color: var(--fg-1);
        margin: 0;
    }

    .dashboard-header h1 span {
        color: var(--accent);
    }

    .header-actions {
        display: flex;
        gap: 12px;
    }

    /* ===== KPI Cards Grid ===== */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 24px;
    }

    @media (max-width: 1200px) {
        .kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 640px) {
        .kpi-grid { grid-template-columns: 1fr; }
    }

    .kpi-card {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
    }

    .kpi-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .kpi-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }

    .kpi-card__icon.blue { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .kpi-card__icon.green { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .kpi-card__icon.yellow { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .kpi-card__icon.purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }
    .kpi-card__icon.red { background: rgba(229, 39, 22, 0.12); color: #E52716; }

    .kpi-card__change {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .kpi-card__change.up {
        background: rgba(34, 197, 94, 0.12);
        color: #22c55e;
    }

    .kpi-card__change.down {
        background: rgba(239, 68, 68, 0.12);
        color: #ef4444;
    }

    .kpi-card__value {
        font-size: 36px;
        font-weight: 800;
        color: var(--fg-1);
        line-height: 1;
        margin-bottom: 8px;
    }

    .kpi-card__label {
        font-size: 14px;
        color: var(--fg-3);
        font-weight: 500;
    }

    .kpi-card__footer {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--br);
        font-size: 13px;
        color: var(--fg-3);
    }

    .kpi-card__footer a {
        color: var(--accent);
        font-weight: 600;
    }

    /* ===== Dashboard Grid ===== */
    .dashboard-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 24px;
    }

    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }

    .dashboard-main {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .dashboard-sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* ===== Charts Section ===== */
    .charts-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    @media (max-width: 900px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }

    .chart-card {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        overflow: hidden;
    }

    .chart-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--br);
    }

    .chart-card__title {
        font-size: 16px;
        font-weight: 700;
        color: var(--fg-1);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chart-card__title i {
        color: var(--accent);
    }

    .chart-card__body {
        padding: 20px;
    }

    /* ===== Table Card ===== */
    .table-card {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        overflow: hidden;
    }

    .table-card__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--br);
    }

    .table-card__title {
        font-size: 16px;
        font-weight: 700;
        color: var(--fg-1);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .table-card__title i {
        color: var(--accent);
    }

    .table-card .table {
        margin: 0;
    }

    .table-card .table th {
        background: transparent;
        border-bottom: 1px solid var(--br);
    }

    .table-card .table th:first-child,
    .table-card .table td:first-child {
        padding-left: 24px;
    }

    .table-card .table th:last-child,
    .table-card .table td:last-child {
        padding-right: 24px;
    }

    /* ===== Activity Feed ===== */
    .activity-feed {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        overflow: hidden;
    }

    .activity-feed__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--br);
    }

    .activity-feed__title {
        font-size: 16px;
        font-weight: 700;
        color: var(--fg-1);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .activity-feed__title i {
        color: var(--accent);
    }

    .activity-feed__list {
        max-height: 420px;
        overflow-y: auto;
    }

    .activity-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--br);
        transition: background 0.15s;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item:hover {
        background: var(--grid);
    }

    .activity-item__icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .activity-item__icon.info { background: rgba(59, 130, 246, 0.12); color: #3b82f6; }
    .activity-item__icon.success { background: rgba(34, 197, 94, 0.12); color: #22c55e; }
    .activity-item__icon.warning { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
    .activity-item__icon.danger { background: rgba(239, 68, 68, 0.12); color: #ef4444; }
    .activity-item__icon.purple { background: rgba(139, 92, 246, 0.12); color: #8b5cf6; }

    .activity-item__content {
        flex: 1;
        min-width: 0;
    }

    .activity-item__title {
        font-weight: 600;
        color: var(--fg-1);
        margin-bottom: 2px;
    }

    .activity-item__desc {
        font-size: 13px;
        color: var(--fg-3);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .activity-item__time {
        font-size: 12px;
        color: var(--fg-3);
        white-space: nowrap;
    }

    /* ===== Quick Stats Card ===== */
    .quick-stats {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        overflow: hidden;
    }

    .quick-stats__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--br);
    }

    .quick-stats__title {
        font-size: 16px;
        font-weight: 700;
        color: var(--fg-1);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .quick-stats__title i {
        color: var(--accent);
    }

    .quick-stats__body {
        padding: 20px 24px;
    }

    .quick-stat-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 0;
        border-bottom: 1px solid var(--br);
    }

    .quick-stat-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .quick-stat-item:first-child {
        padding-top: 0;
    }

    .quick-stat-item__label {
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--fg-2);
        font-weight: 500;
    }

    .quick-stat-item__label i {
        width: 20px;
        text-align: center;
    }

    .quick-stat-item__value {
        font-weight: 700;
        color: var(--fg-1);
    }

    /* ===== AI Status Card ===== */
    .ai-status-card {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px;
        padding: 24px;
        color: white;
        position: relative;
        overflow: hidden;
    }

    .ai-status-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(229, 39, 22, 0.1) 0%, transparent 50%);
        pointer-events: none;
    }

    .ai-status-card__header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        position: relative;
    }

    .ai-status-card__icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }

    .ai-status-card__title {
        font-size: 18px;
        font-weight: 700;
    }

    .ai-status-card__subtitle {
        font-size: 13px;
        opacity: 0.7;
    }

    .ai-stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        position: relative;
    }

    .ai-stat {
        text-align: center;
    }

    .ai-stat__value {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }

    .ai-stat__value.success { color: #22c55e; }
    .ai-stat__value.error { color: #ef4444; }

    .ai-stat__label {
        font-size: 12px;
        opacity: 0.7;
    }

    .ai-status-card__footer {
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        text-align: center;
    }

    .ai-status-card__footer a {
        color: rgba(255, 255, 255, 0.8);
        font-size: 13px;
        font-weight: 600;
        transition: color 0.2s;
    }

    .ai-status-card__footer a:hover {
        color: white;
    }

    /* ===== Popular Vacancies ===== */
    .vacancy-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 24px;
        border-bottom: 1px solid var(--br);
        transition: background 0.15s;
    }

    .vacancy-list-item:last-child {
        border-bottom: none;
    }

    .vacancy-list-item:hover {
        background: var(--grid);
    }

    .vacancy-list-item__info {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        flex: 1;
    }

    .vacancy-list-item__rank {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: var(--grid);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 700;
        color: var(--fg-3);
        flex-shrink: 0;
    }

    .vacancy-list-item:first-child .vacancy-list-item__rank {
        background: var(--accent);
        color: white;
    }

    .vacancy-list-item__title {
        font-weight: 600;
        color: var(--fg-1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .vacancy-list-item__count {
        background: var(--accent);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    /* ===== Candidate Row ===== */
    .candidate-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .candidate-row__avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        object-fit: cover;
    }

    .candidate-row__info {
        min-width: 0;
    }

    .candidate-row__name {
        font-weight: 600;
        color: var(--fg-1);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .candidate-row__email {
        font-size: 12px;
        color: var(--fg-3);
    }

    /* ===== AI Kanban ===== */
    .kanban-wrapper {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        padding: 20px;
    }

    .kanban-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .kanban-columns {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        overflow-x: auto;
    }

    @media (max-width: 1280px) {
        .kanban-columns { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 900px) {
        .kanban-columns { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    .kanban-column {
        background: var(--bg-2);
        border: 1px solid var(--br);
        border-radius: 14px;
        padding: 12px;
        min-height: 140px;
    }

    .kanban-column__title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 700;
        font-size: 14px;
        margin-bottom: 12px;
        color: var(--fg-1);
    }

    .kanban-column__title .count {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 12px;
    }

    .kanban-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .kanban-card {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
    }

    .kanban-card__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
    }

    .kanban-card__name {
        font-weight: 700;
        color: var(--fg-1);
    }

    .kanban-card__vacancy {
        font-size: 12px;
        color: var(--fg-3);
    }

    .kanban-card__chips {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    .chip {
        background: var(--bg-2);
        border: 1px solid var(--br);
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        color: var(--fg-2);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .chip.match-high { border-color: #22c55e; color: #16a34a; }
    .chip.match-mid { border-color: #f59e0b; color: #d97706; }
    .chip.match-low { border-color: #ef4444; color: #b91c1c; }

    .kanban-card__meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        font-size: 12px;
        color: var(--fg-3);
    }

    /* Modal */
    .ai-modal {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
    }

    .ai-modal__dialog {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        width: min(960px, 96vw);
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .ai-modal__header, .ai-modal__footer {
        padding: 16px 20px;
        border-bottom: 1px solid var(--br);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .ai-modal__body {
        padding: 20px;
        overflow-y: auto;
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 16px;
    }

    @media (max-width: 900px) {
        .ai-modal__body { grid-template-columns: 1fr; }
    }

    .ai-block {
        background: var(--bg-2);
        border: 1px solid var(--br);
        border-radius: 12px;
        padding: 12px;
    }

    .ai-block h4 {
        margin: 0 0 8px;
        font-size: 14px;
        color: var(--fg-1);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .ai-list {
        display: grid;
        gap: 6px;
        padding-left: 16px;
        color: var(--fg-2);
        font-size: 13px;
    }

    .ai-empty {
        color: var(--fg-3);
        font-size: 13px;
    }
</style>
@endpush

@section('content')
<!-- Dashboard Header -->
<div class="dashboard-header">
    <h1>Добро пожаловать, <span>{{ auth()->user()->name }}</span></h1>
    <div class="header-actions">
        <a href="{{ route('admin.applications.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-file-lines"></i> Заявки
        </a>
        <a href="{{ route('admin.vacancies.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Новая вакансия
        </a>
    </div>
</div>

<!-- KPI Cards -->
<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon blue">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            @if($stats['vacancies_active'] > 0)
            <div class="kpi-card__change up">
                <i class="fa-solid fa-arrow-up"></i>
                {{ $stats['vacancies_active'] }}
            </div>
            @endif
        </div>
        <div class="kpi-card__value">{{ $stats['vacancies_total'] }}</div>
        <div class="kpi-card__label">Всего вакансий</div>
        <div class="kpi-card__footer">
            <a href="{{ route('admin.vacancies.index') }}">Смотреть все <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon green">
                <i class="fa-solid fa-file-lines"></i>
            </div>
            @if($changes['applications']['value'] > 0)
            <div class="kpi-card__change {{ $changes['applications']['direction'] }}">
                <i class="fa-solid fa-arrow-{{ $changes['applications']['direction'] }}"></i>
                {{ $changes['applications']['value'] }}%
            </div>
            @endif
        </div>
        <div class="kpi-card__value">{{ $stats['applications_total'] }}</div>
        <div class="kpi-card__label">Всего заявок</div>
        <div class="kpi-card__footer">
            <a href="{{ route('admin.applications.index') }}">Смотреть все <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon yellow">
                <i class="fa-solid fa-inbox"></i>
            </div>
            @if($stats['applications_new'] > 0)
            <div class="kpi-card__change up">
                <i class="fa-solid fa-circle"></i>
                Новые
            </div>
            @endif
        </div>
        <div class="kpi-card__value">{{ $stats['applications_new'] }}</div>
        <div class="kpi-card__label">Новых заявок</div>
        <div class="kpi-card__footer">
            <a href="{{ route('admin.applications.index', ['status' => 'new']) }}">Обработать <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>

    <div class="kpi-card">
        <div class="kpi-card__header">
            <div class="kpi-card__icon purple">
                <i class="fa-solid fa-trophy"></i>
            </div>
            @if($changes['hired']['value'] > 0)
            <div class="kpi-card__change {{ $changes['hired']['direction'] }}">
                <i class="fa-solid fa-arrow-{{ $changes['hired']['direction'] }}"></i>
                {{ $changes['hired']['value'] }}%
            </div>
            @endif
        </div>
        <div class="kpi-card__value">{{ $stats['applications_hired'] }}</div>
        <div class="kpi-card__label">Принято на работу</div>
        <div class="kpi-card__footer">
            <a href="{{ route('admin.applications.index', ['status' => 'hired']) }}">История <i class="fa-solid fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-grid mb-3">
    <!-- Line Chart: Applications Trend -->
    <div class="chart-card">
        <div class="chart-card__header">
            <div class="chart-card__title">
                <i class="fa-solid fa-chart-line"></i>
                Динамика заявок
            </div>
            <span class="text-muted" style="font-size: 13px;">Последние 14 дней</span>
        </div>
        <div class="chart-card__body">
            <div id="applicationsChart" style="height: 280px;"></div>
        </div>
    </div>

    <!-- Donut Chart: Status Distribution -->
    <div class="chart-card">
        <div class="chart-card__header">
            <div class="chart-card__title">
                <i class="fa-solid fa-chart-pie"></i>
                Статусы заявок
            </div>
        </div>
        <div class="chart-card__body">
            <div id="statusChart" style="height: 280px;"></div>
        </div>
    </div>
</div>

@php $kanbanGrouped = collect($kanbanApplications)->groupBy('status'); @endphp
<div class="kanban-wrapper mb-3">
    <div class="kanban-header">
        <div>
            <div class="text-muted" style="font-size: 12px;">AI-поток</div>
            <h3 style="margin:0;font-size:18px;color:var(--fg-1);">Канбан кандидатов (анализ AI)</h3>
        </div>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline">
            Все отклики
        </a>
    </div>
    <div class="kanban-columns">
        @foreach($kanbanColumns as $column)
            @php $items = $kanbanGrouped->get($column['key'], collect()); @endphp
            <div class="kanban-column">
                <div class="kanban-column__title">
                    <span>{{ $column['title'] }}</span>
                    <span class="count">{{ $items->count() }}</span>
                </div>
                <div class="kanban-list">
                    @forelse($items as $item)
                        @php
                            $scoreClass = is_null($item['match_score'])
                                ? 'chip'
                                : ($item['match_score'] >= 80 ? 'chip match-high' : ($item['match_score'] >= 60 ? 'chip match-mid' : 'chip match-low'));
                            $firstStrength = $item['analysis']['strengths'][0] ?? null;
                            $strongSkills = array_slice(array_map(fn($s) => $s['name'] ?? $s, $item['strong_skills'] ?? []), 0, 2);
                        @endphp
                        <div class="kanban-card" data-id="{{ $item['id'] }}">
                            <div class="kanban-card__header">
                                <div>
                                    <div class="kanban-card__name">{{ $item['name'] }}</div>
                                    <div class="kanban-card__vacancy">{{ Str::limit($item['vacancy'], 38) }}</div>
                                </div>
                                <span class="{{ $scoreClass }}">
                                    @if(is_null($item['match_score']))
                                        AI
                                    @else
                                        {{ $item['match_score'] }}%
                                    @endif
                                </span>
                            </div>
                            <div class="kanban-card__chips">
                                @if($firstStrength)
                                    <span class="chip">
                                        <i class="fa-solid fa-thumbs-up"></i>
                                        {{ Str::limit($firstStrength, 40) }}
                                    </span>
                                @endif
                                @foreach($strongSkills as $skill)
                                    <span class="chip"><i class="fa-solid fa-bolt"></i> {{ Str::limit($skill, 20) }}</span>
                                @endforeach
                            </div>
                            <div class="kanban-card__meta">
                                <span>{{ $item['position_title'] ?? '—' }}</span>
                                <span>{{ $item['status_label'] }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="ai-empty">Нет кандидатов</div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="dashboard-grid">
    <!-- Main Content -->
    <div class="dashboard-main">
        <!-- Recent Applications Table -->
        <div class="table-card">
            <div class="table-card__header">
                <div class="table-card__title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Последние заявки
                </div>
                <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-outline">Все заявки</a>
            </div>
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Кандидат</th>
                            <th>Вакансия</th>
                            <th>Статус</th>
                            <th>Match</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentApplications as $application)
                            <tr class="clickable" onclick="window.location='{{ route('admin.applications.show', $application) }}'">
                                <td>
                                    <div class="candidate-row">
                                        <img src="{{ $application->candidate->avatar_url }}" alt="" class="candidate-row__avatar">
                                        <div class="candidate-row__info">
                                            <div class="candidate-row__name">{{ $application->candidate->name }}</div>
                                            <div class="candidate-row__email">{{ $application->candidate->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ Str::limit($application->vacancy->title, 30) }}</td>
                                <td>
                                    <span class="badge badge-{{ $application->status->value }}">
                                        {{ $application->status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($application->match_score !== null)
                                        <span class="match-score {{ $application->match_score >= 60 ? 'high' : ($application->match_score >= 40 ? 'medium' : 'low') }}">
                                            {{ $application->match_score }}%
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $application->created_at->format('d.m.Y') }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state" style="padding: 40px 20px;">
                                        <i class="fa-solid fa-inbox"></i>
                                        <h3>Нет заявок</h3>
                                        <p class="text-muted">Заявки появятся здесь, когда кандидаты откликнутся на вакансии</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="dashboard-sidebar">
        <!-- AI Status -->
        <div class="ai-status-card">
            <div class="ai-status-card__header">
                <div class="ai-status-card__icon">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <div class="ai-status-card__title">AI-робот</div>
                    <div class="ai-status-card__subtitle">Статистика за 24 часа</div>
                </div>
            </div>
            <div class="ai-stats-row">
                <div class="ai-stat">
                    <div class="ai-stat__value">{{ $aiStats['total'] }}</div>
                    <div class="ai-stat__label">Операций</div>
                </div>
                <div class="ai-stat">
                    <div class="ai-stat__value success">{{ $aiStats['success'] }}</div>
                    <div class="ai-stat__label">Успешно</div>
                </div>
                <div class="ai-stat">
                    <div class="ai-stat__value error">{{ $aiStats['errors'] }}</div>
                    <div class="ai-stat__label">Ошибок</div>
                </div>
            </div>
            <div class="ai-status-card__footer">
                <a href="{{ route('admin.ai.logs') }}">
                    Посмотреть логи <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="activity-feed">
            <div class="activity-feed__header">
                <div class="activity-feed__title">
                    <i class="fa-solid fa-bolt"></i>
                    Активность
                </div>
            </div>
            <div class="activity-feed__list">
                @forelse($recentActivity as $activity)
                    <div class="activity-item">
                        <div class="activity-item__icon {{ $activity['color'] }}">
                            <i class="fa-solid {{ $activity['icon'] }}"></i>
                        </div>
                        <div class="activity-item__content">
                            <div class="activity-item__title">{{ $activity['title'] }}</div>
                            <div class="activity-item__desc">{{ $activity['description'] }}</div>
                        </div>
                        <div class="activity-item__time">{{ $activity['time']->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="activity-item">
                        <div class="activity-item__content" style="text-align: center; padding: 20px;">
                            <span class="text-muted">Нет активности</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Popular Vacancies -->
        <div class="quick-stats">
            <div class="quick-stats__header">
                <div class="quick-stats__title">
                    <i class="fa-solid fa-fire"></i>
                    Популярные вакансии
                </div>
            </div>
            @forelse($popularVacancies as $index => $vacancy)
                <a href="{{ route('admin.vacancies.show', $vacancy) }}" class="vacancy-list-item" style="text-decoration: none;">
                    <div class="vacancy-list-item__info">
                        <div class="vacancy-list-item__rank">{{ $index + 1 }}</div>
                        <div class="vacancy-list-item__title">{{ Str::limit($vacancy->title, 25) }}</div>
                    </div>
                    <div class="vacancy-list-item__count">{{ $vacancy->applications_count }}</div>
                </a>
            @empty
                <div style="padding: 24px; text-align: center;">
                    <span class="text-muted">Нет вакансий</span>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- AI Modal -->
<div class="ai-modal" id="aiModal" aria-hidden="true">
    <div class="ai-modal__dialog">
        <div class="ai-modal__header">
            <div>
                <div class="text-muted" style="font-size:12px;">Кандидат</div>
                <h3 id="modalCandidateName" style="margin:0;"></h3>
                <div class="text-muted" id="modalVacancy" style="font-size:12px;"></div>
            </div>
            <button class="btn btn-sm btn-outline" type="button" id="aiModalClose">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="ai-modal__body">
            <div class="ai-block">
                <h4><i class="fa-solid fa-user"></i> Профиль</h4>
                <div class="ai-list" id="modalProfile"></div>
                <div class="ai-block" style="margin-top:10px;">
                    <h4><i class="fa-solid fa-heart-pulse"></i> Сильные стороны</h4>
                    <div class="ai-list" id="modalStrengths"></div>
                </div>
                <div class="ai-block" style="margin-top:10px;">
                    <h4><i class="fa-solid fa-triangle-exclamation"></i> Риски / слабые стороны</h4>
                    <div class="ai-list" id="modalRisks"></div>
                </div>
            </div>
            <div class="ai-block">
                <h4><i class="fa-solid fa-signal"></i> Матч</h4>
                <div id="modalMatchScore" style="font-weight:700;font-size:24px;margin-bottom:10px;">—</div>
                <div class="ai-list" id="modalContacts"></div>
                <div class="ai-block" style="margin-top:10px;">
                    <h4><i class="fa-solid fa-circle-question"></i> Вопросы</h4>
                    <div class="ai-list" id="modalQuestions"></div>
                </div>
                <div class="ai-block" style="margin-top:10px;">
                    <h4><i class="fa-solid fa-lightbulb"></i> Рекомендация</h4>
                    <div class="ai-list" id="modalRecommendation"></div>
                </div>
            </div>
        </div>
        <div class="ai-modal__footer">
            <div id="modalStatus" class="text-muted"></div>
            <button class="btn btn-primary" type="button" id="aiModalClose2">Закрыть</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme colors
    const isDark = document.body.classList.contains('theme-dark');
    const textColor = isDark ? '#cdd6ff' : '#64748b';
    const gridColor = isDark ? '#1f2740' : '#e2e8f0';

    // Applications Line Chart
    const applicationsChart = new ApexCharts(document.querySelector("#applicationsChart"), {
        series: [{
            name: 'Заявки',
            data: @json($applicationsChart['data'])
        }],
        chart: {
            type: 'area',
            height: 280,
            toolbar: { show: false },
            fontFamily: 'Manrope, sans-serif',
            background: 'transparent'
        },
        colors: ['#E52716'],
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.4,
                opacityTo: 0.1,
                stops: [0, 90, 100]
            }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        dataLabels: { enabled: false },
        xaxis: {
            categories: @json($applicationsChart['labels']),
            labels: {
                style: { colors: textColor, fontSize: '12px' }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { colors: textColor, fontSize: '12px' }
            }
        },
        grid: {
            borderColor: gridColor,
            strokeDashArray: 4
        },
        tooltip: {
            theme: isDark ? 'dark' : 'light',
            y: {
                formatter: function(val) {
                    return val + ' заявок';
                }
            }
        }
    });
    applicationsChart.render();

    // Status Donut Chart
    const statusData = @json($statusChart);
    const statusDonutChart = new ApexCharts(document.querySelector("#statusChart"), {
        series: statusData.map(s => s.count),
        chart: {
            type: 'donut',
            height: 280,
            fontFamily: 'Manrope, sans-serif',
            background: 'transparent'
        },
        colors: statusData.map(s => s.color),
        labels: statusData.map(s => s.status),
        legend: {
            position: 'bottom',
            labels: { colors: textColor }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Всего',
                            fontSize: '14px',
                            fontWeight: 600,
                            color: textColor,
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
        dataLabels: { enabled: false },
        tooltip: {
            theme: isDark ? 'dark' : 'light'
        }
    });
    statusDonutChart.render();

    // Re-render charts on theme change
    window.addEventListener('themeChanged', function() {
        location.reload();
    });

    // ===== AI Kanban Modal =====
    const kanbanData = @json($kanbanApplications);
    const kanbanMap = new Map();
    kanbanData.forEach(app => kanbanMap.set(String(app.id), app));

    const modal = document.getElementById('aiModal');
    const modalCandidateName = document.getElementById('modalCandidateName');
    const modalVacancy = document.getElementById('modalVacancy');
    const modalMatchScore = document.getElementById('modalMatchScore');
    const modalStatus = document.getElementById('modalStatus');
    const modalProfile = document.getElementById('modalProfile');
    const modalStrengths = document.getElementById('modalStrengths');
    const modalRisks = document.getElementById('modalRisks');
    const modalContacts = document.getElementById('modalContacts');
    const modalQuestions = document.getElementById('modalQuestions');
    const modalRecommendation = document.getElementById('modalRecommendation');

    function fillList(el, items, emptyText = 'Нет данных') {
        el.innerHTML = '';
        if (!items || !items.length) {
            el.innerHTML = `<div class="ai-empty">${emptyText}</div>`;
            return;
        }
        items.forEach((item) => {
            const div = document.createElement('div');
            div.textContent = `• ${item}`;
            el.appendChild(div);
        });
    }

    function openAiModal(id) {
        const app = kanbanMap.get(String(id));
        if (!app) return;

        modalCandidateName.textContent = app.name || 'Кандидат';
        modalVacancy.textContent = app.vacancy || '—';
        modalMatchScore.textContent = app.match_score !== null && app.match_score !== undefined
            ? `${app.match_score}%`
            : 'Нет данных';
        modalStatus.textContent = app.status_label || '';

        const profileItems = [];
        if (app.position_title) profileItems.push(`Желаемая позиция: ${app.position_title}`);
        if (app.domains && app.domains.length) profileItems.push(`Домены: ${app.domains.join(', ')}`);
        fillList(modalProfile, profileItems, 'Профиль пока не собран');

        fillList(modalStrengths, app.analysis?.strengths || [], 'Сильные стороны пока не определены');
        const risksAndWeaknesses = [
            ...(app.analysis?.weaknesses || []),
            ...(app.analysis?.risks || []),
        ];
        fillList(modalRisks, risksAndWeaknesses, 'Риски/слабые стороны не найдены');

        const contacts = [];
        if (app.contact?.email) contacts.push(`Email: ${app.contact.email}`);
        if (app.contact?.phone) contacts.push(`Телефон: ${app.contact.phone}`);
        if (app.contact?.pin) contacts.push(`PIN: ${app.contact.pin}`);
        fillList(modalContacts, contacts, 'Контакты не указаны');

        fillList(modalQuestions, app.analysis?.questions || [], 'Вопросы не сгенерированы');
        fillList(modalRecommendation, app.analysis?.recommendation ? [app.analysis.recommendation] : [], 'Нет рекомендаций');

        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeAiModal() {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }

    document.querySelectorAll('.kanban-card').forEach(card => {
        card.addEventListener('click', () => openAiModal(card.dataset.id));
    });

    document.getElementById('aiModalClose')?.addEventListener('click', closeAiModal);
    document.getElementById('aiModalClose2')?.addEventListener('click', closeAiModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeAiModal();
    });
});
</script>
@endpush
