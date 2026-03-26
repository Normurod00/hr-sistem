@extends('layouts.admin')

@section('title', 'Настройки AI')
@section('header', 'Настройки AI-робота')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- AI Status -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-robot me-2"></i>Статус AI-сервера</span>
                <form action="{{ route('admin.ai.health') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Проверить
                    </button>
                </form>
            </div>
            <div class="card-body">
                @if($aiStatus['status'] === 'online')
                    <div class="d-flex align-items-center">
                        <span class="badge bg-success fs-6 me-3">
                            <i class="bi bi-check-circle me-1"></i> Online
                        </span>
                        <span class="text-muted">AI-сервер работает нормально</span>
                    </div>
                @else
                    <div class="d-flex align-items-center">
                        <span class="badge bg-danger fs-6 me-3">
                            <i class="bi bi-x-circle me-1"></i> Offline
                        </span>
                        <span class="text-danger">{{ $aiStatus['message'] ?? 'AI-сервер недоступен' }}</span>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <strong>Как запустить:</strong> Перейдите в папку <code>ai_server</code> и выполните <code>python run.py</code>
                    </div>
                @endif
            </div>
        </div>

        <!-- Settings Form -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-gear me-2"></i>Настройки поведения
            </div>
            <div class="card-body">
                <form action="{{ route('admin.ai.settings.update') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input type="hidden" name="auto_analyze_on_new_application" value="0">
                            <input class="form-check-input" type="checkbox" id="auto_analyze"
                                   name="auto_analyze_on_new_application" value="1"
                                   {{ ($settings['auto_analyze_on_new_application'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_analyze">
                                <strong>Автоматический анализ новых заявок</strong>
                                <br><small class="text-muted">AI будет автоматически анализировать резюме при подаче заявки</small>
                            </label>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">Генерировать в отчёте:</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="generate_strengths" value="0">
                                <input class="form-check-input" type="checkbox" id="gen_strengths"
                                       name="generate_strengths" value="1"
                                       {{ ($settings['generate_strengths'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="gen_strengths">Сильные стороны</label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="hidden" name="generate_weaknesses" value="0">
                                <input class="form-check-input" type="checkbox" id="gen_weaknesses"
                                       name="generate_weaknesses" value="1"
                                       {{ ($settings['generate_weaknesses'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="gen_weaknesses">Слабые стороны</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mb-3">
                                <input type="hidden" name="generate_risks" value="0">
                                <input class="form-check-input" type="checkbox" id="gen_risks"
                                       name="generate_risks" value="1"
                                       {{ ($settings['generate_risks'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="gen_risks">Риски</label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="hidden" name="generate_questions" value="0">
                                <input class="form-check-input" type="checkbox" id="gen_questions"
                                       name="generate_questions" value="1"
                                       {{ ($settings['generate_questions'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="gen_questions">Вопросы для интервью</label>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <label for="min_score" class="form-label">
                            <strong>Минимальный match score для shortlist</strong>
                        </label>
                        <div class="input-group" style="max-width: 200px;">
                            <input type="number" class="form-control" id="min_score"
                                   name="min_match_score_for_shortlist"
                                   value="{{ $settings['min_match_score_for_shortlist'] ?? '60' }}"
                                   min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Кандидаты с score выше этого значения попадают в shortlist</small>
                    </div>

                    <button type="submit" class="btn btn-brb">
                        <i class="bi bi-check me-1"></i> Сохранить настройки
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Статистика (7 дней)
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего операций:</span>
                    <strong>{{ $stats['total_operations'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Успешных:</span>
                    <strong class="text-success">{{ $stats['successful'] }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ошибок:</span>
                    <strong class="text-danger">{{ $stats['failed'] }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Среднее время:</span>
                    <strong>{{ $stats['avg_duration'] ? round($stats['avg_duration']) . ' мс' : '—' }}</strong>
                </div>
            </div>
            <div class="card-footer bg-white">
                <a href="{{ route('admin.ai.logs') }}" class="text-decoration-none">
                    Посмотреть логи <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Operations breakdown -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-pie-chart me-2"></i>По операциям
            </div>
            <ul class="list-group list-group-flush">
                @forelse($operationStats as $op => $count)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ \App\Models\AiLog::logStart($op, null)->operation_label ?? $op }}</span>
                        <span class="badge bg-primary">{{ $count }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted text-center">Нет данных</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
