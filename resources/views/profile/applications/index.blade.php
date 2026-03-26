@extends('layouts.app')

@section('title', 'Мои отклики — BRB Bank')

@section('content')
<style>
    .applications-page {
        background: #f9fafb;
        min-height: calc(100vh - 200px);
        padding: 40px 0 60px;
    }

    .applications-header {
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 24px 0;
    }

    .applications-title {
        font-size: 28px;
        font-weight: 700;
        color: #222;
        margin-bottom: 4px;
    }

    .applications-subtitle {
        color: #666;
        font-size: 15px;
    }

    .applications-stats {
        display: flex;
        gap: 32px;
        margin-top: 20px;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--brb-red);
    }

    .stat-label {
        font-size: 14px;
        color: #666;
    }

    .application-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        padding: 24px;
        margin-bottom: 16px;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .application-card:hover {
        border-color: var(--brb-red);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    .application-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 16px;
    }

    .application-vacancy-title {
        font-size: 18px;
        font-weight: 700;
        color: #222;
        text-decoration: none;
        display: block;
        margin-bottom: 8px;
    }

    .application-vacancy-title:hover {
        color: var(--brb-red);
    }

    .application-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 14px;
        color: #666;
    }

    .application-meta i {
        color: #999;
        margin-right: 4px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }

    .status-new {
        background: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .status-in_review {
        background: rgba(255, 193, 7, 0.15);
        color: #997404;
    }

    .status-invited {
        background: rgba(35, 169, 66, 0.1);
        color: #1a8f3e;
    }

    .status-rejected {
        background: rgba(214, 0, 28, 0.1);
        color: var(--brb-red);
    }

    .status-hired {
        background: rgba(35, 169, 66, 0.15);
        color: #1a8f3e;
    }

    .application-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 16px;
        border-top: 1px solid #f0f0f0;
        margin-top: 16px;
    }

    .application-date {
        font-size: 13px;
        color: #888;
    }

    .match-score {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .match-score-bar {
        width: 60px;
        height: 6px;
        background: #e5e5e5;
        border-radius: 3px;
        overflow: hidden;
    }

    .match-score-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s;
    }

    .match-score-fill.high {
        background: var(--brb-green);
    }

    .match-score-fill.medium {
        background: #ffc107;
    }

    .match-score-fill.low {
        background: var(--brb-red);
    }

    .btn-view {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 20px;
        background: #f5f5f5;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s;
    }

    .btn-view:hover {
        background: var(--brb-red);
        color: #fff;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        background: #fff;
        border-radius: 12px;
    }

    .empty-state i {
        font-size: 64px;
        color: #ddd;
        margin-bottom: 20px;
    }

    .empty-state h4 {
        font-weight: 700;
        color: #333;
        margin-bottom: 8px;
    }

    .empty-state p {
        color: #666;
        margin-bottom: 24px;
    }

    .btn-find-job {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        background: var(--brb-red);
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.2s;
    }

    .btn-find-job:hover {
        background: #b8001a;
        color: #fff;
    }

    @media (max-width: 768px) {
        .applications-stats {
            flex-direction: column;
            gap: 16px;
        }

        .application-card-header {
            flex-direction: column;
            gap: 12px;
        }

        .application-footer {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }
    }
</style>

<!-- Header -->
<div class="applications-header">
    <div class="container">
        <h1 class="applications-title">Мои отклики</h1>
        <p class="applications-subtitle">Отслеживайте статус ваших заявок</p>

        <div class="applications-stats">
            <div class="stat-item">
                <span class="stat-value">{{ $applications->total() }}</span>
                <span class="stat-label">{{ trans_choice('отклик|отклика|откликов', $applications->total()) }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="applications-page">
    <div class="container">
        @if($applications->count() > 0)
            @foreach($applications as $application)
                <div class="application-card">
                    <div class="application-card-header">
                        <div>
                            <a href="{{ route('vacant.show', $application->vacancy) }}" class="application-vacancy-title">
                                {{ $application->vacancy->title }}
                            </a>
                            <div class="application-meta">
                                <span><i class="bi bi-building"></i> BRB Bank</span>
                                @if($application->vacancy->location)
                                    <span><i class="bi bi-geo-alt"></i> {{ $application->vacancy->location }}</span>
                                @endif
                                <span><i class="bi bi-briefcase"></i> {{ $application->vacancy->employment_type_label }}</span>
                            </div>
                        </div>

                        <span class="status-badge status-{{ $application->status->value }}">
                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i>
                            {{ $application->status_label }}
                        </span>
                    </div>

                    <div class="application-footer">
                        <div class="d-flex align-items-center gap-4">
                            <span class="application-date">
                                <i class="bi bi-clock me-1"></i>
                                Подано {{ $application->created_at->diffForHumans() }}
                            </span>

                            @if($application->match_score !== null)
                                <div class="match-score">
                                    <span>Совпадение:</span>
                                    <div class="match-score-bar">
                                        <div class="match-score-fill {{ $application->match_score >= 70 ? 'high' : ($application->match_score >= 40 ? 'medium' : 'low') }}"
                                             style="width: {{ $application->match_score }}%"></div>
                                    </div>
                                    <strong>{{ $application->match_score }}%</strong>
                                </div>
                            @endif
                        </div>

                        <a href="{{ route('profile.applications.show', $application) }}" class="btn-view">
                            <i class="bi bi-eye"></i> Подробнее
                        </a>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            @if($applications->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $applications->links() }}
                </div>
            @endif
        @else
            <div class="empty-state">
                <i class="bi bi-file-earmark-text"></i>
                <h4>У вас пока нет откликов</h4>
                <p>Найдите интересную вакансию и отправьте свой первый отклик</p>
                <a href="{{ route('vacant.index') }}" class="btn-find-job">
                    <i class="bi bi-search"></i> Найти вакансию
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
