@extends('employee.layouts.app')

@section('title', 'Мои KPI')
@section('page-title', 'Мои KPI')

@section('content')
<!-- Period Selector -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="btn-group" role="group">
                @foreach($availablePeriods as $period)
                    <a href="{{ route('employee.kpi.index', ['period' => $period->value]) }}"
                       class="btn {{ $currentPeriod === $period->value ? 'btn-brb' : 'btn-outline-secondary' }}">
                        {{ $period->label() }}
                    </a>
                @endforeach
            </div>

            <div class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                Показаны данные за последние {{ $snapshots->count() }} периодов
            </div>
        </div>
    </div>
</div>

<!-- KPI Trend Chart -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0">Динамика KPI</h5>
    </div>
    <div class="card-body">
        <canvas id="kpiChart" height="100"></canvas>
    </div>
</div>

<!-- KPI Snapshots List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="mb-0">История KPI</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Период</th>
                        <th class="text-center">Общий балл</th>
                        <th>Статус</th>
                        <th>Бонус</th>
                        <th class="text-end">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($snapshots as $snapshot)
                        <tr>
                            <td>
                                <strong>{{ $snapshot->period_label }}</strong>
                                <div class="text-muted small">
                                    {{ $snapshot->period_start->format('d.m.Y') }} - {{ $snapshot->period_end->format('d.m.Y') }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <div class="progress" style="width: 100px; height: 8px;">
                                        <div class="progress-bar bg-{{ $snapshot->score_color }}"
                                             style="width: {{ min(100, $snapshot->total_score) }}%"></div>
                                    </div>
                                    <span class="fw-bold text-{{ $snapshot->score_color }}">
                                        {{ number_format($snapshot->total_score, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $snapshot->status->color() }}">
                                    {{ $snapshot->status->label() }}
                                </span>
                            </td>
                            <td>
                                @if($snapshot->isBonusEligible())
                                    <span class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        {{ number_format($snapshot->getBonusAmount(), 0, ',', ' ') }} сум
                                        @if($snapshot->isBonusPaid())
                                            <span class="badge bg-success-subtle text-success ms-1">Выплачен</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-muted">
                                        <i class="bi bi-x-circle me-1"></i>
                                        Не положен
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('employee.kpi.show', $snapshot) }}"
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary"
                                            onclick="explainKpi({{ $snapshot->id }})">
                                        <i class="bi bi-question-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-graph-up fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted mb-0">Нет данных о KPI за выбранный период</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Explain Modal -->
<div class="modal fade" id="explainModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-robot me-2"></i>
                    AI объяснение KPI
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="explainContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-3 text-muted">Анализирую ваши показатели...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Trend Chart
    const trendData = @json($trend);

    if (trendData.length > 0) {
        const ctx = document.getElementById('kpiChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trendData.map(t => t.period),
                datasets: [{
                    label: 'KPI %',
                    data: trendData.map(t => t.score),
                    backgroundColor: trendData.map(t => {
                        if (t.score >= 90) return 'rgba(0, 168, 107, 0.8)';
                        if (t.score >= 70) return 'rgba(0, 102, 255, 0.8)';
                        if (t.score >= 50) return 'rgba(255, 149, 0, 0.8)';
                        return 'rgba(214, 0, 28, 0.8)';
                    }),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
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

    // Explain KPI
    async function explainKpi(snapshotId) {
        const modal = new bootstrap.Modal(document.getElementById('explainModal'));
        const content = document.getElementById('explainContent');

        content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-3 text-muted">Анализирую ваши показатели...</p>
            </div>
        `;
        modal.show();

        try {
            const response = await fetch(`/employee/kpi/${snapshotId}/explain`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }

            if (data.success) {
                content.innerHTML = `
                    <div class="mb-4">
                        <h6 class="text-primary"><i class="bi bi-chat-left-text me-2"></i>Общее объяснение</h6>
                        <p>${escapeHtml(data.explanation || 'Нет объяснения')}</p>
                    </div>

                    ${data.metric_explanations ? `
                        <div class="mb-4">
                            <h6 class="text-primary"><i class="bi bi-list-check me-2"></i>По показателям</h6>
                            <ul class="list-group list-group-flush">
                                ${Object.entries(data.metric_explanations).map(([key, val]) => `
                                    <li class="list-group-item px-0">
                                        <strong>${escapeHtml(key)}:</strong> ${escapeHtml(val)}
                                    </li>
                                `).join('')}
                            </ul>
                        </div>
                    ` : ''}

                    ${data.improvement_suggestions?.length ? `
                        <div>
                            <h6 class="text-primary"><i class="bi bi-lightbulb me-2"></i>Рекомендации</h6>
                            <ul>
                                ${data.improvement_suggestions.map(s => `<li>${escapeHtml(s)}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                `;
            } else {
                throw new Error(data.error || 'Ошибка анализа');
            }
        } catch (error) {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Ошибка загрузки данных
                </div>
            `;
        }
    }
</script>
@endpush
