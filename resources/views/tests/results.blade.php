@extends('layouts.app')

@section('title', 'Результаты теста')

@push('styles')
<style>
/* Results Page */
.results-page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 40px 16px;
}

.results-container {
    max-width: 720px;
    margin: 0 auto;
}

/* Score Circle */
.score-hero {
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    border-radius: 24px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.score-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--score-color), var(--score-color-light));
}

.score-circle {
    width: 140px;
    height: 140px;
    margin: 0 auto 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    background: linear-gradient(135deg, var(--score-bg) 0%, var(--score-bg-light) 100%);
    border: 4px solid var(--score-color);
    box-shadow: 0 8px 32px var(--score-shadow);
}

.score-circle__value {
    font-size: 42px;
    font-weight: 800;
    color: var(--score-color);
    line-height: 1;
}

.score-circle__percent {
    font-size: 20px;
    font-weight: 600;
}

.score-hero__title {
    font-size: 28px;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 8px 0;
}

.score-hero__vacancy {
    font-size: 16px;
    color: #6c757d;
    margin: 0;
    font-weight: 500;
}

/* Score colors */
.score-excellent {
    --score-color: #10b981;
    --score-color-light: #34d399;
    --score-bg: rgba(16, 185, 129, 0.1);
    --score-bg-light: rgba(16, 185, 129, 0.05);
    --score-shadow: rgba(16, 185, 129, 0.25);
}

.score-good {
    --score-color: #f59e0b;
    --score-color-light: #fbbf24;
    --score-bg: rgba(245, 158, 11, 0.1);
    --score-bg-light: rgba(245, 158, 11, 0.05);
    --score-shadow: rgba(245, 158, 11, 0.25);
}

.score-average {
    --score-color: #f97316;
    --score-color-light: #fb923c;
    --score-bg: rgba(249, 115, 22, 0.1);
    --score-bg-light: rgba(249, 115, 22, 0.05);
    --score-shadow: rgba(249, 115, 22, 0.25);
}

.score-low {
    --score-color: #ef4444;
    --score-color-light: #f87171;
    --score-bg: rgba(239, 68, 68, 0.1);
    --score-bg-light: rgba(239, 68, 68, 0.05);
    --score-shadow: rgba(239, 68, 68, 0.25);
}

/* Result Cards */
.result-card {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
    margin-bottom: 24px;
    overflow: hidden;
}

.result-card__header {
    padding: 20px 24px;
    border-bottom: 1px solid #f0f0f0;
}

.result-card__title {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.result-card__title i {
    color: #d6001c;
}

.result-card__body {
    padding: 24px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}

@media (max-width: 640px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.stat-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e9ecef;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    transition: all 0.2s ease;
}

.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.stat-box__value {
    font-size: 28px;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1.2;
}

.stat-box__label {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
    margin-top: 4px;
}

/* Interpretation Box */
.interpretation-box {
    padding: 20px;
    border-radius: 16px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 24px;
}

.interpretation-box.excellent {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
    border: 1px solid rgba(16, 185, 129, 0.2);
}

.interpretation-box.good {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
    border: 1px solid rgba(245, 158, 11, 0.2);
}

.interpretation-box.average {
    background: linear-gradient(135deg, rgba(249, 115, 22, 0.1) 0%, rgba(249, 115, 22, 0.05) 100%);
    border: 1px solid rgba(249, 115, 22, 0.2);
}

.interpretation-box.low {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(239, 68, 68, 0.05) 100%);
    border: 1px solid rgba(239, 68, 68, 0.2);
}

.interpretation-box__icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.interpretation-box.excellent .interpretation-box__icon {
    background: rgba(16, 185, 129, 0.2);
    color: #059669;
}

.interpretation-box.good .interpretation-box__icon {
    background: rgba(245, 158, 11, 0.2);
    color: #d97706;
}

.interpretation-box.average .interpretation-box__icon {
    background: rgba(249, 115, 22, 0.2);
    color: #ea580c;
}

.interpretation-box.low .interpretation-box__icon {
    background: rgba(239, 68, 68, 0.2);
    color: #dc2626;
}

.interpretation-box__content h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 6px 0;
}

