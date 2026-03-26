@extends('layouts.app')

@section('title', 'Заявка: ' . $application->vacancy->title . ' — BRB Bank')

@section('content')
<style>
    .application-detail-page {
        background: #f9fafb;
        min-height: calc(100vh - 200px);
        padding: 40px 0 60px;
    }

    .application-detail-header {
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 20px 0;
    }

    .application-detail-header .breadcrumb {
        margin-bottom: 0;
        font-size: 14px;
    }

    .application-detail-header .breadcrumb a {
        color: var(--brb-red);
        text-decoration: none;
    }

    .vacancy-info-header {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        padding: 28px;
        margin-bottom: 24px;
    }

    .vacancy-info-title {
        font-size: 24px;
        font-weight: 700;
        color: #222;
        margin-bottom: 12px;
    }

    .vacancy-info-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        font-size: 14px;
        color: #666;
        margin-bottom: 20px;
    }

    .vacancy-info-meta i {
        color: #999;
        margin-right: 6px;
    }

    .status-header {
        display: flex;
        align-items: center;
        gap: 24px;
        padding-top: 20px;
        border-top: 1px solid #f0f0f0;
    }

    .status-badge-large {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 25px;
        font-size: 15px;
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

    .stat-box {
        text-align: center;
        padding: 0 20px;
        border-left: 1px solid #e5e5e5;
    }

    .stat-box:first-child {
        border-left: none;
        padding-left: 0;
    }

    .stat-box-label {
        font-size: 12px;
        color: #888;
        margin-bottom: 4px;
    }

    .stat-box-value {
        font-size: 18px;
        font-weight: 700;
        color: #222;
    }

    .match-score-large {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .match-score-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
    }

    .match-score-circle.high {
        background: var(--brb-green);
    }

    .match-score-circle.medium {
        background: #ffc107;
        color: #333;
    }

    .match-score-circle.low {
        background: var(--brb-red);
    }

    .detail-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .detail-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .detail-card-header i {
        font-size: 20px;
        color: var(--brb-red);
    }

    .detail-card-title {
        font-size: 18px;
        font-weight: 700;
        color: #222;
        margin: 0;
    }

    .detail-card-body {
        padding: 24px;
    }

    .cover-letter-text {
        font-size: 15px;
        line-height: 1.7;
        color: #333;
        white-space: pre-line;
    }

    .file-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 12px;
    }

    .file-item:last-child {
        margin-bottom: 0;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .file-icon {
        width: 44px;
        height: 44px;
        background: rgba(214, 0, 28, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--brb-red);
        font-size: 20px;
    }

    .file-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 2px;
    }

    .file-size {
        font-size: 12px;
        color: #888;
    }

    .file-status {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .file-status.parsed {
        background: rgba(35, 169, 66, 0.1);
        color: #1a8f3e;
    }

    .file-status.pending {
        background: rgba(255, 193, 7, 0.15);
        color: #997404;
    }

    /* Timeline */
    .timeline-card {
        position: sticky;
        top: 90px;
    }

    .timeline-item {
        display: flex;
        gap: 16px;
        position: relative;
        padding-bottom: 24px;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 40px;
        bottom: 0;
        width: 2px;
        background: #e5e5e5;
    }

    .timeline-item.active:not(:last-child)::before {
        background: var(--brb-red);
    }

    .timeline-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
        z-index: 1;
    }

    .timeline-icon.active {
        background: var(--brb-red);
        color: #fff;
    }

    .timeline-icon.inactive {
        background: #e9ecef;
        color: #adb5bd;
    }

    .timeline-icon.rejected {
        background: #dc3545;
        color: #fff;
    }

    .timeline-content {
        flex: 1;
    }

    .timeline-title {
        font-weight: 600;
        color: #222;
        margin-bottom: 2px;
    }

    .timeline-title.inactive {
        color: #adb5bd;
    }

    .timeline-subtitle {
        font-size: 13px;
        color: var(--brb-red);
    }

    .back-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--brb-red);
        text-decoration: none;
        font-weight: 500;
        margin-bottom: 20px;
    }

    .back-link:hover {
        text-decoration: underline;
        color: var(--brb-red);
    }

    @media (max-width: 991px) {
        .status-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
        }

        .stat-box {
            border-left: none;
            padding: 0;
        }
    }
