@extends('layouts.admin')

@section('title', 'Рейтинг сотрудников')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">
        <i class="bi bi-bar-chart-fill text-success me-2"></i>
        Рейтинг сотрудников
    </h4>
    <div class="d-flex align-items-center gap-2">
        <span class="text-muted small">Всего сотрудников: {{ $totalEmployees }}</span>
    </div>
</div>

<!-- Period Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex gap-2">
            <a href="{{ route('admin.recognition.leaderboard', ['period' => 'month']) }}"
               class="btn {{ $currentPeriod === 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                Месяц
            </a>
            <a href="{{ route('admin.recognition.leaderboard', ['period' => 'quarter']) }}"
               class="btn {{ $currentPeriod === 'quarter' ? 'btn-primary' : 'btn-outline-primary' }}">
                Квартал
            </a>
            <a href="{{ route('admin.recognition.leaderboard', ['period' => 'year']) }}"
               class="btn {{ $currentPeriod === 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                Год
            </a>
            <a href="{{ route('admin.recognition.leaderboard', ['period' => 'all']) }}"
               class="btn {{ $currentPeriod === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">
                Всё время
            </a>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center" width="60">#</th>
                        <th>Сотрудник</th>
                        <th class="text-center">Баллы</th>
                        <th class="text-center">Номинации</th>
                        <th class="text-center">Награды</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaderboard as $index => $entry)
                    <tr class="{{ $index < 3 ? 'table-warning' : '' }}">
                        <td class="text-center">
                            @if($index === 0)
                                <i class="bi bi-trophy-fill text-warning fs-5"></i>
                            @elseif($index === 1)
                                <i class="bi bi-trophy-fill text-secondary fs-5"></i>
                            @elseif($index === 2)
                                <i class="bi bi-trophy-fill" style="color: #cd7f32; font-size: 1.25rem;"></i>
                            @else
                                <span class="fw-semibold text-muted">{{ $index + 1 }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $entry['user']->name ?? $entry['name'] ?? 'Unknown' }}</div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success fs-6">{{ $entry['points'] ?? $entry['total_points'] ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted">{{ $entry['nominations_count'] ?? 0 }}</span>
                        </td>
                        <td class="text-center">
                            <span class="text-muted">{{ $entry['awards_count'] ?? 0 }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-bar-chart" style="font-size: 48px; opacity: 0.3;"></i>
                            <div class="mt-2">Нет данных за выбранный период</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
