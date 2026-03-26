@extends('layouts.admin')

@section('title', $meeting->title)
@section('header', 'Детали встречи')

@section('content')
<style>
    .meeting-hero {
        background: linear-gradient(135deg, var(--brb-red) 0%, #c41e0a 100%);
        color: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
    }
    .meeting-hero-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .meeting-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin-top: 16px;
    }
    .hero-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        opacity: 0.9;
    }
    .hero-meta-item i {
        font-size: 1.1rem;
    }

    .info-card {
        background: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .info-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-card-title i {
        color: var(--brb-red);
    }

    .participant-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .participant-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: var(--bg-secondary);
        border-radius: 8px;
    }
    .participant-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .participant-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--brb-red);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
    }
    .participant-name {
        font-weight: 500;
    }
    .participant-role-badge {
        font-size: 0.75rem;
        padding: 2px 8px;
        border-radius: 10px;
        background: rgba(229, 39, 22, 0.1);
        color: var(--brb-red);
    }

    .status-badge {
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .status-scheduled { background: #e3f2fd; color: #1976d2; }
    .status-started { background: #e8f5e9; color: #388e3c; }
    .status-completed { background: #f5f5f5; color: #616161; }
    .status-cancelled { background: #ffebee; color: #d32f2f; }

    .action-buttons {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .meeting-link-box {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: var(--bg-secondary);
        border-radius: 8px;
        margin-top: 16px;
    }
    .meeting-link-box input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 0.9rem;
        color: var(--text-primary);
    }
</style>

<a href="{{ route('admin.meetings.index') }}" class="btn btn-outline-secondary mb-4">
    <i class="fa-solid fa-arrow-left me-2"></i> Назад к списку
</a>

<div class="meeting-hero">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h1 class="meeting-hero-title">{{ $meeting->title }}</h1>
            @if($meeting->description)
                <p class="mb-0 opacity-75">{{ $meeting->description }}</p>
            @endif
        </div>
        <span class="status-badge status-{{ $meeting->status }}">
            {{ $meeting->status_label }}
        </span>
    </div>

    <div class="meeting-hero-meta">
        <div class="hero-meta-item">
            <i class="fa-regular fa-calendar"></i>
            {{ $meeting->scheduled_at->format('d.m.Y') }}
        </div>
        <div class="hero-meta-item">
            <i class="fa-regular fa-clock"></i>
            {{ $meeting->scheduled_at->format('H:i') }} - {{ $meeting->end_time->format('H:i') }}
        </div>
        <div class="hero-meta-item">
            <i class="fa-solid fa-hourglass-half"></i>
            {{ $meeting->duration_minutes }} минут
        </div>
        <div class="hero-meta-item">
            <i class="fa-solid fa-user"></i>
            Организатор: {{ $meeting->createdBy->name }}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        {{-- Участники --}}
        <div class="info-card">
            <div class="info-card-title">
                <i class="fa-solid fa-users"></i>
                Участники ({{ $meeting->participants->count() }})
            </div>

            <div class="participant-list">
                {{-- Организатор --}}
                <div class="participant-row">
                    <div class="participant-info">
                        <div class="participant-avatar">
                            {{ $meeting->createdBy->initials }}
                        </div>
                        <div>
                            <div class="participant-name">{{ $meeting->createdBy->name }}</div>
                            <small class="text-muted">{{ $meeting->createdBy->email }}</small>
                        </div>
                    </div>
                    <span class="participant-role-badge">Организатор</span>
                </div>

                @foreach($meeting->participants as $participant)
                    @if($participant->user_id !== $meeting->created_by)
                        <div class="participant-row">
                            <div class="participant-info">
                                <div class="participant-avatar">
                                    {{ $participant->user->initials }}
                                </div>
                                <div>
                                    <div class="participant-name">{{ $participant->user->name }}</div>
                                    <small class="text-muted">{{ $participant->user->email }}</small>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($participant->status === 'joined')
                                    <span class="badge bg-success">Присоединился</span>
                                @elseif($participant->status === 'accepted')
                                    <span class="badge bg-primary">Принял</span>
                                @elseif($participant->status === 'declined')
                                    <span class="badge bg-danger">Отклонил</span>
                                @elseif($participant->status === 'left')
                                    <span class="badge bg-secondary">Покинул</span>
                                @else
                                    <span class="badge bg-warning text-dark">Приглашен</span>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        @if($meeting->application)
            {{-- Кандидат --}}
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-user-tie"></i>
                    Кандидат
                </div>

                <div class="participant-row">
                    <div class="participant-info">
                        <div class="participant-avatar">
                            {{ $meeting->application->user->initials ?? 'К' }}
                        </div>
                        <div>
                            <div class="participant-name">{{ $meeting->application->user->name ?? 'Кандидат' }}</div>
                            <small class="text-muted">{{ $meeting->application->vacancy->title ?? '' }}</small>
                        </div>
                    </div>
                    <a href="{{ route('admin.applications.show', $meeting->application) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid fa-eye me-1"></i> Заявка
                    </a>
                </div>
            </div>
        @endif

        @if($meeting->started_at || $meeting->ended_at)
            {{-- Статистика --}}
            <div class="info-card">
                <div class="info-card-title">
                    <i class="fa-solid fa-chart-simple"></i>
                    Статистика
                </div>

                <div class="row">
                    @if($meeting->started_at)
                        <div class="col-md-4">
                            <div class="text-muted small">Начало</div>
                            <div class="fw-semibold">{{ $meeting->started_at->format('H:i:s') }}</div>
                        </div>
                    @endif
                    @if($meeting->ended_at)
                        <div class="col-md-4">
                            <div class="text-muted small">Окончание</div>
                            <div class="fw-semibold">{{ $meeting->ended_at->format('H:i:s') }}</div>
                        </div>
                    @endif
                    @if($meeting->duration_minutes && $meeting->ended_at)
                        <div class="col-md-4">
                            <div class="text-muted small">Фактическая длительность</div>
                            <div class="fw-semibold">
                                {{ $meeting->started_at->diffInMinutes($meeting->ended_at) }} мин
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        {{-- Действия --}}
        <div class="info-card">
            <div class="info-card-title">
                <i class="fa-solid fa-bolt"></i>
                Действия
            </div>

            <div class="action-buttons">
                @if($meeting->status === 'scheduled')
                    <a href="{{ route('admin.meetings.room', $meeting) }}" class="btn btn-brb flex-grow-1">
                        <i class="fa-solid fa-video me-2"></i> Начать встречу
                    </a>
                    <a href="{{ route('admin.meetings.edit', $meeting) }}" class="btn btn-outline-secondary flex-grow-1">
                        <i class="fa-solid fa-pen me-2"></i> Редактировать
                    </a>
                    <form action="{{ route('admin.meetings.cancel', $meeting) }}" method="POST" class="w-100"
                          onsubmit="return confirm('Отменить встречу?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="fa-solid fa-xmark me-2"></i> Отменить встречу
                        </button>
                    </form>
                @elseif($meeting->status === 'started')
                    <a href="{{ route('admin.meetings.room', $meeting) }}" class="btn btn-success w-100">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Присоединиться
                    </a>
                @endif
            </div>

            @if($meeting->meeting_link)
                <div class="meeting-link-box">
                    <i class="fa-solid fa-link text-muted"></i>
                    <input type="text" value="{{ $meeting->meeting_link }}" readonly id="meetingLink">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyLink()">
                        <i class="fa-solid fa-copy"></i>
                    </button>
                </div>
            @endif
        </div>

        {{-- Информация --}}
        <div class="info-card">
            <div class="info-card-title">
                <i class="fa-solid fa-info-circle"></i>
                Информация
            </div>

            <dl class="mb-0">
                <dt class="text-muted small">ID встречи</dt>
                <dd class="mb-3">{{ $meeting->room_id ?? 'Не назначен' }}</dd>

                <dt class="text-muted small">Создана</dt>
                <dd class="mb-3">{{ $meeting->created_at->format('d.m.Y H:i') }}</dd>

                <dt class="text-muted small">Обновлена</dt>
                <dd class="mb-0">{{ $meeting->updated_at->format('d.m.Y H:i') }}</dd>
            </dl>
        </div>
    </div>
</div>

<script>
function copyLink() {
    const input = document.getElementById('meetingLink');
    input.select();
    document.execCommand('copy');
    alert('Ссылка скопирована!');
}
</script>
@endsection
