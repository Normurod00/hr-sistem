@extends('employee.layouts.app')

@section('title', 'KPI - ' . $snapshot->period_label)
@section('page-title', 'KPI за ' . $snapshot->period_label)

@section('content')
<div class="row g-4">
    <!-- Summary Card -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="mb-3">
                    <div class="display-3 fw-bold text-{{ $snapshot->score_color }}">
                        {{ number_format($snapshot->total_score, 1) }}%
                    </div>
                    <div class="text-muted">{{ $snapshot->score_label }}</div>
                </div>

                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar bg-{{ $snapshot->score_color }}"
                         style="width: {{ min(100, $snapshot->total_score) }}%"></div>
                </div>

                <div class="d-flex justify-content-between text-muted small">
                    <span>0%</span>
                    <span>50%</span>
                    <span>100%</span>
                </div>
            </div>

            <div class="card-footer bg-light">
                <div class="row text-center">
                    <div class="col-6 border-end">
                        <div class="fw-bold">{{ $snapshot->period_start->format('d.m.Y') }}</div>
                        <small class="text-muted">Начало</small>
                    </div>
                    <div class="col-6">
                        <div class="fw-bold">{{ $snapshot->period_end->format('d.m.Y') }}</div>
                        <small class="text-muted">Конец</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bonus Info -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Бонус</h6>
            </div>
            <div class="card-body">
                @if($details['bonus_analysis']['eligible'])
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-success-subtle p-2">
                            <i class="bi bi-check-lg text-success fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-success">Бонус положен</div>
                            <div class="fs-4 fw-bold">
                                {{ number_format($details['bonus_analysis']['amount'], 0, ',', ' ') }} сум
                            </div>
                        </div>
                    </div>

                    @if($details['bonus_analysis']['multiplier_explanation'])
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            {{ $details['bonus_analysis']['multiplier_explanation'] }}
                        </div>
                    @endif
                @else
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-danger-subtle p-2">
                            <i class="bi bi-x-lg text-danger fs-4"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-danger">Бонус не положен</div>
                        </div>
                    </div>

                    @if(!empty($details['bonus_analysis']['reasons']))
                        <div class="mt-3">
                            <strong class="small">Причины:</strong>
                            <ul class="small mb-0 mt-2">
                                @foreach($details['bonus_analysis']['reasons'] as $reason)
                                    <li>{{ $reason['message'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="d-grid gap-2 mt-4">
            <a href="{{ route('employee.kpi.recommendations', $snapshot) }}" class="btn btn-brb">
                <i class="bi bi-lightbulb me-2"></i>
                Получить рекомендации
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="explainKpi()">
                <i class="bi bi-robot me-2"></i>
                AI объяснение
            </button>
        </div>
    </div>

    <!-- Metrics Details -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0">Показатели KPI</h5>
            </div>
            <div class="card-body">
                @foreach($snapshot->metrics as $key => $metric)
                    @php
                        $completion = $metric['completion'] ?? 0;
                        $color = match(true) {
                            $completion >= 100 => 'success',
                            $completion >= 70 => 'info',
                            $completion >= 50 => 'warning',
                            default => 'danger',
                        };
                    @endphp
                    <div class="metric-item mb-4 pb-4 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">{{ $metric['name'] }}</h6>
                                <small class="text-muted">
                                    Вес: {{ ($metric['weight'] ?? 0) * 100 }}%
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-bold text-{{ $color }}">
                                    {{ number_format($completion, 1) }}%
                                </div>
                            </div>
                        </div>

                        <div class="progress mb-2" style="height: 8px;">
                            <div class="progress-bar bg-{{ $color }}"
                                 style="width: {{ min(100, $completion) }}%"></div>
                        </div>

                        <div class="d-flex justify-content-between small text-muted">
                            <span>
                                Факт: <strong>{{ number_format($metric['value'] ?? 0, 1) }}</strong>
                                {{ $metric['unit'] ?? '' }}
                            </span>
                            <span>
                                План: <strong>{{ number_format($metric['target'] ?? 0, 1) }}</strong>
                                {{ $metric['unit'] ?? '' }}
                            </span>
                        </div>

                        @if(isset($details['comparison']['metrics_changes'][$key]))
                            @php $change = $details['comparison']['metrics_changes'][$key]; @endphp
                            <div class="mt-2">
                                <small class="text-{{ $change['trend'] === 'up' ? 'success' : ($change['trend'] === 'down' ? 'danger' : 'muted') }}">
                                    <i class="bi bi-arrow-{{ $change['trend'] === 'up' ? 'up' : ($change['trend'] === 'down' ? 'down' : 'right') }}"></i>
                                    {{ $change['completion_delta'] > 0 ? '+' : '' }}{{ number_format($change['completion_delta'], 1) }}%
                                    vs {{ $details['comparison']['previous_period'] }}
                                </small>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Low & High Performing -->
        <div class="row g-4 mt-2">
            @if(!empty($details['low_metrics']))
                <div class="col-md-6">
                    <div class="card border-danger border-opacity-25">
                        <div class="card-header bg-danger-subtle border-0">
                            <h6 class="mb-0 text-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                Требуют внимания
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($details['low_metrics'] as $key => $metric)
                                <div class="d-flex justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <span>{{ $metric['name'] }}</span>
                                    <span class="text-danger fw-bold">{{ number_format($metric['completion'], 1) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($details['high_metrics']))
                <div class="col-md-6">
                    <div class="card border-success border-opacity-25">
                        <div class="card-header bg-success-subtle border-0">
                            <h6 class="mb-0 text-success">
                                <i class="bi bi-trophy me-2"></i>
                                Сильные стороны
                            </h6>
                        </div>
                        <div class="card-body">
                            @foreach($details['high_metrics'] as $key => $metric)
                                <div class="d-flex justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <span>{{ $metric['name'] }}</span>
                                    <span class="text-success fw-bold">{{ number_format($metric['completion'], 1) }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Explain Modal -->
<div class="modal fade" id="explainModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-robot me-2"></i>AI объяснение</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="explainContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-3 text-muted">Анализирую...</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function explainKpi() {
        const modal = new bootstrap.Modal(document.getElementById('explainModal'));
        const content = document.getElementById('explainContent');

        content.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-3 text-muted">Анализирую ваши показатели...</p>
            </div>
        `;
        modal.show();

        try {
            const response = await fetch('{{ route("employee.kpi.explain", $snapshot) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
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
                        <h6><i class="bi bi-chat-left-text me-2 text-primary"></i>Анализ</h6>
                        <p>${escapeHtml(data.explanation)}</p>
                    </div>
                    ${data.improvement_suggestions?.length ? `
                        <div>
                            <h6><i class="bi bi-lightbulb me-2 text-warning"></i>Советы</h6>
                            <ul>${data.improvement_suggestions.map(s => `<li>${escapeHtml(s)}</li>`).join('')}</ul>
                        </div>
                    ` : ''}
                `;
            } else {
                content.innerHTML = `<div class="alert alert-danger">Ошибка загрузки</div>`;
            }
        } catch (e) {
            content.innerHTML = `<div class="alert alert-danger">Ошибка загрузки</div>`;
        }
    }
</script>
@endpush
