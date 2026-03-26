@extends('layouts.admin')

@section('title', 'Кандидаты')
@section('header', 'Кандидаты')

@section('header-actions')
    <!-- Счетчики -->
    <div class="d-flex gap-2">
        @foreach($kanbanColumns as $column)
            <span class="badge bg-secondary">
                {{ $column['title'] }}: {{ $column['candidates']->count() }}
            </span>
        @endforeach
    </div>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Фильтры -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.candidates.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label"><i class="fa-solid fa-search"></i> Поиск</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Имя, email, телефон..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label"><i class="fa-solid fa-briefcase"></i> Вакансия</label>
                    <select name="vacancy_id" class="form-select">
                        <option value="">Все вакансии</option>
                        @foreach($vacancies as $vacancy)
                            <option value="{{ $vacancy->id }}"
                                    {{ request('vacancy_id') == $vacancy->id ? 'selected' : '' }}>
                                {{ $vacancy->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label"><i class="fa-solid fa-filter"></i> Заявки</label>
                    <select name="has_applications" class="form-select">
                        <option value="">Все</option>
                        <option value="yes" {{ request('has_applications') == 'yes' ? 'selected' : '' }}>С заявками</option>
                        <option value="no" {{ request('has_applications') == 'no' ? 'selected' : '' }}>Без заявок</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter"></i> Применить
                    </button>
                    @if(request()->hasAny(['search', 'vacancy_id', 'has_applications']))
                        <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Kanban Board -->
    <div class="kanban-board">
        <div class="row g-3">
            @foreach($kanbanColumns as $columnKey => $column)
                <div class="col-lg-2 col-md-4">
                    <div class="kanban-column">
                        <!-- Column Header -->
                        <div class="kanban-column-header {{ $columnKey }}">
                            <h5 class="mb-0">
                                {{ $column['title'] }}
                                <span class="badge bg-light text-dark ms-2">{{ $column['candidates']->count() }}</span>
                            </h5>
                        </div>

                        <!-- Column Body -->
                        <div class="kanban-column-body">
                            @forelse($column['candidates'] as $candidate)
                                <div class="kanban-card">
                                    <!-- Candidate Info -->
                                    <div class="kanban-card-header">
                                        <h6 class="mb-1">
                                            <a href="{{ route('admin.candidates.show', $candidate) }}"
                                               class="text-decoration-none text-dark fw-bold">
                                                {{ $candidate->name }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="fa-solid fa-envelope"></i>
                                            {{ Str::limit($candidate->email, 20) }}
                                        </small>
                                    </div>

                                    <div class="kanban-card-body">
                                        @php
                                            $latestApplication = $candidate->applications->sortByDesc('created_at')->first();
                                        @endphp

                                        @if($latestApplication)
                                            <!-- Latest Application -->
                                            <div class="mb-2">
                                                <small class="text-muted">Вакансия:</small>
                                                <div class="fw-500">
                                                    <i class="fa-solid fa-briefcase text-primary"></i>
                                                    {{ Str::limit($latestApplication->vacancy->title, 25) }}
                                                </div>
                                            </div>

                                            <!-- Match Score -->
                                            @if($latestApplication->match_score !== null)
                                                <div class="mb-2">
                                                    <small class="text-muted">Match Score:</small>
                                                    <div class="progress" style="height: 20px;">
                                                        @php
                                                            $score = $latestApplication->match_score;
                                                            $color = $score >= 70 ? 'success' : ($score >= 40 ? 'warning' : 'danger');
                                                        @endphp
                                                        <div class="progress-bar bg-{{ $color }}"
                                                             style="width: {{ $score }}%">
                                                            {{ $score }}%
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Application Date -->
                                            <small class="text-muted">
                                                <i class="fa-solid fa-calendar"></i>
                                                {{ $latestApplication->created_at->format('d.m.Y') }}
                                            </small>
                                        @else
                                            <p class="text-muted small mb-0">
                                                <i class="fa-solid fa-info-circle"></i>
                                                Нет заявок
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Card Actions -->
                                    <div class="kanban-card-footer">
                                        <a href="{{ route('admin.candidates.show', $candidate) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-eye"></i> Просмотр
                                        </a>
                                        @if($candidate->applications_count > 1)
                                            <span class="badge bg-info">
                                                +{{ $candidate->applications_count - 1 }} заявок
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4">
                                    <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0 small">Нет кандидатов</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@push('styles')
<style>
/* Kanban Board Styles */
.kanban-board {
    overflow-x: auto;
    padding-bottom: 20px;
}

.kanban-column {
    background: var(--bs-gray-100);
    border-radius: 8px;
    min-height: 500px;
    display: flex;
    flex-direction: column;
}

.kanban-column-header {
    padding: 15px;
    border-bottom: 2px solid var(--bs-gray-300);
    border-radius: 8px 8px 0 0;
}

.kanban-column-header.new {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.kanban-column-header.in_review {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.kanban-column-header.invited {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.kanban-column-header.hired {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.kanban-column-header.rejected {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.kanban-column-header.no_applications {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    color: #333;
}

.kanban-column-body {
    padding: 15px;
    flex: 1;
    overflow-y: auto;
    max-height: calc(100vh - 400px);
}

.kanban-card {
    background: white;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.kanban-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transform: translateY(-2px);
    border-color: var(--bs-primary);
}

.kanban-card-header h6 a:hover {
    color: var(--bs-primary) !important;
}

.kanban-card-body {
    margin: 10px 0;
}

.kanban-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-top: 10px;
    border-top: 1px solid var(--bs-gray-200);
}

.fw-500 {
    font-weight: 500;
}

/* Dark Theme Support */
.theme-dark .kanban-column {
    background: var(--bs-dark);
}

.theme-dark .kanban-card {
    background: var(--bs-gray-800);
    color: var(--bs-gray-200);
}

.theme-dark .kanban-card-header h6 a {
    color: var(--bs-gray-100) !important;
}

.theme-dark .kanban-card-footer {
    border-top-color: var(--bs-gray-700);
}

/* Responsive */
@media (max-width: 992px) {
    .kanban-column {
        min-height: 400px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Auto-refresh kanban board every 30 seconds (optional)
// setInterval(() => {
//     if (!document.querySelector('form input:focus')) {
//         location.reload();
//     }
// }, 30000);
</script>
@endpush
@endsection
