@extends('employee.layouts.app')

@section('title', 'Рекомендации по KPI')
@section('page-title', 'Рекомендации по улучшению KPI')

@section('content')
<div class="row g-4">
    <!-- Summary -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle bg-primary-subtle p-3">
                        <i class="bi bi-graph-up-arrow text-primary fs-4"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">{{ $snapshot->period_label }}</h5>
                        <span class="text-{{ $snapshot->score_color }} fw-bold">
                            {{ number_format($snapshot->total_score, 1) }}%
                        </span>
                    </div>
                </div>

                <p class="text-muted small mb-0">
                    Персональные рекомендации на основе анализа ваших показателей KPI.
                </p>
            </div>
        </div>

        <!-- Back Link -->
        <div class="mt-3">
            <a href="{{ route('employee.kpi.show', $snapshot) }}" class="btn btn-outline-secondary w-100">
                <i class="bi bi-arrow-left me-2"></i>
                Вернуться к KPI
            </a>
        </div>
    </div>

    <!-- Recommendations List -->
    <div class="col-lg-8">
        @if(count($recommendations) > 0)
            <!-- Priority Actions -->
            @if(isset($priorityActions) && count($priorityActions) > 0)
                <div class="card border-warning border-opacity-50 mb-4">
                    <div class="card-header bg-warning-subtle border-0 py-3">
                        <h6 class="mb-0">
                            <i class="bi bi-lightning-charge text-warning me-2"></i>
                            Приоритетные действия
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($priorityActions as $action)
                            <div class="d-flex gap-3 {{ !$loop->last ? 'mb-3 pb-3 border-bottom' : '' }}">
                                <div class="flex-shrink-0">
                                    <span class="badge bg-warning text-dark">{{ $loop->iteration }}</span>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $action['action'] ?? $action->action }}</div>
                                    @if(isset($action['effect']) || isset($action->expected_effect))
                                        <small class="text-muted">
                                            <i class="bi bi-arrow-right me-1"></i>
                                            {{ $action['effect'] ?? $action->expected_effect }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Wins -->
            @php
                $quickRecs = collect($recommendations)->filter(fn($r) => ($r['type'] ?? $r->type ?? '') === 'quick');
                $mediumRecs = collect($recommendations)->filter(fn($r) => ($r['type'] ?? $r->type ?? '') === 'medium');
                $longRecs = collect($recommendations)->filter(fn($r) => ($r['type'] ?? $r->type ?? '') === 'long');
            @endphp

            @if($quickRecs->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success-subtle border-0 py-3">
                        <h6 class="mb-0">
                            <i class="bi bi-rocket-takeoff text-success me-2"></i>
                            Быстрые победы (1-2 недели)
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($quickRecs as $rec)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-medium">{{ $rec['action'] ?? $rec->action }}</div>
                                        @if(isset($rec['effect']) || isset($rec->expected_effect))
                                            <small class="text-muted">{{ $rec['effect'] ?? $rec->expected_effect }}</small>
                                        @endif
                                    </div>
                                    @if(isset($rec['expected_impact']) || isset($rec->expected_impact))
                                        <span class="badge bg-success">
                                            +{{ number_format($rec['expected_impact'] ?? $rec->expected_impact, 1) }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($mediumRecs->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info-subtle border-0 py-3">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar3 text-info me-2"></i>
                            Среднесрочные (1-3 месяца)
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($mediumRecs as $rec)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-medium">{{ $rec['action'] ?? $rec->action }}</div>
                                        @if(isset($rec['effect']) || isset($rec->expected_effect))
                                            <small class="text-muted">{{ $rec['effect'] ?? $rec->expected_effect }}</small>
                                        @endif
                                        @if(isset($rec['metric']) || isset($rec->metric))
                                            <span class="badge bg-light text-dark ms-2">{{ $rec['metric'] ?? $rec->metric }}</span>
                                        @endif
                                    </div>
                                    @if(isset($rec['expected_impact']) || isset($rec->expected_impact))
                                        <span class="badge bg-info">
                                            +{{ number_format($rec['expected_impact'] ?? $rec->expected_impact, 1) }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($longRecs->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-secondary-subtle border-0 py-3">
                        <h6 class="mb-0">
                            <i class="bi bi-calendar-range text-secondary me-2"></i>
                            Долгосрочные (3-12 месяцев)
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($longRecs as $rec)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-medium">{{ $rec['action'] ?? $rec->action }}</div>
                                        @if(isset($rec['effect']) || isset($rec->expected_effect))
                                            <small class="text-muted">{{ $rec['effect'] ?? $rec->expected_effect }}</small>
                                        @endif
                                    </div>
                                    @if(isset($rec['expected_impact']) || isset($rec->expected_impact))
                                        <span class="badge bg-secondary">
                                            +{{ number_format($rec['expected_impact'] ?? $rec->expected_impact, 1) }}%
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Expected Improvement -->
            @if(isset($expectedImprovement))
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body text-center">
                        <h6 class="text-muted mb-2">Ожидаемое улучшение при выполнении всех рекомендаций</h6>
                        <div class="display-6 text-success fw-bold">
                            +{{ number_format($expectedImprovement['total'] ?? 0, 1) }}%
                        </div>
                        <small class="text-muted">к общему KPI</small>
                    </div>
                </div>
            @endif
        @else
            @if(isset($aiError) && $aiError)
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-exclamation-triangle fs-1 text-warning d-block mb-3"></i>
                        <h5>Не удалось получить рекомендации</h5>
                        <p class="text-muted mb-0">
                            {{ $aiError }}
                        </p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-trophy fs-1 text-success d-block mb-3"></i>
                        <h5>Отличная работа!</h5>
                        <p class="text-muted mb-0">
                            Ваши показатели на высоком уровне. Продолжайте в том же духе!
                        </p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
