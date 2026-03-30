@extends('layouts.admin')

@section('title', $user->name)
@section('header', 'Профиль: ' . $user->name)

@section('content')
<div class="row">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle mb-3" width="120" height="120">
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                @php
                    $showRoleColors = [
                        'employee' => 'bg-info',
                        'candidate' => 'bg-primary',
                        'hr' => 'bg-success',
                        'admin' => 'bg-danger',
                    ];
                @endphp
                <span class="badge {{ $showRoleColors[$user->role->value] ?? 'bg-secondary' }} fs-6">
                    {{ $user->role->label() }}
                </span>
            </div>
            <div class="card-footer bg-white">
                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-primary w-100">
                    <i class="bi bi-pencil me-1"></i> Редактировать
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Информация</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    <span>Телефон</span>
                    <strong>{{ $user->phone ?? '—' }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Регистрация</span>
                    <strong>{{ $user->created_at->format('d.m.Y') }}</strong>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span>Заявок</span>
                    <strong>{{ $user->applications->count() }}</strong>
                </li>
            </ul>
        </div>
    </div>

    <div class="col-lg-8">
        @if($user->isCandidate())
            <!-- Candidate Profile -->
            @if($user->candidateProfile && !$user->candidateProfile->isEmpty())
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-robot me-2"></i>Профиль из резюме
                    </div>
                    <div class="card-body">
                        @php $profile = $user->candidateProfile; @endphp
                        @if($profile->position_title)
                            <h5 class="text-brb">{{ $profile->position_title }}</h5>
                        @endif
                        @if($profile->years_of_experience)
                            <p class="text-muted"><i class="bi bi-briefcase me-1"></i>Опыт: {{ $profile->years_of_experience }} лет</p>
                        @endif

                        @if($profile->skills)
                            <h6>Навыки:</h6>
                            @foreach($profile->skills as $skill)
                                <span class="badge bg-secondary me-1 mb-1">{{ $skill['name'] ?? $skill }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif

            <!-- Applications -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-file-earmark-person me-2"></i>Заявки кандидата
                </div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Вакансия</th>
                                <th>Статус</th>
                                <th>Match</th>
                                <th>Дата</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->applications as $application)
                                <tr>
                                    <td>{{ Str::limit($application->vacancy->title, 30) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $application->status->value }}">
                                            {{ $application->status->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($application->match_score !== null)
                                            {{ $application->match_score }}%
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $application->created_at->format('d.m.Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">Нет заявок</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            @if($user->employeeProfile)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-person-badge me-2"></i>Профиль сотрудника
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <small class="text-muted d-block">Табельный номер</small>
                                <strong>{{ $user->employeeProfile->employee_number }}</strong>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <small class="text-muted d-block">Статус</small>
                                <span class="badge bg-{{ $user->employeeProfile->status->color() }}">
                                    {{ $user->employeeProfile->status->label() }}
                                </span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <small class="text-muted d-block">Отдел</small>
                                <strong>{{ $user->employeeProfile->department ?? '—' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <small class="text-muted d-block">Должность</small>
                                <strong>{{ $user->employeeProfile->position ?? '—' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <small class="text-muted d-block">Роль сотрудника</small>
                                <strong>{{ $user->employeeProfile->role?->label() ?? '—' }}</strong>
                            </div>
                            @if($user->employeeProfile->manager)
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Руководитель</small>
                                    <strong>{{ $user->employeeProfile->manager->user?->name ?? '—' }}</strong>
                                </div>
                            @endif
                            @if($user->employeeProfile->hire_date)
                                <div class="col-sm-6 mb-3">
                                    <small class="text-muted d-block">Дата найма</small>
                                    <strong>{{ $user->employeeProfile->hire_date->format('d.m.Y') }}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-person-badge text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">{{ $user->role->label() }}</h5>
                        <p class="text-muted">Профиль сотрудника ещё не создан.</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
