@extends('layouts.admin')

@section('title', 'Заявка кандидата')
@section('header', 'Заявка: ' . $application->candidate->name)

@push('styles')
<style>
/* Top Stats Row */
.top-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.top-stat-card {
    background: var(--panel);
    border: 1px solid var(--br);
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
}

.top-stat-card:hover {
    border-color: var(--accent);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.top-stat-card__icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 22px;
    flex-shrink: 0;
}

.top-stat-card__icon.green { background: rgba(34, 197, 94, 0.15); color: #16a34a; }
.top-stat-card__icon.yellow { background: rgba(245, 158, 11, 0.15); color: #d97706; }
.top-stat-card__icon.red { background: rgba(239, 68, 68, 0.15); color: #dc2626; }
.top-stat-card__icon.blue { background: rgba(59, 130, 246, 0.15); color: #2563eb; }
.top-stat-card__icon.purple { background: rgba(139, 92, 246, 0.15); color: #7c3aed; }

.top-stat-card__body {
    flex: 1;
    min-width: 0;
}

.top-stat-card__label {
    font-size: 12px;
    font-weight: 600;
    color: var(--fg-3);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
}

.top-stat-card__value {
    font-size: 24px;
    font-weight: 800;
    color: var(--fg-1);
    line-height: 1.2;
}

.top-stat-card__meta {
    font-size: 12px;
    color: var(--fg-3);
    font-weight: 500;
    margin-top: 2px;
}

/* AI Analysis Blocks */
.ai-block {
    background: var(--grid);
    border: 1px solid var(--br);
    border-radius: 12px;
    padding: 20px;
}

.ai-block h6 {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 12px;
    color: var(--fg-1);
}

.ai-block ul, .ai-block ol {
    margin: 0;
    padding-left: 20px;
    color: var(--fg-2);
}

.ai-block ul li, .ai-block ol li {
    margin-bottom: 6px;
    font-size: 14px;
    line-height: 1.5;
}

/* File Items */
.file-item {
    background: var(--panel);
    border: 1px solid var(--br);
    border-radius: 12px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.2s ease;
}

.file-item:hover {
    border-color: var(--accent);
    background: var(--grid);
}

.file-item__icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(229, 39, 22, 0.1);
    color: var(--accent);
    border-radius: 10px;
    font-size: 22px;
    flex-shrink: 0;
}

.file-item__info {
    flex: 1;
    margin-left: 16px;
    min-width: 0;
}

.file-item__name {
    font-weight: 700;
    font-size: 14px;
    color: var(--fg-1);
    margin-bottom: 4px;
}

.file-item__meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13px;
    color: var(--fg-3);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state__icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--grid);
    border-radius: 50%;
    font-size: 36px;
    color: var(--fg-3);
}

.empty-state__title {
    font-size: 18px;
    font-weight: 700;
    color: var(--fg-1);
    margin-bottom: 8px;
}

.empty-state__text {
    font-size: 14px;
    color: var(--fg-3);
    margin-bottom: 24px;
}

/* Log Items */
.log-item {
    padding: 12px 0;
    border-bottom: 1px solid var(--br);
}

.log-item:last-child {
    border-bottom: none;
}

.log-item__header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.log-item__title {
    font-weight: 600;
    font-size: 13px;
    color: var(--fg-1);
}

.log-item__meta {
    font-size: 12px;
    color: var(--fg-3);
}

/* Candidate Card */
.candidate-header {
    background: linear-gradient(135deg, var(--panel) 0%, var(--grid) 100%);
    border: 1px solid var(--br);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
}

.candidate-profile {
    display: flex;
    align-items: center;
    gap: 20px;
}

.candidate-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid var(--panel);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
}

.candidate-details {
    flex: 1;
    min-width: 0;
}

.candidate-name {
    font-size: 24px;
    font-weight: 800;
    color: var(--fg-1);
    margin-bottom: 8px;
}

.candidate-contact {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.candidate-contact__item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--fg-2);
    font-weight: 500;
}

.candidate-contact__item i {
    color: var(--accent);
}