.interpretation-box.excellent .interpretation-box__content h3 { color: #059669; }
.interpretation-box.good .interpretation-box__content h3 { color: #d97706; }
.interpretation-box.average .interpretation-box__content h3 { color: #ea580c; }
.interpretation-box.low .interpretation-box__content h3 { color: #dc2626; }

.interpretation-box__content p {
    font-size: 14px;
    margin: 0;
    line-height: 1.5;
}

.interpretation-box.excellent .interpretation-box__content p { color: #047857; }
.interpretation-box.good .interpretation-box__content p { color: #b45309; }
.interpretation-box.average .interpretation-box__content p { color: #c2410c; }
.interpretation-box.low .interpretation-box__content p { color: #b91c1c; }

/* Difficulty Progress */
.difficulty-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid #f0f0f0;
}

.difficulty-item:last-child {
    border-bottom: none;
}

.difficulty-item__label {
    width: 90px;
    font-size: 14px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.difficulty-item__label i {
    font-size: 16px;
}

.difficulty-item__label.easy { color: #10b981; }
.difficulty-item__label.medium { color: #f59e0b; }
.difficulty-item__label.hard { color: #ef4444; }

.difficulty-item__bar {
    flex: 1;
    height: 10px;
    background: #e9ecef;
    border-radius: 5px;
    overflow: hidden;
}

.difficulty-item__progress {
    height: 100%;
    border-radius: 5px;
    transition: width 0.5s ease;
}

.difficulty-item__progress.easy { background: linear-gradient(90deg, #10b981, #34d399); }
.difficulty-item__progress.medium { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.difficulty-item__progress.hard { background: linear-gradient(90deg, #ef4444, #f87171); }

.difficulty-item__score {
    width: 60px;
    text-align: right;
    font-size: 14px;
    font-weight: 700;
    color: #1a1a2e;
}

/* Next Steps */
.next-step {
    display: flex;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #f0f0f0;
}

.next-step:last-child {
    border-bottom: none;
}

.next-step__number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: linear-gradient(135deg, #d6001c, #b8001a);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
}

.next-step__text {
    font-size: 15px;
    color: #495057;
    line-height: 1.6;
    padding-top: 4px;
}

/* Action Buttons */
.actions-row {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 28px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-action.primary {
    background: linear-gradient(135deg, #d6001c, #b8001a);
    color: white;
    box-shadow: 0 4px 16px rgba(214, 0, 28, 0.3);
}

.btn-action.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(214, 0, 28, 0.4);
}

.btn-action.secondary {
    background: white;
    color: #495057;
    border: 2px solid #e9ecef;
}

.btn-action.secondary:hover {
    border-color: #d6001c;
    color: #d6001c;
}

/* Screenshot Protection */
.screenshot-protected {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    -webkit-touch-callout: none;
}

/* Blur when not focused (screen recording protection) */
@media screen {
    .blur-on-unfocus {
        transition: filter 0.3s ease;
    }

    .blur-on-unfocus.blurred {
        filter: blur(20px);
    }
}

/* Print protection */
@media print {
    .results-page {
        display: none !important;
    }
}
</style>
@endpush

@section('content')
@php
    $scoreClass = $test->score >= 80 ? 'excellent' : ($test->score >= 60 ? 'good' : ($test->score >= 40 ? 'average' : 'low'));
@endphp

<div class="results-page screenshot-protected blur-on-unfocus" id="resultsPage">
    <div class="results-container">
        <!-- Score Hero -->
        <div class="score-hero score-{{ $scoreClass }}">
            <div class="score-circle">
                <span class="score-circle__value">{{ $test->score }}<span class="score-circle__percent">%</span></span>
            </div>
            <h1 class="score-hero__title">Тест завершён!</h1>
            <p class="score-hero__vacancy">{{ $application->vacancy?->title ?? 'Вакансия удалена' }}</p>
        </div>

        <!-- Results Card -->
        <div class="result-card">
            <div class="result-card__header">
                <h2 class="result-card__title">
                    <i class="bi bi-bar-chart-fill"></i>
                    Ваши результаты
                </h2>
            </div>
            <div class="result-card__body">
                <!-- Stats Grid -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-box">
                        <div class="stat-box__value">{{ $test->correct_answers }}</div>
                        <div class="stat-box__label">Правильных</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box__value">{{ $test->total_questions }}</div>
                        <div class="stat-box__label">Всего</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box__value">{{ $test->score }}%</div>
                        <div class="stat-box__label">Результат</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-box__value">{{ floor($test->time_spent / 60) }}:{{ str_pad($test->time_spent % 60, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="stat-box__label">Время</div>
                    </div>
                </div>

                <!-- Interpretation -->
                <div class="interpretation-box {{ $scoreClass }}">
                    <div class="interpretation-box__icon">
                        @if($test->score >= 80)
                            <i class="bi bi-trophy-fill"></i>
                        @elseif($test->score >= 60)
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                        @elseif($test->score >= 40)
                            <i class="bi bi-exclamation-circle-fill"></i>
                        @else
                            <i class="bi bi-x-circle-fill"></i>
                        @endif
                    </div>
                    <div class="interpretation-box__content">
                        <h3>
                            @if($test->score >= 80)
                                Отличный результат!
                            @elseif($test->score >= 60)
                                Хороший результат!
                            @elseif($test->score >= 40)
                                Удовлетворительно
                            @else
                                Есть над чем работать
                            @endif
                        </h3>
                        <p>
                            @if($test->score >= 80)
                                Вы продемонстрировали высокий уровень знаний и отлично справились с заданиями.
                            @elseif($test->score >= 60)
                                Вы показали достаточный уровень компетенций для данной позиции.
                            @elseif($test->score >= 40)
                                Есть области, требующие дополнительного изучения и практики.
                            @else
                                Рекомендуем более тщательно изучить материал и повторить попытку позже.
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Difficulty Breakdown -->
                <h3 style="font-size: 15px; font-weight: 700; color: #1a1a2e; margin: 0 0 16px 0;">
                    <i class="bi bi-layers-fill" style="color: #d6001c; margin-right: 8px;"></i>
                    По уровню сложности
                </h3>

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

                <div class="difficulty-item">
                    <span class="difficulty-item__label easy">
                        <i class="bi bi-emoji-smile-fill"></i> Лёгкие
                    </span>
                    <div class="difficulty-item__bar">
                        <div class="difficulty-item__progress easy" style="width: {{ $byDifficulty['easy']['total'] > 0 ? ($byDifficulty['easy']['correct'] / $byDifficulty['easy']['total'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="difficulty-item__score">{{ $byDifficulty['easy']['correct'] }}/{{ $byDifficulty['easy']['total'] }}</span>
                </div>

                <div class="difficulty-item">
                    <span class="difficulty-item__label medium">
                        <i class="bi bi-emoji-neutral-fill"></i> Средние
                    </span>
                    <div class="difficulty-item__bar">
                        <div class="difficulty-item__progress medium" style="width: {{ $byDifficulty['medium']['total'] > 0 ? ($byDifficulty['medium']['correct'] / $byDifficulty['medium']['total'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="difficulty-item__score">{{ $byDifficulty['medium']['correct'] }}/{{ $byDifficulty['medium']['total'] }}</span>
                </div>

                <div class="difficulty-item">
                    <span class="difficulty-item__label hard">
                        <i class="bi bi-emoji-frown-fill"></i> Сложные
                    </span>
                    <div class="difficulty-item__bar">
                        <div class="difficulty-item__progress hard" style="width: {{ $byDifficulty['hard']['total'] > 0 ? ($byDifficulty['hard']['correct'] / $byDifficulty['hard']['total'] * 100) : 0 }}%"></div>
                    </div>
                    <span class="difficulty-item__score">{{ $byDifficulty['hard']['correct'] }}/{{ $byDifficulty['hard']['total'] }}</span>
                </div>
            </div>
        </div>

        <!-- What's Next -->
        <div class="result-card">
            <div class="result-card__header">
                <h2 class="result-card__title">
                    <i class="bi bi-arrow-right-circle-fill"></i>
                    Что дальше?
                </h2>
            </div>
            <div class="result-card__body">
                <div class="next-step">
                    <div class="next-step__number">1</div>
                    <p class="next-step__text">Ваша заявка и результаты теста будут рассмотрены HR-специалистом в ближайшее время.</p>
                </div>
                <div class="next-step">
                    <div class="next-step__number">2</div>
                    <p class="next-step__text">Вы получите SMS-уведомление о решении на указанный номер телефона.</p>
                </div>
                <div class="next-step">
                    <div class="next-step__number">3</div>
                    <p class="next-step__text">При положительном решении вам откроется доступ к чату с HR для назначения собеседования.</p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="actions-row">
            <a href="{{ route('profile.applications') }}" class="btn-action secondary">
                <i class="bi bi-clipboard-check"></i>
                Мои заявки
            </a>
            <a href="{{ route('vacant.index') }}" class="btn-action primary">
                <i class="bi bi-briefcase-fill"></i>
                Все вакансии
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultsPage = document.getElementById('resultsPage');

    // Blur on window unfocus (basic screen recording protection)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            resultsPage.classList.add('blurred');
        } else {
            resultsPage.classList.remove('blurred');
        }
    });

    window.addEventListener('blur', function() {
        resultsPage.classList.add('blurred');
    });

    window.addEventListener('focus', function() {
        resultsPage.classList.remove('blurred');
    });

    // Prevent right-click context menu
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        return false;
    });

    // Prevent common screenshot shortcuts
    document.addEventListener('keydown', function(e) {
        // Prevent PrintScreen
        if (e.key === 'PrintScreen') {
            e.preventDefault();
            resultsPage.classList.add('blurred');
            setTimeout(() => resultsPage.classList.remove('blurred'), 1000);
            return false;
        }
        // Prevent Cmd+Shift+3/4 on Mac, Ctrl+Shift+S, etc.
        if ((e.metaKey || e.ctrlKey) && e.shiftKey && ['3', '4', 's', 'S'].includes(e.key)) {
            e.preventDefault();
            return false;
        }
        // Prevent Ctrl+P (print)
        if ((e.metaKey || e.ctrlKey) && e.key === 'p') {
            e.preventDefault();
            return false;
        }
    });

    // Prevent drag and drop of content
    document.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
});
</script>
@endpush