</style>

<!-- Header -->
<div class="application-detail-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('profile.applications') }}">Мои отклики</a></li>
                <li class="breadcrumb-item text-muted">{{ Str::limit($application->vacancy->title, 40) }}</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="application-detail-page">
    <div class="container">
        <a href="{{ route('profile.applications') }}" class="back-link">
            <i class="bi bi-arrow-left"></i> Вернуться к откликам
        </a>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Vacancy Info Header -->
                <div class="vacancy-info-header">
                    <h1 class="vacancy-info-title">{{ $application->vacancy->title }}</h1>

                    <div class="vacancy-info-meta">
                        <span><i class="bi bi-building"></i> BRB Bank</span>
                        @if($application->vacancy->location)
                            <span><i class="bi bi-geo-alt"></i> {{ $application->vacancy->location }}</span>
                        @endif
                        <span><i class="bi bi-briefcase"></i> {{ $application->vacancy->employment_type_label }}</span>
                        @if($application->vacancy->salary_formatted)
                            <span><i class="bi bi-cash"></i> {{ $application->vacancy->salary_formatted }}</span>
                        @endif
                    </div>

                    <div class="status-header">
                        <span class="status-badge-large status-{{ $application->status->value }}">
                            <i class="bi bi-circle-fill" style="font-size: 8px;"></i>
                            {{ $application->status_label }}
                        </span>

                        <div class="d-flex">
                            <div class="stat-box">
                                <div class="stat-box-label">Дата подачи</div>
                                <div class="stat-box-value">{{ $application->created_at->format('d.m.Y') }}</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-box-label">Обновлено</div>
                                <div class="stat-box-value">{{ $application->updated_at->format('d.m.Y') }}</div>
                            </div>
                            @if($application->match_score !== null)
                                <div class="stat-box">
                                    <div class="stat-box-label">Совпадение</div>
                                    <div class="match-score-large">
                                        <div class="match-score-circle {{ $application->match_score >= 70 ? 'high' : ($application->match_score >= 40 ? 'medium' : 'low') }}">
                                            {{ $application->match_score }}%
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Cover Letter -->
                @if($application->cover_letter)
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <i class="bi bi-chat-text"></i>
                            <h2 class="detail-card-title">Сопроводительное письмо</h2>
                        </div>
                        <div class="detail-card-body">
                            <div class="cover-letter-text">{{ $application->cover_letter }}</div>
                        </div>
                    </div>
                @endif

                <!-- Files -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <i class="bi bi-paperclip"></i>
                        <h2 class="detail-card-title">Прикреплённые документы</h2>
                    </div>
                    <div class="detail-card-body">
                        @if($application->files->count())
                            @foreach($application->files as $file)
                                <div class="file-item">
                                    <div class="file-info">
                                        <div class="file-icon">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </div>
                                        <div>
                                            <div class="file-name">{{ $file->original_name }}</div>
                                            <div class="file-size">{{ $file->size_formatted }}</div>
                                        </div>
                                    </div>
                                    <span class="file-status {{ $file->is_parsed ? 'parsed' : 'pending' }}">
                                        <i class="bi bi-{{ $file->is_parsed ? 'check-circle' : 'hourglass-split' }} me-1"></i>
                                        {{ $file->is_parsed ? 'Обработан' : 'Обрабатывается' }}
                                    </span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted mb-0">Нет прикреплённых файлов</p>
                        @endif
                    </div>
                </div>

                <!-- Test Results -->
                @if($application->candidateTest)
                    @php $test = $application->candidateTest; @endphp
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <i class="bi bi-clipboard-check"></i>
                            <h2 class="detail-card-title">Результат теста</h2>
                        </div>
                        <div class="detail-card-body">
                            @if($test->status === 'completed')
                                <div class="row g-3 mb-3">
                                    <div class="col-6 col-md-3">
                                        <div class="text-center p-3 rounded" style="background: {{ $test->score >= 60 ? 'rgba(35, 169, 66, 0.1)' : 'rgba(214, 0, 28, 0.1)' }};">
                                            <div style="font-size: 28px; font-weight: 700; color: {{ $test->score >= 60 ? '#1a8f3e' : 'var(--brb-red)' }};">{{ $test->score }}%</div>
                                            <div style="font-size: 13px; color: #666;">Результат</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="text-center p-3 rounded" style="background: #f8f9fa;">
                                            <div style="font-size: 28px; font-weight: 700; color: #333;">{{ $test->correct_answers }}/{{ $test->total_questions }}</div>
                                            <div style="font-size: 13px; color: #666;">Правильно</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="text-center p-3 rounded" style="background: #f8f9fa;">
                                            <div style="font-size: 28px; font-weight: 700; color: #333;">{{ floor($test->time_spent / 60) }}:{{ str_pad($test->time_spent % 60, 2, '0', STR_PAD_LEFT) }}</div>
                                            <div style="font-size: 13px; color: #666;">Время</div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="text-center p-3 rounded" style="background: #f8f9fa;">
                                            <div style="font-size: 20px; font-weight: 700; color: #333;">{{ $test->completed_at?->format('d.m.Y') }}</div>
                                            <div style="font-size: 13px; color: #666;">Дата</div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-muted mb-0" style="font-size: 14px;">
                                    @if($test->score >= 80)
                                        <i class="bi bi-emoji-smile text-success me-1"></i> Отличный результат! Вы показали высокий уровень знаний.
                                    @elseif($test->score >= 60)
                                        <i class="bi bi-emoji-neutral text-warning me-1"></i> Хороший результат. Вы успешно прошли тест.
                                    @else
                                        <i class="bi bi-emoji-frown text-danger me-1"></i> К сожалению, результат ниже ожидаемого.
                                    @endif
                                </p>
                            @elseif($test->status === 'in_progress')
                                <div class="alert alert-info mb-0 d-flex align-items-center gap-3">
                                    <i class="bi bi-hourglass-split" style="font-size: 24px;"></i>
                                    <div>
                                        <strong>Тест в процессе</strong>
                                        <div class="small">Завершите тест, чтобы увидеть результаты.</div>
                                    </div>
                                    <a href="{{ route('tests.show', $application) }}" class="btn btn-primary ms-auto">Продолжить</a>
                                </div>
                            @elseif($test->status === 'expired')
                                <div class="alert alert-danger mb-0 d-flex align-items-center gap-3">
                                    <i class="bi bi-clock" style="font-size: 24px;"></i>
                                    <div>
                                        <strong>Время истекло</strong>
                                        <div class="small">К сожалению, вы не успели завершить тест.</div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning mb-0 d-flex align-items-center gap-3">
                                    <i class="bi bi-clipboard" style="font-size: 24px;"></i>
                                    <div>
                                        <strong>Тест ожидает прохождения</strong>
                                        <div class="small">Пройдите тест для оценки ваших знаний и навыков.</div>
                                    </div>
                                    <a href="{{ route('tests.show', $application) }}" class="btn btn-warning ms-auto text-dark">Начать тест</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Match Score Explanation -->
                @if($application->match_score !== null)
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <i class="bi bi-graph-up-arrow"></i>
                            <h2 class="detail-card-title">Оценка соответствия</h2>
                        </div>
                        <div class="detail-card-body">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="match-score-circle {{ $application->match_score >= 70 ? 'high' : ($application->match_score >= 40 ? 'medium' : 'low') }}" style="width: 80px; height: 80px; font-size: 24px;">
                                    {{ $application->match_score }}%
                                </div>
                                <div>
                                    <h5 class="mb-1" style="font-weight: 700;">
                                        @if($application->match_score >= 80)
                                            Отличное соответствие
                                        @elseif($application->match_score >= 60)
                                            Хорошее соответствие
                                        @elseif($application->match_score >= 40)
                                            Среднее соответствие
                                        @else
                                            Низкое соответствие
                                        @endif
                                    </h5>
                                    <p class="text-muted mb-0" style="font-size: 14px;">
                                        Ваш профиль соответствует требованиям вакансии на {{ $application->match_score }}%
                                    </p>
                                </div>
                            </div>
                            <hr>
                            <p class="text-muted mb-0" style="font-size: 13px;">
                                <i class="bi bi-info-circle me-1"></i>
                                Оценка рассчитывается на основе анализа вашего резюме и требований вакансии. Учитываются навыки, опыт работы и образование.
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Chat Access -->
                @if(in_array($application->status->value, ['invited', 'hired']))
                    <div class="detail-card">
                        <div class="detail-card-body text-center py-4">
                            <i class="bi bi-chat-dots" style="font-size: 48px; color: var(--brb-red); opacity: 0.8;"></i>
                            <h5 class="mt-3 mb-2" style="font-weight: 700;">Чат с HR открыт!</h5>
                            <p class="text-muted mb-3">Теперь вы можете общаться с HR-специалистом напрямую</p>
                            <a href="{{ route('chat.show', $application) }}" class="btn btn-lg" style="background: var(--brb-red); color: #fff; padding: 12px 32px;">
                                <i class="bi bi-chat-dots me-2"></i> Открыть чат
                            </a>
                        </div>
                    </div>
                @endif

                <!-- View Vacancy Button -->
                <div class="text-center mt-4">
                    <a href="{{ route('vacant.show', $application->vacancy) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-eye me-2"></i> Посмотреть вакансию
                    </a>
                </div>
            </div>

            <!-- Right Column - Timeline -->
            <div class="col-lg-4">
                <div class="detail-card timeline-card">
                    <div class="detail-card-header">
                        <i class="bi bi-clock-history"></i>
                        <h2 class="detail-card-title">История заявки</h2>
                    </div>
                    <div class="detail-card-body">
                        @php
                            $statuses = [
                                'new' => ['label' => 'Заявка получена', 'icon' => 'inbox-fill'],
                                'in_review' => ['label' => 'На рассмотрении', 'icon' => 'eye-fill'],
                                'invited' => ['label' => 'Приглашение на собеседование', 'icon' => 'calendar-check-fill'],
                                'hired' => ['label' => 'Принят на работу', 'icon' => 'trophy-fill'],
                            ];
                            $currentStatus = $application->status->value;
                            $statusOrder = array_keys($statuses);
                            $currentIndex = array_search($currentStatus, $statusOrder);
                            if ($currentStatus === 'rejected') $currentIndex = -1;
                        @endphp

                        @foreach($statuses as $key => $status)
                            @php
                                $index = array_search($key, $statusOrder);
                                $isActive = $index <= $currentIndex;
                                $isCurrent = $key === $currentStatus;
                            @endphp
                            <div class="timeline-item {{ $isActive ? 'active' : '' }}">
                                <div class="timeline-icon {{ $isActive ? 'active' : 'inactive' }}">
                                    <i class="bi bi-{{ $status['icon'] }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title {{ !$isActive ? 'inactive' : '' }}">
                                        {{ $status['label'] }}
                                    </div>
                                    @if($isCurrent)
                                        <div class="timeline-subtitle">Текущий статус</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if($currentStatus === 'rejected')
                            <div class="timeline-item">
                                <div class="timeline-icon rejected">
                                    <i class="bi bi-x-lg"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-title">Отклонено</div>
                                    <div class="timeline-subtitle" style="color: #dc3545;">
                                        К сожалению, ваша кандидатура не подошла
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
