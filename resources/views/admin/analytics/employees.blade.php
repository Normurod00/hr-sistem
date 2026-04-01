@extends('layouts.admin')

@section('title', 'Аналитика сотрудников')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-people-fill text-success me-2"></i>
        Аналитика сотрудников
    </h4>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Всего сотрудников</div>
                <div class="fs-3 fw-bold">{{ $kpi['total_employees'] }}</div>
                <div class="small text-muted">Активных: {{ $kpi['active_employees'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">AI диалоги</div>
                <div class="fs-3 fw-bold text-primary">{{ $kpi['total_conversations'] }}</div>
                <div class="small text-muted">Активных: {{ $kpi['active_conversations'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">AI рекомендации</div>
                <div class="fs-3 fw-bold text-warning">{{ $kpi['total_recommendations'] }}</div>
                <div class="small">
                    <span class="badge bg-warning text-dark">{{ $kpi['pending_recommendations'] }}</span> ожидают
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Выполнение рекомендаций</div>
                <div class="fs-3 fw-bold text-success">{{ $kpi['recommendation_completion_rate'] }}%</div>
                <div class="small text-muted">{{ $kpi['completed_recommendations'] }} из {{ $kpi['total_recommendations'] }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Document Analysis KPI Row -->
<div class="row g-3 mb-4">
    <div class="col-12"><h5 class="text-muted"><i class="bi bi-file-earmark-medical me-2"></i>AI анализ документов</h5></div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Документов загружено</div>
                <div class="fs-3 fw-bold">{{ $kpi['total_documents'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">AI обработано</div>
                <div class="fs-3 fw-bold text-success">{{ $kpi['parsed_documents'] }}</div>
                <div class="small"><span class="badge bg-success">{{ $kpi['doc_parse_rate'] }}%</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Ошибки обработки</div>
                <div class="fs-3 fw-bold {{ $kpi['failed_documents'] > 0 ? 'text-danger' : 'text-muted' }}">{{ $kpi['failed_documents'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Типы документов</div>
                <div class="d-flex flex-wrap gap-1 mt-1">
                    @foreach($docTypeDistribution as $dt)
                    <span class="badge bg-light text-dark border">{{ $dt->document_type }}: {{ $dt->count }}</span>
                    @endforeach
                    @if($docTypeDistribution->isEmpty())
                    <span class="text-muted small">Нет данных</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- AI Usage Trend -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Использование AI (30 дней)</h6>
            </div>
            <div class="card-body">
                <canvas id="usageTrend" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Department Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">По департаментам</h6>
            </div>
            <div class="card-body">
                <canvas id="departmentChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Context Types -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Типы AI диалогов</h6>
            </div>
            <div class="card-body p-0">
                @forelse($contextTypes as $ct)
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="small fw-semibold">{{ $ct->context_type }}</span>
                    <span class="badge bg-primary">{{ $ct->count }}</span>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">Нет данных</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recommendation Status -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0">Рекомендации по типам и статусам</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Тип</th>
                                <th>Статус</th>
                                <th class="text-center">Количество</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recommendationTypes as $rt)
                            <tr>
                                <td class="small fw-semibold">{{ $rt->type }}</td>
                                <td>
                                    <span class="badge {{ $rt->status === 'completed' ? 'bg-success' : ($rt->status === 'pending' ? 'bg-warning text-dark' : 'bg-info') }}">
                                        {{ $rt->status }}
                                    </span>
                                </td>
                                <td class="text-center">{{ $rt->count }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="3" class="text-center text-muted py-3">Нет данных</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// AI Usage Trend
new Chart(document.getElementById('usageTrend'), {
    type: 'line',
    data: {
        labels: @json(array_column($aiUsageTrend, 'date')),
        datasets: [
            {
                label: 'Диалоги',
                data: @json(array_column($aiUsageTrend, 'conversations')),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59,130,246,0.1)',
                fill: true,
                tension: 0.3,
            },
            {
                label: 'Сообщения',
                data: @json(array_column($aiUsageTrend, 'messages')),
                borderColor: '#22c55e',
                backgroundColor: 'rgba(34,197,94,0.1)',
                fill: true,
                tension: 0.3,
            }
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true } } }
});

// Department Distribution
const depts = @json($departmentDistribution);
const colors = ['#3b82f6','#22c55e','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#f97316','#14b8a6','#6366f1'];
new Chart(document.getElementById('departmentChart'), {
    type: 'doughnut',
    data: {
        labels: depts.map(d => d.department || 'Не указан'),
        datasets: [{
            data: depts.map(d => d.count),
            backgroundColor: colors.slice(0, depts.length),
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { boxWidth: 12 } } } }
});
</script>
@endsection
