@extends('layouts.admin')

@section('title', 'Заявки')
@section('header', 'Все заявки')

@section('content')
<style>
    .filter-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid #e5e5e5;
        margin-bottom: 24px;
    }

    .filter-card .card-body {
        padding: 20px 24px;
    }

    .filter-form .form-label {
        font-weight: 500;
        color: #555;
    }

    .filter-form .form-select,
    .filter-form .form-control {
        border-radius: 8px;
        border: 1px solid #ccc;
        padding: 10px 14px;
        font-size: 14px;
        height: auto;
        background-color: #fff;
        color: #333;
    }

    .filter-form .form-select:focus,
    .filter-form .form-control:focus {
        border-color: var(--brb-red);
        box-shadow: 0 0 0 3px rgba(214, 0, 28, 0.15);
    }

    .filter-form .btn {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
    }

    .filter-form .btn-outline-secondary {
        border-color: #999;
        color: #555;
    }

    .filter-form .btn-outline-secondary:hover {
        background-color: #f5f5f5;
        border-color: #777;
        color: #333;
    }

    .applications-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid #e5e5e5;
        overflow: hidden;
    }

    .applications-table {
        margin-bottom: 0;
    }

    .applications-table thead th {
        background: #f5f5f5;
        border-bottom: 2px solid #ddd;
        padding: 16px 20px;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #666;
        white-space: nowrap;
    }

    .applications-table tbody td {
        padding: 18px 20px;
        vertical-align: middle;
        border-bottom: 1px solid #eee;
        background: #fff;
    }

    .applications-table tbody tr:hover td {
        background-color: #fafafa;
    }

    .applications-table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Candidate Cell */
    .candidate-cell {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .candidate-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #ddd;
        flex-shrink: 0;
        background: #f0f0f0;
    }

    .candidate-info {
        min-width: 0;
    }

    .candidate-name {
        font-weight: 600;
        color: #222;
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .candidate-email {
        font-size: 13px;
        color: #777;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Vacancy Link */
    .vacancy-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .vacancy-link:hover {
        color: var(--brb-red);
        text-decoration: underline;
    }

    /* Status Badge */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 7px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .status-badge.badge-new {
        background: #e7f1ff;
        color: #0055cc;
        border-color: #b8d4ff;
    }

    .status-badge.badge-in_review {
        background: #fff8e6;
        color: #8a6d00;
        border-color: #ffe69c;
    }

    .status-badge.badge-invited {
        background: #e6f4ea;
        color: #137333;
        border-color: #a8dab5;
    }

    .status-badge.badge-rejected {
        background: #fce8e8;
        color: #c5221f;
        border-color: #f5b7b7;
    }

    .status-badge.badge-hired {
        background: #ceead6;
        color: #0d652d;
        border-color: #81c995;
    }

    .status-badge.badge-withdrawn {
        background: #f1f3f4;
        color: #5f6368;
        border-color: #dadce0;
    }

    /* Match Score */
    .match-score-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 56px;
        padding: 7px 12px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .match-score-badge.high {
        background: #e6f4ea;
        color: #137333;
        border-color: #a8dab5;
    }

    .match-score-badge.medium {
        background: #fff8e6;
        color: #8a6d00;
        border-color: #ffe69c;
    }

    .match-score-badge.low {
        background: #fce8e8;
        color: #c5221f;
        border-color: #f5b7b7;
    }

    .match-score-badge.none {
        background: #f5f5f5;
        color: #999;
        border-color: #ddd;
    }

    /* AI Badge */
    .ai-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .ai-badge.ready {
        background: #e6f4ea;
        color: #137333;
        border-color: #a8dab5;
    }

    .ai-badge.pending {
        background: #fff8e6;
        color: #8a6d00;
        border-color: #ffe69c;
    }

    /* Test Score Badge */
    .test-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .test-badge.completed-high {
        background: #e6f4ea;
        color: #137333;
        border-color: #a8dab5;
    }

    .test-badge.completed-medium {
        background: #fff8e6;
        color: #8a6d00;
        border-color: #ffe69c;
    }

    .test-badge.completed-low {
        background: #fce8e8;
        color: #c5221f;
        border-color: #f5b7b7;
    }

    .test-badge.in-progress {
        background: #e7f1ff;
        color: #0055cc;
        border-color: #b8d4ff;
    }

    .test-badge.expired {
        background: #fce8e8;
        color: #c5221f;
        border-color: #f5b7b7;
    }

    .test-badge.not-started {
        background: #f5f5f5;
        color: #999;
        border-color: #ddd;
    }

    /* Date */
    .date-cell {
        font-size: 13px;
        color: #666;
        white-space: nowrap;
        font-weight: 500;
    }

    /* Action Button */
    .btn-view {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 18px;
        background-color: #d6001c !important;
        color: #ffffff !important;
        border: 1px solid #b8001a !important;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s;
        white-space: nowrap;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-view i,
    .btn-view span {
        color: #ffffff !important;
    }

    .btn-view:hover {
        background-color: #b8001a !important;
        color: #ffffff !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        text-decoration: none !important;
    }

    .btn-view:hover i,
    .btn-view:hover span {
        color: #ffffff !important;
    }

    /* Empty State */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        background: #fafafa;
    }

    .empty-state i {
        font-size: 4rem;
        color: #ccc;
        margin-bottom: 16px;
    }

    .empty-state p {
        color: #777;
        font-size: 15px;
        margin: 0;
        font-weight: 500;
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 24px;
    }

    /* Stats Header */
    .stats-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 24px;
        background: #f8f8f8;
        border-bottom: 1px solid #e5e5e5;
    }

    .stats-text {
        font-size: 14px;
        color: #666;
    }

    .stats-text strong {
        color: #333;
        font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .applications-table thead th,
        .applications-table tbody td {
            padding: 14px 12px;
        }

        .candidate-cell {
            gap: 10px;
        }

        .candidate-avatar {
            width: 36px;
            height: 36px;
        }

        .btn-view {
            padding: 8px 12px;
        }

        .btn-view span {
            display: none;
        }
    }
</style>

<!-- Filters -->
<div class="filter-card card">
    <div class="card-body">
        <form action="{{ route('admin.applications.index') }}" method="GET" class="filter-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small mb-1">Статус</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Все статусы</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label class="form-label small mb-1">Минимальный Match Score</label>
                    <input type="number" name="min_score" class="form-control" placeholder="Например: 60"
                           value="{{ request('min_score') }}" min="0" max="100">
                </div>
                <div class="col-md-2 col-sm-6">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-funnel me-1"></i> Применить
                    </button>
                </div>
                @if(request()->hasAny(['status', 'min_score']))
                    <div class="col-md-2 col-sm-6">
                        <a href="{{ route('admin.applications.index') }}" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-lg me-1"></i> Сбросить
                        </a>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Applications Table -->
<div class="applications-card card">
    @if($applications->count() > 0)
        <div class="stats-header">
            <span class="stats-text">
                Показано <strong>{{ $applications->firstItem() }}–{{ $applications->lastItem() }}</strong> из <strong>{{ $applications->total() }}</strong> заявок
            </span>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table applications-table">
            <thead>
                <tr>
                    <th>Кандидат</th>
                    <th>Вакансия</th>
                    <th>Статус</th>
                    <th class="text-center">Match</th>
                    <th class="text-center">Тест</th>
                    <th>AI-анализ</th>
                    <th>Дата</th>
                    <th width="130"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td>
                            <div class="candidate-cell">
                                <img src="{{ $application->candidate->avatar_url }}"
                                     alt="{{ $application->candidate->name }}"
                                     class="candidate-avatar">
                                <div class="candidate-info">
                                    <div class="candidate-name">{{ $application->candidate->name }}</div>
                                    <div class="candidate-email">{{ $application->candidate->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.vacancies.show', $application->vacancy) }}" class="vacancy-link">
                                {{ Str::limit($application->vacancy->title, 35) }}
                            </a>
                        </td>
                        <td>
                            <span class="status-badge badge-{{ $application->status->value }}">
                                {{ $application->status_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($application->match_score !== null)
                                @php
                                    $scoreClass = $application->match_score >= 60 ? 'high' : ($application->match_score >= 40 ? 'medium' : 'low');
                                @endphp
                                <span class="match-score-badge {{ $scoreClass }}">
                                    {{ $application->match_score }}%
                                </span>
                            @else
                                <span class="match-score-badge none">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($application->candidateTest)
                                @php $test = $application->candidateTest; @endphp
                                @if($test->status === 'completed')
                                    @php
                                        $testClass = $test->score >= 60 ? 'completed-high' : ($test->score >= 40 ? 'completed-medium' : 'completed-low');
                                    @endphp
                                    <span class="test-badge {{ $testClass }}">
                                        <i class="bi bi-check-circle-fill"></i> {{ $test->score }}%
                                    </span>
                                @elseif($test->status === 'in_progress')
                                    <span class="test-badge in-progress">
                                        <i class="bi bi-hourglass-split"></i> Идёт
                                    </span>
                                @elseif($test->status === 'expired')
                                    <span class="test-badge expired">
                                        <i class="bi bi-x-circle-fill"></i> Истёк
                                    </span>
                                @else
                                    <span class="test-badge not-started">
                                        <i class="bi bi-clock"></i> Ждёт
                                    </span>
                                @endif
                            @else
                                <span class="test-badge not-started">—</span>
                            @endif
                        </td>
                        <td>
                            @if($application->analysis)
                                <span class="ai-badge ready">
                                    <i class="bi bi-check-circle-fill"></i> Готов
                                </span>
                            @else
                                <span class="ai-badge pending">
                                    <i class="bi bi-hourglass-split"></i> Ожидает
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="date-cell">{{ $application->created_at->format('d.m.Y') }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.applications.show', $application) }}" class="btn-view">
                                <i class="bi bi-eye-fill"></i> <span>Открыть</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <p>Заявки не найдены</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($applications->hasPages())
    <div class="pagination-wrapper">
        {{ $applications->links() }}
    </div>
@endif
@endsection
