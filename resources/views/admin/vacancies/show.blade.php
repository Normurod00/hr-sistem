@extends('layouts.admin')

@section('title', $vacancy->title)
@section('header', 'Вакансия: ' . Str::limit($vacancy->title, 40))

@section('content')
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Информация о вакансии</span>
                <div>
                    <a href="{{ route('admin.vacancies.edit', $vacancy) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Редактировать
                    </a>
                    <a href="{{ route('vacant.show', $vacancy) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-up-right me-1"></i> На сайте
                    </a>
                </div>
            </div>
            <div class="card-body">
                <h4>{{ $vacancy->title }}</h4>

                <div class="d-flex flex-wrap gap-3 mb-3">
                    @if($vacancy->is_active)
                        <span class="badge bg-success">Активна</span>
                    @else
                        <span class="badge bg-secondary">Неактивна</span>
                    @endif
                    <span class="badge bg-secondary">{{ $vacancy->employment_type_label }}</span>
                    @if($vacancy->location)
                        <span class="text-muted"><i class="bi bi-geo-alt me-1"></i>{{ $vacancy->location }}</span>
                    @endif
                    @if($vacancy->salary_formatted)
                        <span class="text-success"><i class="bi bi-cash me-1"></i>{{ $vacancy->salary_formatted }}</span>
                    @endif
                </div>

                <p class="text-muted">{!! nl2br(e($vacancy->description)) !!}</p>

                @if($vacancy->must_have_skills)
                    <h6 class="mt-4">Обязательные навыки:</h6>
                    @foreach($vacancy->must_have_skills as $skill)
                        <span class="badge bg-danger me-1 mb-1">{{ $skill }}</span>
                    @endforeach
                @endif

                @if($vacancy->nice_to_have_skills)
                    <h6 class="mt-3">Желательные навыки:</h6>
                    @foreach($vacancy->nice_to_have_skills as $skill)
                        <span class="badge bg-warning text-dark me-1 mb-1">{{ $skill }}</span>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Статистика</div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Всего заявок:</span>
                    <strong>{{ $applications->total() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Новых:</span>
                    <strong class="text-primary">{{ $vacancy->new_applications_count }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Создано:</span>
                    <strong>{{ $vacancy->created_at->format('d.m.Y') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Applications -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Заявки на вакансию</span>
        <span class="badge bg-primary">{{ $applications->total() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Кандидат</th>
                    <th>Статус</th>
                    <th class="text-center">Match Score</th>
                    <th>AI-анализ</th>
                    <th>Дата</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $application)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $application->candidate->avatar_url }}" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <div class="fw-semibold">{{ $application->candidate->name }}</div>
                                    <small class="text-muted">{{ $application->candidate->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $application->status->value }}">
                                {{ $application->status_label }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($application->match_score !== null)
                                <span class="match-score {{ $application->match_score >= 60 ? 'high' : ($application->match_score >= 40 ? 'medium' : 'low') }}">
                                    {{ $application->match_score }}%
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($application->analysis)
                                <span class="badge bg-success"><i class="bi bi-check"></i> Готов</span>
                            @else
                                <span class="badge bg-warning text-dark">Ожидает</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $application->created_at->format('d.m.Y H:i') }}</small></td>
                        <td>
                            <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Нет заявок</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $applications->links() }}
</div>
@endsection
