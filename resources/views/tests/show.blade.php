@extends('layouts.app')

@section('title', 'Тест — ' . ($application->vacancy?->title ?? 'Вакансия'))

@section('content')
<style>
    .test-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 20px;
    }

    .test-header {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid #e5e5e5;
    }

    .test-title {
        font-size: 24px;
        font-weight: 700;
        color: #222;
        margin-bottom: 8px;
    }

    .test-subtitle {
        color: #666;
        font-size: 15px;
    }

    .timer-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border: 2px solid #e5e5e5;
        border-radius: 12px;
        padding: 16px 24px;
        margin-top: 20px;
    }

    .timer-icon {
        font-size: 28px;
        color: #666;
    }

    .timer-display {
        font-size: 32px;
        font-weight: 700;
        font-family: 'Courier New', monospace;
        color: #333;
    }

    .timer-display.warning {
        color: #f59e0b;
    }

    .timer-display.danger {
        color: #dc2626;
        animation: pulse 1s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }

    .timer-label {
        font-size: 14px;
        color: #888;
    }

    /* Start Screen */
    .start-screen {
        background: #fff;
        border-radius: 16px;
        padding: 48px;
        text-align: center;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid #e5e5e5;
    }

    .start-icon {
        font-size: 64px;
        color: #d6001c;
        margin-bottom: 24px;
    }

    .start-title {
        font-size: 28px;
        font-weight: 700;
        color: #222;
        margin-bottom: 16px;
    }

    .start-description {
        color: #666;
        font-size: 16px;
        line-height: 1.6;
        margin-bottom: 24px;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .start-info {
        display: flex;
        justify-content: center;
        gap: 40px;
        margin-bottom: 32px;
    }

    .start-info-item {
        text-align: center;
    }

    .start-info-value {
        font-size: 32px;
        font-weight: 700;
        color: #d6001c;
    }

    .start-info-label {
        font-size: 14px;
        color: #888;
        margin-top: 4px;
    }

    .btn-start {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 16px 48px;
        background: #d6001c;
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(214, 0, 28, 0.3);
    }

    .btn-start:hover {
        background: #b8001a;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(214, 0, 28, 0.4);
    }

    .btn-start:disabled {
        background: #ccc;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* Test Questions */
    .test-content {
        display: none;
    }

    .test-content.active {
        display: block;
    }

    .progress-bar-container {
        background: #e5e5e5;
        border-radius: 10px;
        height: 8px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #d6001c, #ff4d4d);
        border-radius: 10px;
        transition: width 0.3s ease;
    }

    .question-card {
        background: #fff;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        border: 1px solid #e5e5e5;
    }

    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .question-number {
        font-size: 14px;
        color: #888;
        font-weight: 500;
    }

    .question-difficulty {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .question-difficulty.easy {
        background: #e6f4ea;
        color: #137333;
    }

    .question-difficulty.medium {
        background: #fff8e6;
        color: #8a6d00;
    }

    .question-difficulty.hard {
        background: #fce8e8;
        color: #c5221f;
    }

    .question-text {
        font-size: 20px;
        font-weight: 600;
        color: #222;
        margin-bottom: 24px;
        line-height: 1.5;
    }

    .options-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .option-item {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        background: #f8f9fa;
        border: 2px solid #e5e5e5;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .option-item:hover {
        background: #f0f0f0;
        border-color: #ccc;
    }

    .option-item.selected {
        background: rgba(214, 0, 28, 0.08);
        border-color: #d6001c;
    }

    .option-radio {
        width: 24px;
        height: 24px;
        border: 2px solid #ccc;
        border-radius: 50%;
        margin-right: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
    }

    .option-item.selected .option-radio {
        border-color: #d6001c;
        background: #d6001c;
    }

    .option-item.selected .option-radio::after {
        content: '';
        width: 8px;
        height: 8px;
        background: #fff;
        border-radius: 50%;
    }

    .option-text {
        font-size: 16px;
        color: #333;
    }

    /* Navigation */
    .question-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 24px;
    }

    .btn-nav {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: #f5f5f5;
        color: #333;
        border: 1px solid #ddd;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-nav:hover {
        background: #eee;
    }

    .btn-nav:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .btn-submit {
        padding: 14px 32px;
        background: #d6001c;
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-submit:hover {
        background: #b8001a;
    }

    /* Question dots */
    .question-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .question-dot {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f5f5f5;
        border: 2px solid #ddd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        transition: all 0.2s;
    }

    .question-dot:hover {
        background: #eee;
    }

    .question-dot.current {
        background: #d6001c;
        border-color: #d6001c;
        color: #fff;
    }

    .question-dot.answered {
        background: #e6f4ea;
        border-color: #a8dab5;
        color: #137333;
    }

    /* Loading */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .loading-spinner {
        width: 48px;
        height: 48px;
        border: 4px solid #f0f0f0;
        border-top-color: #d6001c;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    @media (max-width: 768px) {
        .test-container {
            padding: 12px;
        }

        .question-card {
            padding: 20px;
        }

        .start-info {
            flex-direction: column;
            gap: 20px;
        }
    }
</style>

<div class="test-container">
    <!-- Header -->
    <div class="test-header">
        <h1 class="test-title">Тест на должность</h1>
        <p class="test-subtitle">{{ $application->vacancy?->title ?? 'Вакансия удалена' }}</p>

        <div class="timer-container" id="timerContainer" style="display: none;">
            <i class="bi bi-clock timer-icon"></i>
            <div>
                <div class="timer-display" id="timerDisplay">15:00</div>
                <div class="timer-label">Оставшееся время</div>
            </div>
        </div>
    </div>

    <!-- Start Screen -->
    <div class="start-screen" id="startScreen">
        <div class="start-icon">
            <i class="bi bi-clipboard-check"></i>
        </div>
        <h2 class="start-title">Готовы начать тест?</h2>
        <p class="start-description">
            Вам будет предложено {{ $test->total_questions }} вопросов разной сложности.
            После начала у вас будет 15 минут на прохождение теста.
            Результат будет виден HR-менеджеру.
        </p>

        <div class="start-info">
            <div class="start-info-item">
                <div class="start-info-value">{{ $test->total_questions }}</div>
                <div class="start-info-label">вопросов</div>
            </div>
            <div class="start-info-item">
                <div class="start-info-value">15</div>
                <div class="start-info-label">минут</div>
            </div>
        </div>

        <button class="btn-start" id="startBtn" onclick="startTest()">
            <i class="bi bi-play-fill"></i> Начать тест
        </button>
    </div>

    <!-- Test Content -->
    <div class="test-content" id="testContent">
        <!-- Progress -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar" style="width: 0%"></div>
        </div>

        <!-- Question Dots -->
        <div class="question-dots" id="questionDots"></div>

        <!-- Question Card -->
        <div class="question-card">
            <div class="question-header">
                <span class="question-number" id="questionNumber">Вопрос 1 из {{ $test->total_questions }}</span>
                <span class="question-difficulty" id="questionDifficulty">Лёгкий</span>
            </div>

            <div class="question-text" id="questionText"></div>

            <div class="options-list" id="optionsList"></div>

            <div class="question-nav">
                <button class="btn-nav" id="prevBtn" onclick="prevQuestion()" disabled>
                    <i class="bi bi-arrow-left"></i> Назад
                </button>

                <button class="btn-nav" id="nextBtn" onclick="nextQuestion()">
                    Далее <i class="bi bi-arrow-right"></i>
                </button>

                <button class="btn-submit" id="submitBtn" onclick="submitTest()" style="display: none;">
                    <i class="bi bi-check-lg"></i> Завершить тест
                </button>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="loading-spinner"></div>
    </div>
</div>

<script>
    const applicationId = @json($application->id);
    const csrfToken = @json(csrf_token());

    let questions = [];
    let currentQuestionIndex = 0;
    let answers = {};
    let remainingTime = @json($test->remaining_time);
    let timerInterval = null;
    let testStarted = @json($test->status === 'in_progress');

    // If test already started, resume
    if (testStarted) {
        @if($test->status === 'in_progress')
            questions = @json($test->questions);
            // Restore answers if any
            questions.forEach((q, i) => {
                if (q.user_answer !== undefined && q.user_answer !== null) {
                    answers[i] = q.user_answer;
                }
            });
            document.getElementById('startScreen').style.display = 'none';
            document.getElementById('testContent').classList.add('active');
            document.getElementById('timerContainer').style.display = 'flex';
            initQuestionDots();
            showQuestion(0);
            startTimer();
        @endif
    }

    function startTest() {
        const btn = document.getElementById('startBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Загрузка...';

        fetch(`/tests/${applicationId}/start`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                questions = data.questions;
                remainingTime = data.remaining_time;

                document.getElementById('startScreen').style.display = 'none';
                document.getElementById('testContent').classList.add('active');
                document.getElementById('timerContainer').style.display = 'flex';

                initQuestionDots();
                showQuestion(0);
                startTimer();
            } else {
                alert('Ошибка: ' + (data.error || 'Не удалось начать тест'));
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-play-fill"></i> Начать тест';
            }
        })
        .catch(err => {
            console.error('Start test error:', err);
            alert('Не удалось начать тест. Обновите страницу и попробуйте снова.');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-play-fill"></i> Начать тест';
        });
    }

    function initQuestionDots() {
        const container = document.getElementById('questionDots');
        container.innerHTML = '';

        for (let i = 0; i < questions.length; i++) {
            const dot = document.createElement('div');
            dot.className = 'question-dot';
            dot.textContent = i + 1;
            dot.onclick = () => goToQuestion(i);
            container.appendChild(dot);
        }

        updateQuestionDots();
    }

    function updateQuestionDots() {
        const dots = document.querySelectorAll('.question-dot');
        dots.forEach((dot, i) => {
            dot.classList.remove('current', 'answered');
            if (i === currentQuestionIndex) {
                dot.classList.add('current');
            } else if (answers[i] !== undefined) {
                dot.classList.add('answered');
            }
        });
    }

    function showQuestion(index) {
        currentQuestionIndex = index;
        const q = questions[index];

        document.getElementById('questionNumber').textContent = `Вопрос ${index + 1} из ${questions.length}`;

        const diffLabels = { easy: 'Лёгкий', medium: 'Средний', hard: 'Сложный' };
        const diffEl = document.getElementById('questionDifficulty');
        diffEl.textContent = diffLabels[q.difficulty] || q.difficulty;
        diffEl.className = 'question-difficulty ' + q.difficulty;

        document.getElementById('questionText').textContent = q.question;

        const optionsList = document.getElementById('optionsList');
        optionsList.innerHTML = '';

        q.options.forEach((opt, optIndex) => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'option-item' + (answers[index] === optIndex ? ' selected' : '');
            optionDiv.onclick = () => selectOption(index, optIndex);
            const radioDiv = document.createElement('div');
            radioDiv.className = 'option-radio';
            const textDiv = document.createElement('div');
            textDiv.className = 'option-text';
            textDiv.textContent = opt;
            optionDiv.appendChild(radioDiv);
            optionDiv.appendChild(textDiv);
            optionsList.appendChild(optionDiv);
        });

        // Update progress
        const progress = ((index + 1) / questions.length) * 100;
        document.getElementById('progressBar').style.width = progress + '%';

        // Update nav buttons
        document.getElementById('prevBtn').disabled = index === 0;

        if (index === questions.length - 1) {
            document.getElementById('nextBtn').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'inline-flex';
        } else {
            document.getElementById('nextBtn').style.display = 'inline-flex';
            document.getElementById('submitBtn').style.display = 'none';
        }

        updateQuestionDots();
    }

    function selectOption(questionIndex, optionIndex) {
        answers[questionIndex] = optionIndex;

        // Update UI
        const options = document.querySelectorAll('.option-item');
        options.forEach((opt, i) => {
            opt.classList.toggle('selected', i === optionIndex);
        });

        updateQuestionDots();
    }

    function prevQuestion() {
        if (currentQuestionIndex > 0) {
            showQuestion(currentQuestionIndex - 1);
        }
    }

    function nextQuestion() {
        if (currentQuestionIndex < questions.length - 1) {
            showQuestion(currentQuestionIndex + 1);
        }
    }

    function goToQuestion(index) {
        showQuestion(index);
    }

    function startTimer() {
        updateTimerDisplay();

        timerInterval = setInterval(() => {
            remainingTime--;

            if (remainingTime <= 0) {
                clearInterval(timerInterval);
                submitTest(true);
                return;
            }

            updateTimerDisplay();
        }, 1000);
    }

    function updateTimerDisplay() {
        const minutes = Math.floor(remainingTime / 60);
        const seconds = remainingTime % 60;
        const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        const timerEl = document.getElementById('timerDisplay');
        timerEl.textContent = display;

        timerEl.classList.remove('warning', 'danger');
        if (remainingTime <= 60) {
            timerEl.classList.add('danger');
        } else if (remainingTime <= 180) {
            timerEl.classList.add('warning');
        }
    }

    function submitTest(timeExpired = false) {
        if (!timeExpired) {
            const unanswered = questions.length - Object.keys(answers).length;
            if (unanswered > 0) {
                if (!confirm(`Вы не ответили на ${unanswered} вопрос(ов). Завершить тест?`)) {
                    return;
                }
            }
        }

        clearInterval(timerInterval);
        document.getElementById('loadingOverlay').style.display = 'flex';

        fetch(`/tests/${applicationId}/submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ answers })
        })
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = `/tests/${applicationId}/results`;
            } else {
                alert('Ошибка: ' + (data.error || 'Не удалось сохранить результаты'));
                document.getElementById('loadingOverlay').style.display = 'none';
            }
        })
        .catch(err => {
            console.error('Submit test error:', err);
            alert('Не удалось отправить результаты. Обновите страницу и попробуйте снова.');
            document.getElementById('loadingOverlay').style.display = 'none';
        });
    }

    // Prevent accidental page leave
    window.onbeforeunload = function() {
        if (testStarted || Object.keys(answers).length > 0) {
            return 'Вы уверены? Тест не будет сохранён.';
        }
    };
</script>
@endsection
