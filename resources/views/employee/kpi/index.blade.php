@extends('employee.layouts.app')

@section('title', 'Мои KPI')
@section('page-title', 'Мои KPI')

@section('content')
<style>
    .kpi-stats { display:flex; gap:16px; margin-bottom:24px; flex-wrap:wrap; }
    .kpi-stat { flex:1; min-width:160px; padding:20px; background:var(--panel); border:1px solid var(--br); border-radius:14px; text-align:center; }
    .kpi-stat .value { font-size:28px; font-weight:800; }
    .kpi-stat .label { font-size:12px; color:var(--fg-3); margin-top:4px; }
    .kpi-stat .trend { font-size:12px; margin-top:4px; }
    .kpi-stat .trend.up { color:#22c55e; }
    .kpi-stat .trend.down { color:#ef4444; }

    .period-tabs { display:flex; gap:4px; background:var(--panel); border:1px solid var(--br); border-radius:12px; padding:4px; margin-bottom:24px; }
    .period-tabs a { padding:10px 20px; border-radius:10px; text-decoration:none; font-size:13px; font-weight:600; color:var(--fg-3); transition:all 0.2s; }
    .period-tabs a.active { background:var(--accent); color:#fff; }
    .period-tabs a:hover:not(.active) { color:var(--fg-1); background:rgba(0,0,0,0.03); }

    .chart-card { background:var(--panel); border:1px solid var(--br); border-radius:14px; padding:24px; margin-bottom:24px; }
    .chart-card h6 { font-weight:700; margin-bottom:16px; color:var(--fg-1); }

    .kpi-table { width:100%; border-collapse:separate; border-spacing:0 8px; }
    .kpi-table th { padding:8px 16px; font-size:12px; color:var(--fg-3); font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
    .kpi-table td { padding:14px 16px; background:var(--panel); border-top:1px solid var(--br); border-bottom:1px solid var(--br); }
    .kpi-table tr td:first-child { border-left:1px solid var(--br); border-radius:12px 0 0 12px; }
    .kpi-table tr td:last-child { border-right:1px solid var(--br); border-radius:0 12px 12px 0; }
    .kpi-table tr:hover td { border-color:var(--accent); }

    .score-bar { display:flex; align-items:center; gap:10px; }
    .score-bar .bar { flex:1; max-width:120px; height:8px; background:var(--br); border-radius:4px; overflow:hidden; }
    .score-bar .bar .fill { height:100%; border-radius:4px; transition:width 0.5s; }
    .score-bar .val { font-weight:700; font-size:14px; min-width:45px; }

    .bonus-badge { padding:4px 10px; border-radius:8px; font-size:11px; font-weight:600; }
    .bonus-badge.yes { background:rgba(34,197,94,0.12); color:#22c55e; }
    .bonus-badge.no { background:rgba(107,114,128,0.08); color:var(--fg-3); }
    .bonus-badge.paid { background:rgba(59,130,246,0.12); color:#3B82F6; }

    .status-badge { padding:4px 10px; border-radius:8px; font-size:11px; font-weight:600; }

    .action-btns { display:flex; gap:6px; }
    .action-btns a, .action-btns button { width:34px; height:34px; border-radius:8px; border:1px solid var(--br); background:transparent; color:var(--fg-3); display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s; text-decoration:none; }
    .action-btns a:hover, .action-btns button:hover { border-color:var(--accent); color:var(--accent); }

    .empty-state { text-align:center; padding:60px 20px; }
    .empty-state i { font-size:48px; opacity:0.15; color:var(--fg-3); display:block; margin-bottom:12px; }
</style>

{{-- Period Tabs --}}
<div class="period-tabs">
    @foreach($availablePeriods as $period)
        <a href="{{ route('employee.kpi.index', ['period' => $period->value]) }}"
           class="{{ $currentPeriod === $period->value ? 'active' : '' }}">
            {{ $period->label() }}
        </a>
    @endforeach
</div>

{{-- Stats --}}
@if($snapshots->count() > 0)
    @php
        $latest = $snapshots->first();
        $previous = $snapshots->skip(1)->first();
        $avgScore = $snapshots->avg('total_score');
        $trend = $previous ? $latest->total_score - $previous->total_score : 0;
    @endphp
    <div class="kpi-stats">
        <div class="kpi-stat">
            <div class="value" style="color:{{ $latest->total_score >= 70 ? '#22c55e' : ($latest->total_score >= 50 ? '#f59e0b' : '#ef4444') }}">
                {{ number_format($latest->total_score, 1) }}%
            </div>
            <div class="label">Текущий KPI</div>
            @if($trend != 0)
                <div class="trend {{ $trend > 0 ? 'up' : 'down' }}">
                    <i class="fa-solid fa-arrow-{{ $trend > 0 ? 'up' : 'down' }}"></i>
                    {{ abs(round($trend, 1)) }}% к прошлому
                </div>
            @endif
        </div>
        <div class="kpi-stat">
            <div class="value" style="color:var(--fg-1);">{{ number_format($avgScore, 1) }}%</div>
            <div class="label">Средний KPI</div>
        </div>
        <div class="kpi-stat">
            <div class="value" style="color:var(--fg-1);">{{ $snapshots->count() }}</div>
            <div class="label">Периодов</div>
        </div>
        <div class="kpi-stat">
            <div class="value" style="color:#22c55e;">{{ $snapshots->filter(fn($s) => $s->isBonusEligible())->count() }}</div>
            <div class="label">С бонусом</div>
        </div>
    </div>
@endif

{{-- Chart --}}
<div class="chart-card">
    <h6><i class="fa-solid fa-chart-line me-2" style="color:var(--accent);"></i>Динамика KPI</h6>
    <canvas id="kpiChart" height="80"></canvas>
</div>

{{-- Table --}}
<div style="background:var(--panel); border:1px solid var(--br); border-radius:14px; padding:20px;">
    <h6 style="font-weight:700; margin-bottom:16px; color:var(--fg-1);">
        <i class="fa-solid fa-history me-2" style="color:var(--accent);"></i>История KPI
    </h6>

    <table class="kpi-table">
        <thead>
            <tr>
                <th>Период</th>
                <th>Балл</th>
                <th>Статус</th>
                <th>Бонус</th>
                <th style="text-align:right;">Действия</th>
            </tr>
        </thead>
        <tbody>
            @forelse($snapshots as $snapshot)
                @php
                    $scoreColor = $snapshot->total_score >= 90 ? '#22c55e' : ($snapshot->total_score >= 70 ? '#3B82F6' : ($snapshot->total_score >= 50 ? '#f59e0b' : '#ef4444'));
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:600; color:var(--fg-1);">{{ $snapshot->period_label }}</div>
                        <div style="font-size:12px; color:var(--fg-3);">{{ $snapshot->period_start->format('d.m.Y') }} — {{ $snapshot->period_end->format('d.m.Y') }}</div>
                    </td>
                    <td>
                        <div class="score-bar">
                            <div class="bar">
                                <div class="fill" style="width:{{ min(100, $snapshot->total_score) }}%; background:{{ $scoreColor }};"></div>
                            </div>
                            <span class="val" style="color:{{ $scoreColor }};">{{ number_format($snapshot->total_score, 1) }}%</span>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge" style="background:rgba({{ $snapshot->status->value === 'approved' ? '34,197,94' : '107,114,128' }},0.12); color:{{ $snapshot->status->value === 'approved' ? '#22c55e' : 'var(--fg-3)' }};">
                            {{ $snapshot->status->label() }}
                        </span>
                    </td>
                    <td>
                        @if($snapshot->isBonusEligible())
                            @if($snapshot->isBonusPaid())
                                <span class="bonus-badge paid"><i class="fa-solid fa-check me-1"></i>{{ number_format($snapshot->getBonusAmount(), 0, ',', ' ') }} сум</span>
                            @else
                                <span class="bonus-badge yes">{{ number_format($snapshot->getBonusAmount(), 0, ',', ' ') }} сум</span>
                            @endif
                        @else
                            <span class="bonus-badge no">Не положен</span>
                        @endif
                    </td>
                    <td>
                        <div class="action-btns" style="justify-content:flex-end;">
                            <a href="{{ route('employee.kpi.show', $snapshot) }}" title="Подробнее">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <button type="button" onclick="explainKpi({{ $snapshot->id }})" title="AI объяснение">
                                <i class="fa-solid fa-robot"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="fa-solid fa-chart-bar"></i>
                            <p style="color:var(--fg-3);">Нет данных о KPI за выбранный период</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Explain Modal --}}
<div class="modal fade" id="explainModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:16px; border:none; box-shadow:0 8px 40px rgba(0,0,0,0.15);">
            <div class="modal-header" style="border-bottom:1px solid var(--br); padding:20px 24px;">
                <h5 class="modal-title" style="font-weight:700;">
                    <i class="fa-solid fa-robot me-2" style="color:var(--accent);"></i>AI объяснение KPI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="explainContent" style="padding:24px; min-height:120px;">
                <div class="text-center py-5">
                    <div class="spinner-border" style="color:var(--accent);" role="status"></div>
                    <p class="mt-3" style="color:var(--fg-3);">Анализирую ваши показатели...</p>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid var(--br);">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const trendData = @json($trend);

    if (trendData.length > 0) {
        const ctx = document.getElementById('kpiChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trendData.map(t => t.period),
                datasets: [{
                    label: 'KPI',
                    data: trendData.map(t => t.score),
                    backgroundColor: trendData.map(t => {
                        if (t.score >= 90) return 'rgba(34, 197, 94, 0.8)';
                        if (t.score >= 70) return 'rgba(59, 130, 246, 0.8)';
                        if (t.score >= 50) return 'rgba(245, 158, 11, 0.8)';
                        return 'rgba(239, 68, 68, 0.8)';
                    }),
                    borderRadius: 8,
                    barPercentage: 0.6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' }, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    function escapeHtml(str) { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }

    // Fix modal scroll lock
    document.getElementById('explainModal').addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    });

    let _explainModal = null;
    function getModal() {
        if (!_explainModal) _explainModal = new bootstrap.Modal(document.getElementById('explainModal'));
        return _explainModal;
    }

async function explainKpi(snapshotId) {
    const modal = getModal();
    const content = document.getElementById('explainContent');

    content.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border" style="color:var(--accent);"></div>
            <p class="mt-3" style="color:var(--fg-3);">Анализирую...</p>
        </div>
    `;

    modal.show();

    try {
        const res = await fetch(`/kpi/${snapshotId}/explain`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (!res.ok) {
            const errorText = await res.text();
            console.error('KPI explain request failed:', res.status, errorText);
            throw new Error(`HTTP ${res.status}`);
        }

        const data = await res.json();

        if (!data.success) {
            console.error('KPI explain response error:', data);
            throw new Error(data.error || 'Ошибка ответа сервера');
        }

        content.innerHTML = `
            <div style="margin-bottom:20px;">
                <h6 style="color:var(--accent);font-weight:700;">
                    <i class="fa-solid fa-comment-dots me-2"></i>Объяснение
                </h6>
                <p style="line-height:1.6;">${escapeHtml(data.explanation || '')}</p>
            </div>

            ${data.metric_explanations && Object.keys(data.metric_explanations).length ? `
                <div style="margin-bottom:20px;">
                    <h6 style="color:var(--accent);font-weight:700;">
                        <i class="fa-solid fa-list-check me-2"></i>По показателям
                    </h6>
                    ${Object.entries(data.metric_explanations).map(([k, v]) => `
                        <div style="padding:10px 14px;background:rgba(0,0,0,0.02);border-radius:10px;margin-bottom:8px;">
                            <strong>${escapeHtml(k)}:</strong> ${escapeHtml(v)}
                        </div>
                    `).join('')}
                </div>
            ` : ''}

            ${data.improvement_suggestions?.length ? `
                <div>
                    <h6 style="color:var(--accent);font-weight:700;">
                        <i class="fa-solid fa-lightbulb me-2"></i>Рекомендации
                    </h6>
                    <ul style="margin:0;padding-left:20px;">
                        ${data.improvement_suggestions.map(s => `
                            <li style="margin-bottom:6px;">${escapeHtml(s)}</li>
                        `).join('')}
                    </ul>
                </div>
            ` : ''}
        `;
    } catch (e) {
        console.error('explainKpi error:', e);
        content.innerHTML = `
            <div class="alert alert-danger" style="border-radius:12px;">
                <i class="fa-solid fa-exclamation-triangle me-2"></i>
                Ошибка загрузки
            </div>
        `;
    }
}
</script>
@endpush
