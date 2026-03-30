@extends('employee.layouts.app')

@section('title', 'Дашборд')
@section('page-title', 'Добро пожаловать, ' . auth()->user()->name)

@section('content')
@if(!$employee)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>Профиль не найден</strong>
        <p class="mb-0 mt-2">Ваш профиль сотрудника ещё не создан. Пожалуйста, обратитесь в отдел HR.</p>
    </div>
@else
<div class="row g-4 mb-4">
    <!-- KPI Score Card -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-card-icon primary">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <div>
                    <div class="stat-label">Текущий KPI</div>
                    <div class="stat-value">{{ number_format($dashboard['current']['total_score'] ?? 0, 1) }}%</div>
                </div>
            </div>
            @if(isset($dashboard['risk']['delta']))
                <div class="stat-trend {{ $dashboard['risk']['delta'] >= 0 ? 'up' : 'down' }}">
                    <i class="bi bi-arrow-{{ $dashboard['risk']['delta'] >= 0 ? 'up' : 'down' }}"></i>
                    {{ abs($dashboard['risk']['delta']) }}% vs прошлый месяц
                </div>
            @endif
        </div>
    </div>

    <!-- Quarter KPI -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-card-icon info">
                    <i class="bi bi-calendar3"></i>
                </div>
                <div>
                    <div class="stat-label">Квартальный KPI</div>
                    <div class="stat-value">{{ number_format($dashboard['quarter']['total_score'] ?? 0, 1) }}%</div>
                </div>
            </div>
            <div class="text-muted small">
                {{ $dashboard['quarter']['period_label'] ?? 'Текущий квартал' }}
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-card-icon warning">
                    <i class="bi bi-lightbulb"></i>
                </div>
                <div>
                    <div class="stat-label">Рекомендации</div>
                    <div class="stat-value">{{ $activeRecommendations->count() }}</div>
                </div>
            </div>
            <div class="text-muted small">
                Активных к выполнению
            </div>
        </div>
    </div>

    <!-- Risk Score -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="stat-card-icon {{ ($dashboard['risk']['level'] ?? 'none') === 'high' ? 'primary' : 'success' }}">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div>
                    <div class="stat-label">Риск-индикатор</div>
                    <div class="stat-value">
                        @switch($dashboard['risk']['level'] ?? 'none')
                            @case('high')
                                <span class="text-danger">Высокий</span>
                                @break
                            @case('medium')
                                <span class="text-warning">Средний</span>
                                @break
                            @case('low')
                                <span class="text-info">Низкий</span>
                                @break
                            @default
                                <span class="text-success">Норма</span>
                        @endswitch
                    </div>
                </div>
            </div>
            <div class="text-muted small">
                {{ $dashboard['risk']['message'] ?? 'Показатели в норме' }}
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- KPI Trend Chart -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Динамика KPI</h5>
                <a href="{{ route('employee.kpi.index') }}" class="btn btn-sm btn-outline-brb">
                    Подробнее
                </a>
            </div>
            <div class="card-body">
                <canvas id="kpiTrendChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Быстрые действия</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('employee.chat.index') }}" class="btn btn-brb">
                        <i class="bi bi-chat-dots me-2"></i>
                        Задать вопрос AI
                    </a>
                    <a href="{{ route('employee.kpi.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-graph-up me-2"></i>
                        Посмотреть KPI
                    </a>
                    <a href="{{ route('employee.policies.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-file-text me-2"></i>
                        Найти политику
                    </a>
                </div>

                @if(isset($dashboard['current']) && $dashboard['current']['total_score'] < 70)
                    <div class="alert alert-brb mt-4 mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        <strong>Совет:</strong> Ваш KPI ниже 70%. Спросите AI, как его улучшить.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recommendations & Conversations -->
<div class="row g-4 mt-2">
    <!-- Active Recommendations -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Рекомендации</h5>
                @if($activeRecommendations->isNotEmpty() && isset($dashboard['current']['id']))
                    <a href="{{ route('employee.kpi.recommendations', $dashboard['current']['id']) }}" class="btn btn-sm btn-link">
                        Все
                    </a>
                @endif
            </div>
            <div class="card-body p-0">
                @forelse($activeRecommendations as $rec)
                    <div class="d-flex align-items-start gap-3 p-3 border-bottom">
                        <div class="badge bg-{{ $rec->type_color }} rounded-pill">
                            {{ $rec->priority }}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium mb-1">{{ Str::limit($rec->action, 80) }}</div>
                            <div class="text-muted small">
                                <span class="badge bg-light text-dark">{{ $rec->type_label }}</span>
                                @if($rec->expected_impact)
                                    <span class="ms-2">+{{ $rec->expected_impact }}% к KPI</span>
                                @endif
                            </div>
                        </div>
                        <span class="badge bg-{{ $rec->status->color() }}">
                            {{ $rec->status_label }}
                        </span>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
                        Нет активных рекомендаций
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Conversations -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0">Недавние разговоры</h5>
                <a href="{{ route('employee.chat.index') }}" class="btn btn-sm btn-link">
                    Все
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($recentConversations as $conv)
                    <a href="{{ route('employee.chat.show', $conv) }}" class="d-flex align-items-center gap-3 p-3 border-bottom text-decoration-none text-dark hover-bg-light">
                        <div class="rounded-circle bg-light p-2">
                            <i class="bi {{ $conv->context_type->icon() }} fs-5"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-medium text-truncate">{{ $conv->display_title }}</div>
                            <div class="text-muted small">
                                {{ $conv->context_label }} · {{ $conv->last_message_at?->diffForHumans() }}
                            </div>
                        </div>
                        <span class="badge bg-light text-dark">
                            {{ $conv->message_count }} сообщ.
                        </span>
                    </a>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-chat fs-1 d-block mb-2"></i>
                        Начните разговор с AI
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if($teamStats)
    <!-- Team Stats for Managers -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="bi bi-people me-2"></i>Моя команда</h5>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fs-1 fw-bold text-primary">{{ $teamStats['team_size'] }}</div>
                        <div class="text-muted">Сотрудников</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fs-1 fw-bold text-success">{{ number_format($teamStats['average_score'], 1) }}%</div>
                        <div class="text-muted">Средний KPI</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fs-1 fw-bold text-info">{{ count($teamStats['top_performers']) }}</div>
                        <div class="text-muted">Лидеры</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="fs-1 fw-bold text-warning">{{ count($teamStats['needs_attention']) }}</div>
                        <div class="text-muted">Требуют внимания</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const trendData = @json($dashboard ? ($dashboard['trend'] ?? []) : []);

    if (trendData.length > 0) {
        const ctx = document.getElementById('kpiTrendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: trendData.map(t => t.period || t.label),
                datasets: [{
                    label: 'KPI %',
                    data: trendData.map(t => t.score),
                    borderColor: '#D6001C',
                    backgroundColor: 'rgba(214, 0, 28, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    pointBackgroundColor: '#D6001C',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => value + '%'
                        }
                    }
                }
            }
        });
    }
</script>
@endpush
