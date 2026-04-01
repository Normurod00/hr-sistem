@extends('layouts.admin')

@section('title', 'Аналитика сотрудников')

@push('styles')
<style>
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-in { animation: fadeSlideUp 0.45s ease both; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.12s; }
    .animate-in:nth-child(4) { animation-delay: 0.18s; }

    .analytics-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 28px; flex-wrap: wrap; gap: 16px;
    }
    .analytics-header h1 {
        font-size: 28px; font-weight: 800; color: var(--fg-1); margin: 0;
        display: flex; align-items: center; gap: 14px;
    }
    .header-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 20px;
        box-shadow: 0 4px 14px rgba(34,197,94,0.3);
    }
    .header-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 12px;
        background: var(--accent); color: #fff;
        font-size: 13px; font-weight: 600; text-decoration: none;
        box-shadow: 0 2px 8px rgba(229,39,22,0.25);
        transition: all 0.2s;
    }
    .header-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(229,39,22,0.35); color: #fff; }

    .kpi-grid {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px;
    }
    @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

    .kpi-card {
        background: var(--panel); border: 1px solid var(--br); border-radius: 16px;
        padding: 24px; position: relative; overflow: hidden; transition: all 0.3s ease;
    }
    .kpi-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,0.08); border-color: transparent; }
    .kpi-card__accent { position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: 16px 16px 0 0; }
    .kpi-card__header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px; }
    .kpi-card__icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .kpi-card__icon.blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
    .kpi-card__icon.green { background: rgba(34,197,94,0.12); color: #22c55e; }
    .kpi-card__icon.purple { background: rgba(139,92,246,0.12); color: #8b5cf6; }
    .kpi-card__icon.orange { background: rgba(245,158,11,0.12); color: #f59e0b; }
    .kpi-card__icon.red { background: rgba(239,68,68,0.12); color: #ef4444; }
    .kpi-card__icon.teal { background: rgba(20,184,166,0.12); color: #14b8a6; }

    .kpi-card__badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
    }
    .kpi-card__badge.up { background: rgba(34,197,94,0.12); color: #22c55e; }
    .kpi-card__badge.warn { background: rgba(245,158,11,0.12); color: #f59e0b; }
    .kpi-card__badge.neutral { background: var(--grid); color: var(--fg-3); }

    .kpi-card__value { font-size: 36px; font-weight: 800; color: var(--fg-1); line-height: 1; margin-bottom: 6px; }
    .kpi-card__value .unit { font-size: 20px; color: var(--fg-3); font-weight: 600; }
    .kpi-card__label { font-size: 14px; color: var(--fg-3); font-weight: 500; }
    .kpi-card__meta {
        margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--br);
        font-size: 13px; color: var(--fg-3); display: flex; align-items: center; gap: 6px;
    }
    .kpi-card__meta strong { color: var(--fg-1); }

    .section-divider {
        display: flex; align-items: center; gap: 12px; margin-bottom: 20px; margin-top: 8px;
    }
    .section-divider__icon {
        width: 36px; height: 36px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 16px;
    }
    .section-divider__icon.docs { background: rgba(20,184,166,0.12); color: #14b8a6; }
    .section-divider__icon.ai { background: rgba(139,92,246,0.12); color: #8b5cf6; }
    .section-divider__text { font-size: 18px; font-weight: 700; color: var(--fg-1); }
    .section-divider__line { flex: 1; height: 1px; background: var(--br); }

    .a-card {
        background: var(--panel); border: 1px solid var(--br); border-radius: 16px;
        overflow: hidden; transition: box-shadow 0.3s;
    }
    .a-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .a-card__header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px; border-bottom: 1px solid var(--br);
    }
    .a-card__title {
        font-size: 16px; font-weight: 700; color: var(--fg-1);
        display: flex; align-items: center; gap: 10px;
    }
    .a-card__title i {
        width: 32px; height: 32px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(229,39,22,0.08); color: var(--accent); font-size: 14px;
    }
    .a-card__body { padding: 20px 24px; }
    .a-card__body--flush { padding: 0; }

    .a-card .table { margin: 0; }
    .a-card .table th {
        background: transparent; border-bottom: 1px solid var(--br);
        font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px;
        color: var(--fg-3); font-weight: 600; padding: 14px 20px;
    }
    .a-card .table td {
        padding: 14px 20px; vertical-align: middle; border-bottom: 1px solid var(--br);
        color: var(--fg-1); font-size: 14px;
    }
    .a-card .table tbody tr:last-child td { border-bottom: none; }
    .a-card .table tbody tr { transition: background 0.15s; }
    .a-card .table tbody tr:hover { background: var(--grid); }

    .list-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 24px; border-bottom: 1px solid var(--br); transition: background 0.15s;
    }
    .list-item:last-child { border-bottom: none; }
    .list-item:hover { background: var(--grid); }
    .list-item__text { font-size: 14px; font-weight: 600; color: var(--fg-1); }
    .list-item__count {
        min-width: 36px; text-align: center; padding: 4px 14px; border-radius: 10px;
        font-weight: 700; font-size: 13px; background: rgba(59,130,246,0.12); color: #3b82f6;
    }

    .status-pill {
        display: inline-block; padding: 4px 12px; border-radius: 8px;
        font-size: 12px; font-weight: 600;
    }

    .doc-tag {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 5px 12px; border-radius: 10px;
        font-size: 12px; font-weight: 600;
        background: var(--grid); color: var(--fg-2); border: 1px solid var(--br);
        transition: all 0.15s;
    }
    .doc-tag:hover { border-color: var(--accent); color: var(--accent); }

    .empty-state { text-align: center; padding: 48px 20px; color: var(--fg-3); }
    .empty-state i { font-size: 36px; opacity: 0.25; margin-bottom: 8px; display: block; }

    .analytics-grid { display: grid; gap: 24px; margin-bottom: 24px; }
    .analytics-grid--2-1 { grid-template-columns: 2fr 1fr; }
    .analytics-grid--5-7 { grid-template-columns: 5fr 7fr; }
    @media (max-width: 900px) { .analytics-grid--2-1, .analytics-grid--5-7 { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="analytics-header animate-in">
    <h1>
        <span class="header-icon"><i class="fa-solid fa-users-gear"></i></span>
        Аналитика сотрудников
    </h1>
    <a href="{{ route('admin.employee-documents.index') }}" class="header-btn">
        <i class="fa-solid fa-file-shield"></i> Документы сотрудников
    </a>
</div>

<!-- KPI: People & AI -->
<div class="kpi-grid">
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon blue"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="kpi-card__value">{{ number_format($kpi['total_employees']) }}</div>
        <div class="kpi-card__label">Всего сотрудников</div>
        <div class="kpi-card__meta"><i class="fa-solid fa-circle-check" style="color: #22c55e;"></i> Активных: <strong>{{ $kpi['active_employees'] }}</strong></div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #8b5cf6, #a78bfa);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon purple"><i class="fa-solid fa-comments"></i></div>
            <span class="kpi-card__badge neutral">{{ $kpi['active_conversations'] }} актив.</span>
        </div>
        <div class="kpi-card__value">{{ number_format($kpi['total_conversations']) }}</div>
        <div class="kpi-card__label">AI диалоги</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon orange"><i class="fa-solid fa-lightbulb"></i></div>
            <span class="kpi-card__badge warn"><i class="fa-solid fa-hourglass-half"></i> {{ $kpi['pending_recommendations'] }}</span>
        </div>
        <div class="kpi-card__value">{{ number_format($kpi['total_recommendations']) }}</div>
        <div class="kpi-card__label">AI рекомендации</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #22c55e, #4ade80);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon green"><i class="fa-solid fa-circle-check"></i></div>
            <span class="kpi-card__badge up"><i class="fa-solid fa-arrow-trend-up"></i> {{ $kpi['recommendation_completion_rate'] }}%</span>
        </div>
        <div class="kpi-card__value" style="color: #16a34a;">{{ $kpi['recommendation_completion_rate'] }}<span class="unit">%</span></div>
        <div class="kpi-card__label">Выполнение рекомендаций</div>
        <div class="kpi-card__meta">{{ $kpi['completed_recommendations'] }} из {{ $kpi['total_recommendations'] }}</div>
    </div>
</div>

<!-- Section: Documents -->
<div class="section-divider animate-in">
    <div class="section-divider__icon docs"><i class="fa-solid fa-file-shield"></i></div>
    <div class="section-divider__text">AI анализ документов</div>
    <div class="section-divider__line"></div>
</div>

<div class="kpi-grid">
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #14b8a6, #5eead4);"></div>
        <div class="kpi-card__header"><div class="kpi-card__icon teal"><i class="fa-solid fa-file-arrow-up"></i></div></div>
        <div class="kpi-card__value">{{ $kpi['total_documents'] }}</div>
        <div class="kpi-card__label">Документов загружено</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #22c55e, #86efac);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon green"><i class="fa-solid fa-file-circle-check"></i></div>
            <span class="kpi-card__badge up">{{ $kpi['doc_parse_rate'] }}%</span>
        </div>
        <div class="kpi-card__value" style="color: #16a34a;">{{ $kpi['parsed_documents'] }}</div>
        <div class="kpi-card__label">AI обработано</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #ef4444, #fca5a5);"></div>
        <div class="kpi-card__header"><div class="kpi-card__icon red"><i class="fa-solid fa-file-circle-xmark"></i></div></div>
        <div class="kpi-card__value" style="color: {{ $kpi['failed_documents'] > 0 ? '#dc2626' : 'var(--fg-3)' }};">{{ $kpi['failed_documents'] }}</div>
        <div class="kpi-card__label">Ошибки</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #8b5cf6, #c4b5fd);"></div>
        <div class="kpi-card__header"><div class="kpi-card__icon purple"><i class="fa-solid fa-tags"></i></div></div>
        <div class="kpi-card__label" style="margin-bottom: 10px;">Типы документов</div>
        @php $docTypeLabels = ['contract'=>'Договор','diploma'=>'Диплом','certificate'=>'Сертификат','id_document'=>'Удост.','medical'=>'Мед.','other'=>'Другое']; @endphp
        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
            @forelse($docTypeDistribution as $dt)
                <span class="doc-tag">{{ $docTypeLabels[$dt->document_type] ?? $dt->document_type }}: {{ $dt->count }}</span>
            @empty
                <span style="font-size: 13px; color: var(--fg-3);">Пока нет документов</span>
            @endforelse
        </div>
    </div>
</div>

<!-- Section: AI Usage -->
<div class="section-divider animate-in">
    <div class="section-divider__icon ai"><i class="fa-solid fa-chart-line"></i></div>
    <div class="section-divider__text">AI ассистент</div>
    <div class="section-divider__line"></div>
</div>

<div class="analytics-grid analytics-grid--2-1 animate-in">
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-chart-area"></i> Диалоги и сообщения <span style="font-weight: 400; font-size: 13px; color: var(--fg-3); margin-left: 4px;">(30 дней)</span></div>
        </div>
        <div class="a-card__body"><canvas id="usageTrend" height="280"></canvas></div>
    </div>
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-building"></i> Департаменты</div>
        </div>
        <div class="a-card__body"><canvas id="departmentChart" height="280"></canvas></div>
    </div>
</div>

<div class="analytics-grid analytics-grid--5-7 animate-in">
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-comments"></i> Типы диалогов</div>
        </div>
        <div class="a-card__body--flush">
            @php $contextLabels = ['general'=>'Общие','kpi'=>'KPI','leave'=>'Отпуск','bonus'=>'Бонус','policy'=>'Политики','complaint'=>'Жалобы']; @endphp
            @forelse($contextTypes as $ct)
            <div class="list-item">
                <span class="list-item__text">{{ $contextLabels[$ct->context_type] ?? $ct->context_type }}</span>
                <span class="list-item__count">{{ $ct->count }}</span>
            </div>
            @empty
            <div class="empty-state"><i class="fa-solid fa-inbox"></i>Нет данных</div>
            @endforelse
        </div>
    </div>
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-list-check"></i> Рекомендации по типам</div>
        </div>
        <div class="a-card__body--flush">
            @php
                $typeLabels = ['quick'=>'Быстрые','medium'=>'Средние','long'=>'Долгосрочные'];
                $statusLabels = ['pending'=>'Ожидает','in_progress'=>'В работе','completed'=>'Выполнено','dismissed'=>'Отклонено'];
                $statusColors = ['pending'=>'#f59e0b','in_progress'=>'#3b82f6','completed'=>'#22c55e','dismissed'=>'#6b7280'];
            @endphp
            <table class="table">
                <thead><tr><th>Тип</th><th>Статус</th><th style="text-align:center">Кол-во</th></tr></thead>
                <tbody>
                @forelse($recommendationTypes as $rt)
                <tr>
                    <td><strong>{{ $typeLabels[$rt->type] ?? $rt->type }}</strong></td>
                    <td>
                        <span class="status-pill" style="background: {{ ($statusColors[$rt->status] ?? '#6b7280') }}18; color: {{ $statusColors[$rt->status] ?? '#6b7280' }};">
                            {{ $statusLabels[$rt->status] ?? $rt->status }}
                        </span>
                    </td>
                    <td style="text-align:center; font-weight: 700;">{{ $rt->count }}</td>
                </tr>
                @empty
                <tr><td colspan="3"><div class="empty-state"><i class="fa-solid fa-lightbulb"></i>Нет данных</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif';
Chart.defaults.font.size = 12;
Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--fg-3').trim() || '#888';

const ctx = document.getElementById('usageTrend').getContext('2d');
const g1 = ctx.createLinearGradient(0, 0, 0, 280);
g1.addColorStop(0, 'rgba(59,130,246,0.2)'); g1.addColorStop(1, 'rgba(59,130,246,0.01)');
const g2 = ctx.createLinearGradient(0, 0, 0, 280);
g2.addColorStop(0, 'rgba(34,197,94,0.2)'); g2.addColorStop(1, 'rgba(34,197,94,0.01)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json(array_column($aiUsageTrend, 'date')),
        datasets: [
            { label: 'Диалоги', data: @json(array_column($aiUsageTrend, 'conversations')), borderColor: '#3b82f6', backgroundColor: g1, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 6, pointBackgroundColor: '#3b82f6', borderWidth: 2.5 },
            { label: 'Сообщения', data: @json(array_column($aiUsageTrend, 'messages')), borderColor: '#22c55e', backgroundColor: g2, fill: true, tension: 0.4, pointRadius: 2, pointHoverRadius: 6, pointBackgroundColor: '#22c55e', borderWidth: 2.5 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' },
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
            tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10, titleFont: { weight: '700' } } },
        scales: { y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false } }, x: { grid: { display: false } } }
    }
});

const depts = @json($departmentDistribution);
const deptColors = ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#f97316','#14b8a6','#6366f1'];
new Chart(document.getElementById('departmentChart'), {
    type: 'doughnut',
    data: {
        labels: depts.map(d => d.department || 'Не указан'),
        datasets: [{ data: depts.map(d => d.count), backgroundColor: deptColors.slice(0, depts.length), borderWidth: 0, hoverOffset: 10, spacing: 2 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } },
            tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10 } }
    }
});
</script>
@endpush