.candidate-status {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Test Questions */
.test-questions-list {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid var(--br);
    border-radius: 12px;
}

.test-question-item {
    padding: 16px 20px;
    border-bottom: 1px solid var(--br);
}

.test-question-item:last-child {
    border-bottom: none;
}

.test-question-item.correct {
    background: rgba(34, 197, 94, 0.05);
}

.test-question-item.incorrect {
    background: rgba(239, 68, 68, 0.05);
}

.test-question-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.test-question-num {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 700;
    font-size: 14px;
    flex-shrink: 0;
}

.test-question-num.correct {
    background: rgba(34, 197, 94, 0.2);
    color: var(--good);
}

.test-question-num.incorrect {
    background: rgba(239, 68, 68, 0.2);
    color: var(--error);
}

.test-question-text {
    flex: 1;
    margin-left: 12px;
    font-weight: 600;
    font-size: 14px;
    color: var(--fg-1);
}

.test-question-difficulty {
    font-size: 11px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 12px;
    background: var(--grid);
}

.test-question-answers {
    margin-left: 44px;
    font-size: 13px;
}

/* Responsive */
@media (max-width: 1200px) {
    .top-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .top-stats {
        grid-template-columns: 1fr;
    }

    .candidate-profile {
        flex-direction: column;
        text-align: center;
    }

    .candidate-contact {
        justify-content: center;
    }

    .candidate-status {
        justify-content: center;
        margin-top: 16px;
    }
}
</style>
@endpush

@section('content')
<!-- Top Stats Row -->
<div class="top-stats">
    <!-- Match Score -->
    <div class="top-stat-card">
        @php
            $scoreColor = $application->match_score >= 60 ? 'green' : ($application->match_score >= 40 ? 'yellow' : 'red');
        @endphp
        <div class="top-stat-card__icon {{ $scoreColor }}">
            <i class="bi bi-star-fill"></i>
        </div>
        <div class="top-stat-card__body">
            <div class="top-stat-card__label">Match Score</div>
            @if($application->match_score !== null)
                <div class="top-stat-card__value">{{ $application->match_score }}%</div>
            @else
                <div class="top-stat-card__value" style="color: var(--fg-3);">-</div>
                <div class="top-stat-card__meta">Ожидает анализа</div>
            @endif
        </div>
    </div>

    <!-- Test Score -->
    <div class="top-stat-card">
        @php
            $test = $application->candidateTest;
            $testColor = 'blue';
            if ($test && $test->status === 'completed') {
                $testColor = $test->score >= 60 ? 'green' : ($test->score >= 40 ? 'yellow' : 'red');
            }
        @endphp
        <div class="top-stat-card__icon {{ $testColor }}">
            <i class="bi bi-clipboard-check"></i>
        </div>
        <div class="top-stat-card__body">
            <div class="top-stat-card__label">Тест</div>
            @if($test && $test->status === 'completed')
                <div class="top-stat-card__value">{{ $test->score }}%</div>
                <div class="top-stat-card__meta">{{ $test->correct_answers }}/{{ $test->total_questions }} правильно</div>
            @elseif($test && $test->status === 'in_progress')
                <div class="top-stat-card__value" style="font-size: 18px;">В процессе</div>
            @elseif($test && $test->status === 'expired')
                <div class="top-stat-card__value" style="font-size: 18px; color: var(--error);">Истёк</div>
            @else
                <div class="top-stat-card__value" style="color: var(--fg-3);">-</div>
                <div class="top-stat-card__meta">Не начат</div>
            @endif
        </div>
    </div>

    <!-- Date -->
    <div class="top-stat-card">
        <div class="top-stat-card__icon blue">
            <i class="bi bi-calendar-event"></i>
        </div>
        <div class="top-stat-card__body">
            <div class="top-stat-card__label">Дата заявки</div>
            <div class="top-stat-card__value">{{ $application->created_at->format('d.m.Y') }}</div>
            <div class="top-stat-card__meta">{{ $application->created_at->diffForHumans() }}</div>
        </div>
    </div>

    <!-- Source -->
    <div class="top-stat-card">
        <div class="top-stat-card__icon purple">
            @php
                $sourceIcon = match($application->source) {
                    'website' => 'globe',
                    'linkedin' => 'linkedin',
                    'hh' => 'briefcase',
                    'referral' => 'people',
                    default => 'diagram-3'
                };
                $sourceFormatted = match($application->source) {
                    'website' => 'Веб-сайт',
                    'linkedin' => 'LinkedIn',
                    'hh' => 'HeadHunter',
                    'referral' => 'Рекомендация',
                    default => ucfirst($application->source ?? 'Сайт')
                };
            @endphp
            <i class="bi bi-{{ $sourceIcon }}"></i>
        </div>
        <div class="top-stat-card__body">
            <div class="top-stat-card__label">Источник</div>
            <div class="top-stat-card__value" style="font-size: 20px;">{{ $sourceFormatted }}</div>
        </div>
    </div>
</div>

<!-- Candidate Header -->
<div class="candidate-header">
    <div class="candidate-profile">
        <img src="{{ $application->candidate->avatar_url }}" class="candidate-avatar" alt="{{ $application->candidate->name }}">
        <div class="candidate-details">
            <h1 class="candidate-name">{{ $application->candidate->name }}</h1>
            <div class="candidate-contact">
                <div class="candidate-contact__item">
                    <i class="bi bi-envelope-fill"></i>
                    <span>{{ $application->candidate->email }}</span>
                </div>
                @if($application->candidate->phone)
                    <div class="candidate-contact__item">
                        <i class="bi bi-telephone-fill"></i>
                        <span>{{ $application->candidate->phone }}</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="candidate-status">
            <span class="badge badge-{{ $application->status->value }}" style="font-size: 14px; padding: 10px 20px; font-weight: 700;">
                {{ $application->status_label }}
            </span>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Vacancy Applied -->
        <div class="card mb-4">
            <div class="card-header">
                <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-briefcase me-2"></i>Вакансия</span>
            </div>
            <div class="card-body">
                <h5 style="margin: 0 0 8px 0; font-weight: 700; font-size: 18px;">
                    <a href="{{ route('admin.vacancies.show', $application->vacancy) }}" style="color: var(--accent); text-decoration: none;">
                        {{ $application->vacancy->title }}
                    </a>
                </h5>
                <div style="display: flex; align-items: center; gap: 16px; color: var(--fg-3); font-size: 14px; font-weight: 500;">
                    @if($application->vacancy->location)
                        <span><i class="bi bi-geo-alt-fill" style="margin-right: 4px;"></i>{{ $application->vacancy->location }}</span>
                    @endif
                    <span><i class="bi bi-clock-fill" style="margin-right: 4px;"></i>{{ $application->vacancy->employment_type_label }}</span>
                </div>
            </div>
        </div>

        <!-- Match Breakdown -->
        @if($matchBreakdown)
            <div class="card mb-4">
                <div class="card-header">
                    <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-bar-chart me-2"></i>Детальный анализ соответствия</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="ai-block">
                                <h6><i class="bi bi-check-circle me-1" style="color: var(--error);"></i>Must-have</h6>
                                <div style="font-size: 32px; font-weight: 800; color: var(--fg-1); margin-bottom: 8px;">{{ $matchBreakdown['must_have']['score'] }}%</div>
                                <small style="color: var(--fg-3); font-weight: 600;">
                                    {{ $matchBreakdown['must_have']['matched'] }} из {{ $matchBreakdown['must_have']['total'] }} навыков
                                </small>
                                @if(!empty($matchBreakdown['must_have']['missing_skills']))
                                    <div style="margin-top: 12px;">
                                        <small style="color: var(--error); font-weight: 700; display: block; margin-bottom: 6px;">Не хватает:</small>
                                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                            @foreach($matchBreakdown['must_have']['missing_skills'] as $skill)
                                                <span class="badge" style="background: rgba(239, 68, 68, 0.1); color: var(--error); font-weight: 600;">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="ai-block">
                                <h6><i class="bi bi-star me-1" style="color: var(--warn);"></i>Nice-to-have</h6>
                                <div style="font-size: 32px; font-weight: 800; color: var(--fg-1); margin-bottom: 8px;">{{ $matchBreakdown['nice_to_have']['score'] }}%</div>
                                <small style="color: var(--fg-3); font-weight: 600;">
                                    {{ $matchBreakdown['nice_to_have']['matched'] }} из {{ $matchBreakdown['nice_to_have']['total'] }} навыков
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="ai-block">
                                <h6><i class="bi bi-briefcase me-1" style="color: var(--info);"></i>Опыт</h6>
                                <div style="font-size: 32px; font-weight: 800; color: var(--fg-1); margin-bottom: 8px;">{{ $matchBreakdown['experience']['score'] }}%</div>
                                <small style="color: var(--fg-3); font-weight: 600;">
                                    {{ $matchBreakdown['experience']['candidate_years'] }} / {{ $matchBreakdown['experience']['required_years'] }} лет опыта
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Test Results -->
        @if($application->candidateTest)
            @php $test = $application->candidateTest; @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-clipboard-check me-2"></i>Результаты теста</span>
                    <span class="badge {{ $test->status === 'completed' ? ($test->score >= 60 ? 'bg-success' : 'bg-warning') : ($test->status === 'expired' ? 'bg-danger' : 'bg-secondary') }}" style="font-size: 13px; padding: 6px 14px;">
                        @if($test->status === 'completed')
                            {{ $test->score }}%
                        @elseif($test->status === 'expired')
                            Время истекло
                        @elseif($test->status === 'in_progress')
                            В процессе
                        @else
                            Не начат
                        @endif
                    </span>
                </div>
                <div class="card-body">
                    @if($test->status === 'completed')
                        <!-- Score Overview -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="ai-block text-center">
                                    <div style="font-size: 36px; font-weight: 800; color: {{ $test->score >= 60 ? 'var(--good)' : ($test->score >= 40 ? 'var(--warn)' : 'var(--error)') }};">
                                        {{ $test->score }}%
                                    </div>
                                    <small style="color: var(--fg-3); font-weight: 600;">Результат</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-block text-center">
                                    <div style="font-size: 28px; font-weight: 800; color: var(--fg-1);">{{ $test->correct_answers }}/{{ $test->total_questions }}</div>
                                    <small style="color: var(--fg-3); font-weight: 600;">Правильно</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-block text-center">
                                    <div style="font-size: 28px; font-weight: 800; color: var(--fg-1);">{{ floor($test->time_spent / 60) }}:{{ str_pad($test->time_spent % 60, 2, '0', STR_PAD_LEFT) }}</div>
                                    <small style="color: var(--fg-3); font-weight: 600;">Затрачено</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ai-block text-center">
                                    <div style="font-size: 20px; font-weight: 800; color: var(--fg-1);">{{ $test->completed_at?->format('d.m.Y') }}</div>
                                    <small style="color: var(--fg-3); font-weight: 600;">Дата</small>
                                </div>
                            </div>
                        </div>

                        <!-- Difficulty Breakdown -->
                        @php
                            $byDifficulty = ['easy' => ['correct' => 0, 'total' => 0], 'medium' => ['correct' => 0, 'total' => 0], 'hard' => ['correct' => 0, 'total' => 0]];
                            foreach ($test->questions as $q) {
                                $diff = $q['difficulty'] ?? 'medium';
                                $byDifficulty[$diff]['total']++;
                                if (isset($q['user_answer']) && $q['user_answer'] === $q['correct_answer']) {
                                    $byDifficulty[$diff]['correct']++;
                                }
                            }
                        @endphp
                        <h6 style="margin: 0 0 16px 0; font-weight: 700; font-size: 15px; color: var(--fg-1);">
                            <i class="bi bi-bar-chart me-2" style="color: var(--accent);"></i>Результаты по сложности
                        </h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="ai-block">
                                    <h6><i class="bi bi-emoji-smile me-1" style="color: var(--good);"></i>Лёгкие</h6>
                                    <div style="font-size: 28px; font-weight: 800; color: var(--fg-1); margin-bottom: 4px;">
                                        {{ $byDifficulty['easy']['total'] > 0 ? round(($byDifficulty['easy']['correct'] / $byDifficulty['easy']['total']) * 100) : 0 }}%
                                    </div>
                                    <small style="color: var(--fg-3); font-weight: 600;">
                                        {{ $byDifficulty['easy']['correct'] }} из {{ $byDifficulty['easy']['total'] }} вопросов
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ai-block">
                                    <h6><i class="bi bi-emoji-neutral me-1" style="color: var(--warn);"></i>Средние</h6>
                                    <div style="font-size: 28px; font-weight: 800; color: var(--fg-1); margin-bottom: 4px;">
                                        {{ $byDifficulty['medium']['total'] > 0 ? round(($byDifficulty['medium']['correct'] / $byDifficulty['medium']['total']) * 100) : 0 }}%
                                    </div>
                                    <small style="color: var(--fg-3); font-weight: 600;">
                                        {{ $byDifficulty['medium']['correct'] }} из {{ $byDifficulty['medium']['total'] }} вопросов
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="ai-block">
                                    <h6><i class="bi bi-emoji-frown me-1" style="color: var(--error);"></i>Сложные</h6>
                                    <div style="font-size: 28px; font-weight: 800; color: var(--fg-1); margin-bottom: 4px;">
                                        {{ $byDifficulty['hard']['total'] > 0 ? round(($byDifficulty['hard']['correct'] / $byDifficulty['hard']['total']) * 100) : 0 }}%
                                    </div>
                                    <small style="color: var(--fg-3); font-weight: 600;">
                                        {{ $byDifficulty['hard']['correct'] }} из {{ $byDifficulty['hard']['total'] }} вопросов
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Questions Detail - Visible for all admins -->
                        <h6 style="margin: 0 0 16px 0; font-weight: 700; font-size: 15px; color: var(--fg-1);">
                            <i class="bi bi-list-check me-2" style="color: var(--accent);"></i>Детали ответов
                        </h6>
                        <div class="test-questions-list">
                            @foreach($test->questions as $index => $question)
                                @php
                                    $isCorrect = isset($question['user_answer']) && $question['user_answer'] === $question['correct_answer'];
                                    $userAnswer = $question['user_answer'] ?? null;
                                    $diffColor = match($question['difficulty'] ?? 'medium') {
                                        'easy' => 'var(--good)',
                                        'medium' => 'var(--warn)',
                                        'hard' => 'var(--error)',
                                        default => 'var(--fg-3)'
                                    };
                                    $diffLabel = match($question['difficulty'] ?? 'medium') {
                                        'easy' => 'Лёгкий',
                                        'medium' => 'Средний',
                                        'hard' => 'Сложный',
                                        default => 'Средний'
                                    };
                                @endphp
                                <div class="test-question-item {{ $isCorrect ? 'correct' : 'incorrect' }}">
                                    <div class="test-question-header">
                                        <div style="display: flex; align-items: flex-start;">
                                            <span class="test-question-num {{ $isCorrect ? 'correct' : 'incorrect' }}">
                                                {{ $index + 1 }}
                                            </span>
                                            <span class="test-question-text">{{ $question['question'] }}</span>
                                        </div>
                                        <span class="test-question-difficulty" style="color: {{ $diffColor }};">
                                            {{ $diffLabel }}
                                        </span>
                                    </div>
                                    <div class="test-question-answers">
                                        <!-- All answer options -->
                                        <div style="margin-bottom: 10px;">
                                            @foreach($question['options'] as $optIndex => $option)
                                                @php
                                                    $isUserAnswer = $userAnswer === $optIndex;
                                                    $isCorrectAnswer = $question['correct_answer'] === $optIndex;
                                                    $optionStyle = '';
                                                    $optionIcon = '';

                                                    if ($isCorrectAnswer) {
                                                        $optionStyle = 'background: rgba(34, 197, 94, 0.1); border-color: var(--good); color: var(--good);';
                                                        $optionIcon = 'check-circle-fill';
                                                    } elseif ($isUserAnswer && !$isCorrect) {
                                                        $optionStyle = 'background: rgba(239, 68, 68, 0.1); border-color: var(--error); color: var(--error);';
                                                        $optionIcon = 'x-circle-fill';
                                                    }
                                                @endphp
                                                <div style="padding: 8px 12px; margin-bottom: 6px; border-radius: 8px; border: 1px solid var(--br); {{ $optionStyle }} display: flex; align-items: center; gap: 8px;">
                                                    @if($optionIcon)
                                                        <i class="bi bi-{{ $optionIcon }}"></i>
                                                    @else
                                                        <span style="width: 16px;"></span>
                                                    @endif
                                                    <span style="font-weight: 500;">{{ chr(65 + $optIndex) }}.</span>
                                                    <span>{{ $option }}</span>
                                                    @if($isUserAnswer)
                                                        <span class="badge" style="margin-left: auto; background: var(--grid); color: var(--fg-2); font-size: 10px;">Ответ кандидата</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        @if($userAnswer === null)
                                            <span style="color: var(--fg-3); font-weight: 600; font-size: 12px;">
                                                <i class="bi bi-dash-circle me-1"></i>Кандидат не ответил на этот вопрос
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif($test->status === 'in_progress')
                        <div class="empty-state" style="padding: 40px 20px;">
                            <div class="empty-state__icon" style="background: rgba(59, 130, 246, 0.1);">
                                <i class="bi bi-hourglass-split" style="color: var(--info);"></i>
                            </div>
                            <div class="empty-state__title">Тест в процессе</div>
                            <div class="empty-state__text" style="margin: 0;">
                                Кандидат сейчас проходит тест. Осталось: {{ floor($test->remaining_time / 60) }}:{{ str_pad($test->remaining_time % 60, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>
                    @elseif($test->status === 'expired')
                        <div class="empty-state" style="padding: 40px 20px;">
                            <div class="empty-state__icon" style="background: rgba(239, 68, 68, 0.1);">
                                <i class="bi bi-clock" style="color: var(--error);"></i>
                            </div>
                            <div class="empty-state__title">Время теста истекло</div>
                            <div class="empty-state__text" style="margin: 0;">
                                Кандидат не успел завершить тест в отведённое время
                            </div>
                        </div>
                    @else
                        <div class="empty-state" style="padding: 40px 20px;">
                            <div class="empty-state__icon">
                                <i class="bi bi-clipboard"></i>
                            </div>
                            <div class="empty-state__title">Тест ещё не начат</div>
                            <div class="empty-state__text" style="margin: 0;">
                                Кандидат пока не приступил к тестированию
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- AI Analysis -->
        @if($application->analysis)
            <div class="card mb-4">
                <div class="card-header">
                    <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-robot me-2"></i>AI-анализ кандидата</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Strengths -->
                        <div class="col-md-6">
                            <div class="ai-block h-100">
                                <h6><i class="bi bi-hand-thumbs-up me-1" style="color: var(--good);"></i>Сильные стороны</h6>
                                @if($application->analysis->strengths)
                                    <ul class="mb-0">
                                        @foreach($application->analysis->strengths as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p style="color: var(--fg-3); margin: 0;">Не определены</p>
                                @endif
                            </div>
                        </div>

                        <!-- Weaknesses -->
                        <div class="col-md-6">
                            <div class="ai-block h-100">
                                <h6><i class="bi bi-hand-thumbs-down me-1" style="color: var(--warn);"></i>Слабые стороны</h6>
                                @if($application->analysis->weaknesses)
                                    <ul class="mb-0">
                                        @foreach($application->analysis->weaknesses as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p style="color: var(--fg-3); margin: 0;">Не определены</p>
                                @endif
                            </div>
                        </div>

                        <!-- Risks -->
                        <div class="col-md-6">
                            <div class="ai-block h-100">
                                <h6><i class="bi bi-exclamation-triangle me-1" style="color: var(--error);"></i>Риски</h6>
                                @if($application->analysis->risks)
                                    <ul class="mb-0">
                                        @foreach($application->analysis->risks as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p style="color: var(--fg-3); margin: 0;">Не определены</p>
                                @endif
                            </div>
                        </div>

                        <!-- Questions -->
                        <div class="col-md-6">
                            <div class="ai-block h-100">
                                <h6><i class="bi bi-chat-quote me-1" style="color: var(--info);"></i>Вопросы для интервью</h6>
                                @if($application->analysis->suggested_questions)
                                    <ol class="mb-0">
                                        @foreach($application->analysis->suggested_questions as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ol>
                                @else
                                    <p style="color: var(--fg-3); margin: 0;">Не определены</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Recommendation -->
                    @if($application->analysis->recommendation)
                        <div class="ai-block" style="margin-top: 16px;">
                            <h6><i class="bi bi-lightbulb me-1" style="color: var(--accent);"></i>Рекомендация AI</h6>
                            <p style="margin: 0; color: var(--fg-2); line-height: 1.6;">{{ $application->analysis->recommendation }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card mb-4">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="empty-state__icon">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="empty-state__title">AI-анализ ещё не выполнен</div>
                        <div class="empty-state__text">Запустите анализ для получения детальных рекомендаций и оценки кандидата</div>
                        <form action="{{ route('admin.applications.reanalyze', $application) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-brb" style="padding: 12px 28px; font-weight: 600;">
                                <i class="bi bi-play-fill me-2"></i>Запустить анализ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Parsed Resume Data -->
        @if($application->candidate?->candidateProfile && !$application->candidate->candidateProfile->isEmpty())
            @php $profile = $application->candidate->candidateProfile; @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-file-earmark-text me-2"></i>Данные из резюме</span>
                    @if($profile->last_generated_at)
                        <small style="color: var(--fg-3); font-weight: 500;">Обновлено: {{ $profile->last_generated_at->format('d.m.Y H:i') }}</small>
                    @endif
                </div>
                <div class="card-body">
                    <!-- Position & Experience -->
                    <div class="row g-3 mb-4">
                        @if($profile->position_title)
                            <div class="col-md-6">
                                <div class="ai-block h-100">
                                    <h6><i class="bi bi-briefcase me-1" style="color: var(--accent);"></i>Желаемая должность</h6>
                                    <p style="font-size: 18px; font-weight: 700; color: var(--fg-1); margin: 0;">{{ $profile->position_title }}</p>
                                </div>
                            </div>
                        @endif
                        @if($profile->years_of_experience !== null)
                            <div class="col-md-6">
                                <div class="ai-block h-100">
                                    <h6><i class="bi bi-clock-history me-1" style="color: var(--info);"></i>Опыт работы</h6>
                                    <p style="font-size: 18px; font-weight: 700; color: var(--fg-1); margin: 0 0 8px 0;">
                                        {{ $profile->years_of_experience }}
                                        {{ trans_choice('год|года|лет', $profile->years_of_experience) }}
                                    </p>
                                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                        @if($profile->has_management_experience)
                                            <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; font-weight: 600;">
                                                <i class="bi bi-people me-1"></i>Управленческий опыт
                                            </span>
                                        @endif
                                        @if($profile->has_remote_experience)
                                            <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: var(--info); font-weight: 600;">
                                                <i class="bi bi-house me-1"></i>Удалённая работа
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Skills -->
                    @if(!empty($profile->skills))
                        <div class="mb-4">
                            <h6 style="font-weight: 700; font-size: 14px; color: var(--fg-1); margin-bottom: 12px;">
                                <i class="bi bi-tools me-2" style="color: var(--good);"></i>Навыки ({{ count($profile->skills) }})
                            </h6>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($profile->skills as $skill)
                                    @php
                                        $level = $skill['level'] ?? 'basic';
                                        $badgeStyle = match($level) {
                                            'strong' => 'background: rgba(34, 197, 94, 0.15); color: #16a34a;',
                                            'middle', 'medium' => 'background: rgba(245, 158, 11, 0.15); color: #d97706;',
                                            default => 'background: var(--grid); color: var(--fg-2);'
                                        };
                                        $levelLabel = match($level) {
                                            'strong' => 'Продвинутый',
                                            'middle', 'medium' => 'Средний',
                                            default => 'Базовый'
                                        };
                                    @endphp
                                    <span class="badge" style="{{ $badgeStyle }} font-weight: 600; padding: 6px 12px; font-size: 13px;" title="{{ $levelLabel }}">
                                        {{ $skill['name'] ?? $skill }}
                                    </span>
                                @endforeach
                            </div>
                            <div style="margin-top: 12px; display: flex; gap: 16px; font-size: 12px; color: var(--fg-3);">
                                <span><span style="color: #16a34a; font-weight: 700;">●</span> Продвинутый</span>
                                <span><span style="color: #d97706; font-weight: 700;">●</span> Средний</span>
                                <span><span style="color: var(--fg-3); font-weight: 700;">●</span> Базовый</span>
                            </div>
                        </div>
                    @endif

                    <!-- Languages -->
                    @if(!empty($profile->languages))
                        <div class="mb-4">
                            <h6 style="font-weight: 700; font-size: 14px; color: var(--fg-1); margin-bottom: 12px;">
                                <i class="bi bi-translate me-2" style="color: var(--info);"></i>Языки
                            </h6>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($profile->languages as $language)
                                    @php
                                        $langName = is_array($language) ? ($language['name'] ?? '') : $language;
                                        $langLevel = is_array($language) ? ($language['level'] ?? '') : '';
                                    @endphp
                                    <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: var(--info); font-weight: 600; padding: 6px 12px; font-size: 13px;">
                                        {{ $langName }}
                                        @if($langLevel)
                                            <span style="opacity: 0.7;">({{ $langLevel }})</span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Education -->
                    @if(!empty($profile->education))
                        <div class="mb-4">
                            <h6 style="font-weight: 700; font-size: 14px; color: var(--fg-1); margin-bottom: 12px;">
                                <i class="bi bi-mortarboard me-2" style="color: var(--warn);"></i>Образование
                            </h6>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                @foreach($profile->education as $edu)
                                    <div style="background: var(--grid); border: 1px solid var(--br); border-radius: 10px; padding: 14px 16px;">
                                        <div style="font-weight: 700; font-size: 14px; color: var(--fg-1); margin-bottom: 4px;">
                                            {{ is_array($edu) ? ($edu['degree'] ?? $edu['institution'] ?? 'Образование') : $edu }}
                                        </div>
                                        @if(is_array($edu))
                                            <div style="display: flex; align-items: center; gap: 12px; font-size: 13px; color: var(--fg-3);">
                                                @if(!empty($edu['institution']))
                                                    <span>{{ $edu['institution'] }}</span>
                                                @endif
                                                @if(!empty($edu['year']))
                                                    <span class="badge" style="background: var(--br); color: var(--fg-2); font-weight: 600;">{{ $edu['year'] }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Domains -->
                    @if(!empty($profile->domains))
                        <div class="mb-3">
                            <h6 style="font-weight: 700; font-size: 14px; color: var(--fg-1); margin-bottom: 12px;">
                                <i class="bi bi-building me-2" style="color: var(--fg-3);"></i>Отрасли / Домены
                            </h6>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                @foreach($profile->domains as $domain)
                                    <span class="badge" style="background: var(--grid); color: var(--fg-1); border: 1px solid var(--br); font-weight: 600; padding: 6px 12px; font-size: 13px;">{{ $domain }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Cover Letter -->
        @if($application->cover_letter)
            <div class="card mb-4">
                <div class="card-header">
                    <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-envelope me-2"></i>Сопроводительное письмо</span>
                </div>
                <div class="card-body">
                    <div style="color: var(--fg-2); line-height: 1.7; font-size: 14px;">
                        {!! nl2br(e($application->cover_letter)) !!}
                    </div>
                </div>
            </div>
        @endif

        <!-- Files -->
        <div class="card">
            <div class="card-header">
                <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-paperclip me-2"></i>Прикреплённые файлы</span>
            </div>
            <div class="card-body">
                @if($application->files->count())
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($application->files as $file)
                            <div class="file-item">
                                <div class="file-item__icon">
                                    <i class="bi bi-{{ $file->file_type_icon }}"></i>
                                </div>
                                <div class="file-item__info">
                                    <div class="file-item__name">{{ $file->original_name }}</div>
                                    <div class="file-item__meta">
                                        <span>{{ $file->size_formatted }}</span>
                                        <span>•</span>
                                        <span class="badge" style="background: {{ $file->is_parsed ? 'rgba(34, 197, 94, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; color: {{ $file->is_parsed ? 'var(--good)' : 'var(--warn)' }}; font-weight: 600; padding: 4px 10px;">
                                            {{ $file->is_parsed ? 'Распарсен' : 'Ожидает обработки' }}
                                        </span>
                                    </div>
                                </div>
                                <a href="{{ $file->url }}" target="_blank" class="btn btn-sm" style="background: var(--grid); color: var(--fg-1); border: 1px solid var(--br); font-weight: 600;">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state" style="padding: 40px 20px;">
                        <div class="empty-state__icon" style="width: 64px; height: 64px; font-size: 28px; margin-bottom: 16px;">
                            <i class="bi bi-file-earmark-x"></i>
                        </div>
                        <div class="empty-state__text" style="margin: 0;">Нет прикреплённых файлов</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Change Status -->
        <div class="card mb-4">
            <div class="card-header">
                <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-arrow-repeat me-2"></i>Изменить статус</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.applications.status', $application) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label style="font-weight: 600; font-size: 13px; color: var(--fg-2); margin-bottom: 8px; display: block;">Статус заявки</label>
                        <select name="status" class="form-select" style="font-weight: 600;">
                            @foreach($statuses as $status)
                                <option value="{{ $status->value }}" {{ $application->status == $status ? 'selected' : '' }}>
                                    {{ $status->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label style="font-weight: 600; font-size: 13px; color: var(--fg-2); margin-bottom: 8px; display: block;">Заметки (видны только HR)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Добавьте заметку..." style="resize: vertical;">{{ $application->notes }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-brb w-100" style="font-weight: 600; padding: 12px;">
                        <i class="bi bi-check-lg me-1"></i>Сохранить изменения
                    </button>
                </form>
            </div>
        </div>

        <!-- Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <span style="font-weight: 700; color: var(--fg-1);">Действия</span>
            </div>
            <div class="card-body" style="display: flex; flex-direction: column; gap: 10px;">
                <form action="{{ route('admin.applications.reanalyze', $application) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100" style="font-weight: 600; border-width: 2px;">
                        <i class="bi bi-robot me-1"></i>Перезапустить AI-анализ
                    </button>
                </form>
                <a href="{{ route('admin.candidates.show', $application->candidate) }}" class="btn btn-outline-secondary w-100" style="font-weight: 600; border-width: 2px;">
                    <i class="bi bi-person me-1"></i>Профиль кандидата
                </a>
                @if(in_array($application->status->value, ['invited', 'hired']))
                <a href="{{ route('admin.chat.show', $application) }}" class="btn btn-outline-success w-100" style="font-weight: 600; border-width: 2px;">
                    <i class="bi bi-chat-dots me-1"></i>Открыть чат
                </a>
                @elseif(!in_array($application->status->value, ['rejected', 'withdrawn']))
                <button type="button" class="btn btn-outline-success w-100" style="font-weight: 600; border-width: 2px;" onclick="inviteToChat()">
                    <i class="bi bi-chat-dots me-1"></i>Пригласить в чат
                </button>
                @endif
                <form action="{{ route('admin.applications.destroy', $application) }}" method="POST" onsubmit="return confirm('Вы уверены, что хотите удалить эту заявку? Это действие нельзя отменить.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100" style="font-weight: 600; border-width: 2px;">
                        <i class="bi bi-trash me-1"></i>Удалить заявку
                    </button>
                </form>
            </div>
        </div>

        <!-- AI Logs -->
        @if($application->aiLogs->count())
            <div class="card">
                <div class="card-header">
                    <span style="font-weight: 700; color: var(--fg-1);"><i class="bi bi-journal-text me-2"></i>Логи AI</span>
                </div>
                <div class="card-body" style="padding: 16px 20px;">
                    @foreach($application->aiLogs as $log)
                        <div class="log-item">
                            <div class="log-item__header">
                                <span class="log-item__title">{{ $log->operation_label }}</span>
                                <span class="badge {{ $log->status_bg_class }}" style="font-weight: 600;">{{ $log->status_label }}</span>
                            </div>
                            <div class="log-item__meta">
                                {{ $log->created_at->format('d.m.Y H:i') }}
                                @if($log->duration_ms)
                                    • {{ $log->duration_formatted }}
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Invite Modal -->
<div class="modal fade" id="inviteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-chat-dots-fill text-success me-2"></i>
                    Приглашение в чат
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Приветственное сообщение</label>
                    <textarea id="welcomeMessage" class="form-control" rows="4">Здравствуйте! Мы рассмотрели вашу заявку и хотели бы пригласить вас на собеседование. Пожалуйста, напишите нам удобное для вас время.</textarea>
                </div>
                <div class="alert alert-info small mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Кандидат получит SMS-уведомление о приглашении
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-success" onclick="confirmInvite()">
                    <i class="bi bi-send-fill me-1"></i> Отправить приглашение
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let inviteModal;

    document.addEventListener('DOMContentLoaded', function() {
        inviteModal = new bootstrap.Modal(document.getElementById('inviteModal'));
    });

    function inviteToChat() {
        inviteModal.show();
    }

    function confirmInvite() {
        const message = document.getElementById('welcomeMessage').value;

        fetch('/admin/qualified-candidates/{{ $application->id }}/invite', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                inviteModal.hide();
                window.location.href = data.chat_url;
            } else {
                alert(data.message || 'Произошла ошибка');
            }
        })
        .catch(error => {
            alert('Произошла ошибка');
            console.error(error);
        });
    }
</script>
@endpush
