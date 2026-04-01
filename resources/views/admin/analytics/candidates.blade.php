@extends('layouts.admin')

@section('title', 'Аналитика кандидатов')

@push('styles')
<style>
    /* ===== Animate In ===== */
    @keyframes fadeSlideUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-in { animation: fadeSlideUp 0.45s ease both; }
    .animate-in:nth-child(2) { animation-delay: 0.06s; }
    .animate-in:nth-child(3) { animation-delay: 0.12s; }
    .animate-in:nth-child(4) { animation-delay: 0.18s; }

    /* ===== Header ===== */
    .analytics-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .analytics-header h1 {
        font-size: 28px;
        font-weight: 800;
        color: var(--fg-1);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .analytics-header h1 .header-icon {
        width: 44px; height: 44px; border-radius: 12px;
        background: linear-gradient(135deg, var(--accent) 0%, #ff6b4a 100%);
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 20px;
        box-shadow: 0 4px 14px rgba(229,39,22,0.25);
    }

    .period-toggle {
        display: flex;
        background: var(--grid);
        border: 1px solid var(--br);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .period-toggle a {
        padding: 9px 20px;
        font-size: 13px; font-weight: 600;
        color: var(--fg-3);
        text-decoration: none;
        border-right: 1px solid var(--br);
        transition: all 0.2s;
    }
    .period-toggle a:last-child { border-right: none; }
    .period-toggle a:hover { background: var(--panel); color: var(--fg-1); }
    .period-toggle a.active {
        background: var(--accent);
        color: #fff;
        box-shadow: 0 2px 8px rgba(229,39,22,0.3);
    }

    /* ===== KPI Grid ===== */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 28px;
    }
    @media (max-width: 1200px) { .kpi-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 640px) { .kpi-grid { grid-template-columns: 1fr; } }

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
        box-shadow: 0 16px 40px rgba(0,0,0,0.08);
        border-color: transparent;
    }
    .kpi-card__accent {
        position: absolute; top: 0; left: 0; right: 0; height: 3px;
        border-radius: 16px 16px 0 0;
    }
    .kpi-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .kpi-card__icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
    .kpi-card__icon.blue { background: rgba(59,130,246,0.12); color: #3b82f6; }
    .kpi-card__icon.green { background: rgba(34,197,94,0.12); color: #22c55e; }
    .kpi-card__icon.purple { background: rgba(139,92,246,0.12); color: #8b5cf6; }
    .kpi-card__icon.orange { background: rgba(245,158,11,0.12); color: #f59e0b; }

    .kpi-card__badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 10px; border-radius: 20px;
        font-size: 12px; font-weight: 600;
    }
    .kpi-card__badge.up { background: rgba(34,197,94,0.12); color: #22c55e; }
    .kpi-card__badge.neutral { background: var(--grid); color: var(--fg-3); }

    .kpi-card__value {
        font-size: 36px; font-weight: 800;
        color: var(--fg-1); line-height: 1; margin-bottom: 6px;
    }
    .kpi-card__value .unit { font-size: 20px; color: var(--fg-3); font-weight: 600; }
    .kpi-card__label { font-size: 14px; color: var(--fg-3); font-weight: 500; }
    .kpi-card__meta {
        margin-top: 14px; padding-top: 14px;
        border-top: 1px solid var(--br);
        font-size: 13px; color: var(--fg-3);
        display: flex; align-items: center; gap: 6px;
    }
    .kpi-card__meta strong { color: var(--fg-1); }

    /* ===== Cards ===== */
    .a-card {
        background: var(--panel);
        border: 1px solid var(--br);
        border-radius: 16px;
        overflow: hidden;
        transition: box-shadow 0.3s;
    }
    .a-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .a-card__header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid var(--br);
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
        padding: 14px 20px; vertical-align: middle;
        border-bottom: 1px solid var(--br); color: var(--fg-1); font-size: 14px;
    }
    .a-card .table tbody tr:last-child td { border-bottom: none; }
    .a-card .table tbody tr { transition: background 0.15s; }
    .a-card .table tbody tr:hover { background: var(--grid); }

    /* ===== Funnel ===== */
    .funnel-item {
        padding: 18px 24px; border-bottom: 1px solid var(--br);
        transition: background 0.15s;
    }
    .funnel-item:last-child { border-bottom: none; }
    .funnel-item:hover { background: var(--grid); }
    .funnel-item__row {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;
    }
    .funnel-item__label { font-size: 14px; font-weight: 600; color: var(--fg-1); }
    .funnel-item__value { font-size: 20px; font-weight: 800; color: var(--fg-1); }
    .funnel-item__pct { font-size: 12px; color: var(--fg-3); margin-left: 8px; font-weight: 500; }
    .funnel-bar { height: 8px; background: var(--grid); border-radius: 4px; overflow: hidden; }
    .funnel-bar__fill { height: 100%; border-radius: 4px; transition: width 0.8s cubic-bezier(0.25,0.46,0.45,0.94); }

    /* ===== Rec items ===== */
    .rec-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 24px; border-bottom: 1px solid var(--br); transition: background 0.15s;
    }
    .rec-item:last-child { border-bottom: none; }
    .rec-item:hover { background: var(--grid); }
    .rec-item__text { font-size: 14px; font-weight: 500; color: var(--fg-1); }
    .rec-item__count {
        min-width: 36px; text-align: center;
        padding: 4px 14px; border-radius: 10px;
        font-weight: 700; font-size: 13px;
        background: var(--accent); color: #fff;
    }

    /* ===== Score chip ===== */
    .score-chip {
        display: inline-block; padding: 5px 16px; border-radius: 10px;
        font-weight: 700; font-size: 13px;
    }
    .score-chip.high { background: rgba(34,197,94,0.12); color: #16a34a; }
    .score-chip.mid  { background: rgba(245,158,11,0.12); color: #d97706; }
    .score-chip.low  { background: rgba(239,68,68,0.12); color: #dc2626; }

    /* ===== Empty State ===== */
    .empty-state {
        text-align: center; padding: 48px 20px; color: var(--fg-3);
    }
    .empty-state i { font-size: 36px; opacity: 0.25; margin-bottom: 8px; display: block; }

    /* ===== Grid Layout ===== */
    .analytics-grid { display: grid; gap: 24px; margin-bottom: 24px; }
    .analytics-grid--2-1 { grid-template-columns: 2fr 1fr; }
    .analytics-grid--1-1 { grid-template-columns: 1fr 1fr; }
    .analytics-grid--5-7 { grid-template-columns: 5fr 7fr; }
    @media (max-width: 900px) {
        .analytics-grid--2-1, .analytics-grid--1-1, .analytics-grid--5-7 { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="analytics-header animate-in">
    <h1>
        <span class="header-icon"><i class="fa-solid fa-chart-column"></i></span>
        Аналитика кандидатов
    </h1>
    <div class="period-toggle">
        <a href="{{ route('admin.analytics.candidates', ['period' => 7]) }}" class="{{ $period == 7 ? 'active' : '' }}">7 дней</a>
        <a href="{{ route('admin.analytics.candidates', ['period' => 30]) }}" class="{{ $period == 30 ? 'active' : '' }}">30 дней</a>
        <a href="{{ route('admin.analytics.candidates', ['period' => 90]) }}" class="{{ $period == 90 ? 'active' : '' }}">90 дней</a>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #3b82f6, #60a5fa);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon blue"><i class="fa-solid fa-file-lines"></i></div>
            <span class="kpi-card__badge neutral"><i class="fa-solid fa-calendar-day"></i> {{ $period }}д</span>
        </div>
        <div class="kpi-card__value">{{ number_format($kpi['total_applications']) }}</div>
        <div class="kpi-card__label">Всего заявок</div>
        <div class="kpi-card__meta"><i class="fa-solid fa-clock" style="color: #3b82f6;"></i> За период: <strong>{{ $kpi['period_applications'] }}</strong></div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #8b5cf6, #a78bfa);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon purple"><i class="fa-solid fa-robot"></i></div>
            <span class="kpi-card__badge up"><i class="fa-solid fa-check"></i> {{ $kpi['analysis_coverage'] }}%</span>
        </div>
        <div class="kpi-card__value">{{ number_format($kpi['analyzed_applications']) }}</div>
        <div class="kpi-card__label">AI проанализировано</div>
        <div class="kpi-card__meta"><i class="fa-solid fa-layer-group" style="color: #8b5cf6;"></i> Покрытие AI-анализом</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #22c55e, #4ade80);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon green"><i class="fa-solid fa-bullseye"></i></div>
        </div>
        @php $sc = $kpi['avg_match_score']; @endphp
        <div class="kpi-card__value" style="color: {{ $sc >= 60 ? '#16a34a' : ($sc >= 40 ? '#d97706' : '#dc2626') }};">{{ $sc }}<span class="unit">%</span></div>
        <div class="kpi-card__label">Средний Match Score</div>
    </div>
    <div class="kpi-card animate-in">
        <div class="kpi-card__accent" style="background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
        <div class="kpi-card__header">
            <div class="kpi-card__icon orange"><i class="fa-solid fa-file-circle-check"></i></div>
            <span class="kpi-card__badge {{ $kpi['parse_success_rate'] >= 80 ? 'up' : 'neutral' }}">{{ $kpi['parse_success_rate'] }}%</span>
        </div>
        <div class="kpi-card__value">{{ $kpi['parsed_documents'] }}<span class="unit">/{{ $kpi['total_documents'] }}</span></div>
        <div class="kpi-card__label">Документов обработано</div>
        @if($kpi['failed_documents'] > 0)
        <div class="kpi-card__meta"><i class="fa-solid fa-circle-exclamation" style="color: #ef4444;"></i> <strong style="color: #ef4444;">{{ $kpi['failed_documents'] }}</strong> с ошибками</div>
        @endif
    </div>
</div>

<div class="analytics-grid analytics-grid--2-1 animate-in">
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-chart-area"></i> Динамика заявок</div>
        </div>
        <div class="a-card__body"><canvas id="trendChart" height="280"></canvas></div>
    </div>
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-chart-pie"></i> Статусы</div>
        </div>
        <div class="a-card__body"><canvas id="statusChart" height="280"></canvas></div>
    </div>
</div>

<div class="analytics-grid analytics-grid--1-1 animate-in">
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-signal"></i> Match Score</div>
        </div>
        <div class="a-card__body"><canvas id="scoreChart" height="240"></canvas></div>
    </div>
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-filter"></i> Воронка конверсии</div>
        </div>
        <div class="a-card__body--flush">
            @php $funnelColors = ['#3b82f6','#8b5cf6','#f59e0b','#06b6d4','#22c55e']; @endphp
            @foreach($funnel as $i => $stage)
            @php $maxCount = $funnel[0]['count'] ?: 1; $pct = round(($stage['count'] / $maxCount) * 100); @endphp
            <div class="funnel-item">
                <div class="funnel-item__row">
                    <span class="funnel-item__label">{{ $stage['stage'] }}</span>
                    <span><span class="funnel-item__value">{{ number_format($stage['count']) }}</span><span class="funnel-item__pct">({{ $pct }}%)</span></span>
                </div>
                <div class="funnel-bar">
                    <div class="funnel-bar__fill" style="width: {{ $pct }}%; background: linear-gradient(90deg, {{ $funnelColors[$i] ?? '#6b7280' }}, {{ $funnelColors[$i] ?? '#6b7280' }}88);"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="analytics-grid analytics-grid--5-7 animate-in">
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-star"></i> AI рекомендации</div>
        </div>
        <div class="a-card__body--flush">
            @forelse($recommendationDistribution as $rec)
            <div class="rec-item">
                <span class="rec-item__text">{{ $rec->recommendation ?: 'Без рекомендации' }}</span>
                <span class="rec-item__count">{{ $rec->count }}</span>
            </div>
            @empty
            <div class="empty-state"><i class="fa-solid fa-inbox"></i>Нет данных</div>
            @endforelse
        </div>
    </div>
    <div class="a-card">
        <div class="a-card__header">
            <div class="a-card__title"><i class="fa-solid fa-microchip"></i> AI операции <span style="font-weight: 400; font-size: 13px; color: var(--fg-3); margin-left: 4px;">(7 дней)</span></div>
        </div>
        <div class="a-card__body--flush">
            <table class="table">
                <thead><tr><th>Операция</th><th style="text-align:center">Всего</th><th style="text-align:center">OK</th><th style="text-align:center">Ошибки</th><th style="text-align:right">Время</th></tr></thead>
                <tbody>
                @forelse($aiOpsStats as $op)
                <tr>
                    <td><strong>{{ $op->operation }}</strong></td>
                    <td style="text-align:center">{{ $op->total }}</td>
                    <td style="text-align:center; color: #16a34a; font-weight: 700;">{{ $op->success }}</td>
                    <td style="text-align:center; {{ $op->errors > 0 ? 'color:#dc2626;font-weight:700;' : 'color:var(--fg-3);' }}">{{ $op->errors }}</td>
                    <td style="text-align:right; color: var(--fg-3); font-size: 13px;">{{ round($op->avg_duration) }}<span style="opacity:0.6;">ms</span></td>
                </tr>
                @empty
                <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-database"></i>Нет операций</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="a-card animate-in" style="margin-bottom: 24px;">
    <div class="a-card__header">
        <div class="a-card__title"><i class="fa-solid fa-ranking-star"></i> Топ вакансий</div>
    </div>
    <div class="a-card__body--flush">
        <table class="table">
            <thead><tr><th>Вакансия</th><th style="text-align:center">Заявки</th><th style="text-align:center">Проанализ.</th><th style="text-align:center">Avg Score</th></tr></thead>
            <tbody>
            @forelse($topVacancies as $vac)
            <tr>
                <td><strong>{{ $vac['title'] }}</strong></td>
                <td style="text-align:center; font-weight: 600;">{{ $vac['applications'] }}</td>
                <td style="text-align:center">{{ $vac['analyzed'] }}</td>
                <td style="text-align:center">
                    <span class="score-chip {{ $vac['avg_score'] >= 60 ? 'high' : ($vac['avg_score'] >= 40 ? 'mid' : 'low') }}">{{ $vac['avg_score'] }}%</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="4"><div class="empty-state"><i class="fa-solid fa-briefcase"></i>Нет данных</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.font.family = '-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif';
Chart.defaults.font.size = 12;
Chart.defaults.color = getComputedStyle(document.documentElement).getPropertyValue('--fg-3').trim() || '#888';

const trendCtx = document.getElementById('trendChart').getContext('2d');
const grad1 = trendCtx.createLinearGradient(0, 0, 0, 280);
grad1.addColorStop(0, 'rgba(59,130,246,0.25)'); grad1.addColorStop(1, 'rgba(59,130,246,0.02)');
const grad2 = trendCtx.createLinearGradient(0, 0, 0, 280);
grad2.addColorStop(0, 'rgba(34,197,94,0.25)'); grad2.addColorStop(1, 'rgba(34,197,94,0.02)');

new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: @json(array_column($applicationsTrend, 'date')),
        datasets: [
            { label: 'Заявки', data: @json(array_column($applicationsTrend, 'count')), borderColor: '#3b82f6', backgroundColor: grad1, fill: true, tension: 0.4, pointRadius: 3, pointHoverRadius: 6, pointBackgroundColor: '#3b82f6', borderWidth: 2.5 },
            { label: 'Проанализированы', data: @json(array_column($applicationsTrend, 'analyzed')), borderColor: '#22c55e', backgroundColor: grad2, fill: true, tension: 0.4, pointRadius: 3, pointHoverRadius: 6, pointBackgroundColor: '#22c55e', borderWidth: 2.5 }
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' },
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } },
            tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10, titleFont: { weight: '700' } } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false } }, x: { grid: { display: false } } }
    }
});

new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_column($statusDistribution, 'status')),
        datasets: [{ data: @json(array_column($statusDistribution, 'count')), backgroundColor: @json(array_column($statusDistribution, 'color')), borderWidth: 0, hoverOffset: 10, spacing: 2 }]
    },
    options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } },
            tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10 } }
    }
});

const scoreBuckets = @json($scoreDistribution);
new Chart(document.getElementById('scoreChart'), {
    type: 'bar',
    data: {
        labels: scoreBuckets.map(b => b.bucket),
        datasets: [{
            label: 'Кандидатов', data: scoreBuckets.map(b => b.count),
            backgroundColor: scoreBuckets.map(b => {
                const v = parseInt(b.bucket);
                return v >= 80 ? 'rgba(34,197,94,0.75)' : v >= 60 ? 'rgba(59,130,246,0.75)' : v >= 40 ? 'rgba(245,158,11,0.75)' : 'rgba(239,68,68,0.75)';
            }),
            borderRadius: 8, borderSkipped: false, maxBarThickness: 48,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', padding: 12, cornerRadius: 10 } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false } }, x: { grid: { display: false } } }
    }
});
</script>
@endpush
