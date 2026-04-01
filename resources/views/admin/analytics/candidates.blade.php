@extends('layouts.admin')

@section('title', 'Аналитика кандидатов')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-graph-up text-primary me-2"></i>
        Аналитика кандидатов
    </h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.analytics.candidates', ['period' => 7]) }}" class="btn btn-sm {{ $period == 7 ? 'btn-primary' : 'btn-outline-primary' }}">7 дней</a>
        <a href="{{ route('admin.analytics.candidates', ['period' => 30]) }}" class="btn btn-sm {{ $period == 30 ? 'btn-primary' : 'btn-outline-primary' }}">30 дней</a>
        <a href="{{ route('admin.analytics.candidates', ['period' => 90]) }}" class="btn btn-sm {{ $period == 90 ? 'btn-primary' : 'btn-outline-primary' }}">90 дней</a>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Всего заявок</div>
                <div class="fs-3 fw-bold">{{ $kpi['total_applications'] }}</div>
                <div class="small text-muted">За период: {{ $kpi['period_applications'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">AI проанализировано</div>
                <div class="fs-3 fw-bold text-primary">{{ $kpi['analyzed_applications'] }}</div>
                <div class="small">
                    <span class="badge bg-primary">{{ $kpi['analysis_coverage'] }}%</span> покрытие
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Средний Match Score</div>
                <div class="fs-3 fw-bold {{ $kpi['avg_match_score'] >= 60 ? 'text-success' : ($kpi['avg_match_score'] >= 40 ? 'text-warning' : 'text-danger') }}">
                    {{ $kpi['avg_match_score'] }}%
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Документы</div>
                <div class="fs-3 fw-bold">{{ $kpi['parsed_documents'] }}<span class="fs-6 text-muted">/{{ $kpi['total_documents'] }}</span></div>
                <div class="small">
                    <span class="badge bg-success">{{ $kpi['parse_success_rate'] }}%</span> обработано
                    @if($kpi['failed_documents'] > 0)
                        <span class="badge bg-danger ms-1">{{ $kpi['failed_documents'] }} ошибок</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Applications Trend -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Заявки и анализ по дням</h6>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Status Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Распределение по статусам</h6>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Match Score Distribution -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Распределение Match Score</h6>
            </div>
            <div class="card-body">
                <canvas id="scoreChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Conversion Funnel -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Воронка конверсии</h6>
            </div>
            <div class="card-body p-0">
                @foreach($funnel as $i => $stage)
                @php
                    $maxCount = $funnel[0]['count'] ?: 1;
                    $pct = round(($stage['count'] / $maxCount) * 100);
                @endphp
                <div class="p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-semibold small">{{ $stage['stage'] }}</span>
                        <span class="fw-bold">{{ $stage['count'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: {{ $pct }}%; background: {{ ['#3b82f6','#8b5cf6','#f59e0b','#06b6d4','#22c55e'][$i] ?? '#6b7280' }};"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- AI Recommendation Distribution -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">AI Рекомендации</h6>
            </div>
            <div class="card-body p-0">
                @forelse($recommendationDistribution as $rec)
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="small">{{ $rec->recommendation ?: 'Без рекомендации' }}</span>
                    <span class="badge bg-secondary">{{ $rec->count }}</span>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">Нет данных</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- AI Operations Stats -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">AI операции (7 дней)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Операция</th>
                                <th class="text-center">Всего</th>
                                <th class="text-center">Успешно</th>
                                <th class="text-center">Ошибки</th>
                                <th class="text-end">Avg время</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($aiOpsStats as $op)
                            <tr>
                                <td class="fw-semibold small">{{ $op->operation }}</td>
                                <td class="text-center">{{ $op->total }}</td>
                                <td class="text-center text-success">{{ $op->success }}</td>
                                <td class="text-center {{ $op->errors > 0 ? 'text-danger fw-bold' : '' }}">{{ $op->errors }}</td>
                                <td class="text-end small text-muted">{{ round($op->avg_duration) }}ms</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Нет данных</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Vacancies -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0">Топ вакансий по заявкам</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Вакансия</th>
                        <th class="text-center">Заявки</th>
                        <th class="text-center">Проанализированы</th>
                        <th class="text-center">Avg Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topVacancies as $vac)
                    <tr>
                        <td class="fw-semibold">{{ $vac['title'] }}</td>
                        <td class="text-center">{{ $vac['applications'] }}</td>
                        <td class="text-center">{{ $vac['analyzed'] }}</td>
                        <td class="text-center">
                            <span class="badge {{ $vac['avg_score'] >= 60 ? 'bg-success' : ($vac['avg_score'] >= 40 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                {{ $vac['avg_score'] }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Applications Trend
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: @json(array_column($applicationsTrend, 'date')),
        datasets: [
            {
                label: 'Заявки',
                data: @json(array_column($applicationsTrend, 'count')),
                backgroundColor: 'rgba(59,130,246,0.5)',
                borderColor: '#3b82f6',
                borderWidth: 1,
            },
            {
                label: 'Проанализированы',
                data: @json(array_column($applicationsTrend, 'analyzed')),
                backgroundColor: 'rgba(34,197,94,0.5)',
                borderColor: '#22c55e',
                borderWidth: 1,
            }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});

// Status Distribution
new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_column($statusDistribution, 'status')),
        datasets: [{
            data: @json(array_column($statusDistribution, 'count')),
            backgroundColor: @json(array_column($statusDistribution, 'color')),
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
});

// Match Score Distribution
const scoreBuckets = @json($scoreDistribution);
new Chart(document.getElementById('scoreChart'), {
    type: 'bar',
    data: {
        labels: scoreBuckets.map(b => b.bucket),
        datasets: [{
            label: 'Кандидаты',
            data: scoreBuckets.map(b => b.count),
            backgroundColor: scoreBuckets.map(b => {
                const val = parseInt(b.bucket);
                if (val >= 80) return '#22c55e';
                if (val >= 60) return '#3b82f6';
                if (val >= 40) return '#f59e0b';
                return '#ef4444';
            }),
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>
@endsection
