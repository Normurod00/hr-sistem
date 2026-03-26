@extends('layouts.admin')

@section('title', 'Видеовстречи')
@section('header', 'Видеовстречи')

@section('content')
<style>
    /* Page Header */
    .page-intro {
        background: linear-gradient(135deg, var(--brb-red) 0%, #c41e0a 100%);
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .page-intro::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .page-intro-content {
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .page-intro h2 {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 8px;
    }
    .page-intro p {
        opacity: 0.9;
        margin: 0;
    }
    .btn-white {
        background: white;
        color: var(--brb-red);
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-white:hover {
        background: #f5f5f5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Stats Row */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 24px;
    }
    .stat-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .theme-dark .stat-card {
        background: var(--card-bg);
        border-color: var(--border-color);
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .stat-icon.blue { background: rgba(33, 150, 243, 0.1); color: #2196f3; }
    .stat-icon.green { background: rgba(76, 175, 80, 0.1); color: #4caf50; }
    .stat-icon.orange { background: rgba(255, 152, 0, 0.1); color: #ff9800; }
    .stat-icon.gray { background: rgba(158, 158, 158, 0.1); color: #9e9e9e; }
    .stat-info h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        color: #1f2937;
    }
    .theme-dark .stat-info h3 {
        color: var(--text-primary);
    }
    .stat-info span {
        font-size: 0.85rem;
        color: #6b7280;
    }
    .theme-dark .stat-info span {
        color: var(--text-secondary);
    }

    /* Filter & Search */
    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }
    .filter-tabs {
        display: flex;
        gap: 4px;
        background: #f3f4f6;
        padding: 4px;
        border-radius: 10px;
    }
    .theme-dark .filter-tabs {
        background: var(--bg-secondary);
    }
    .filter-tab {
        padding: 10px 20px;
        border-radius: 8px;
        background: transparent;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s;
    }
    .theme-dark .filter-tab {
        color: var(--text-secondary);
    }
    .filter-tab:hover {
        color: #1f2937;
    }
    .theme-dark .filter-tab:hover {
        color: var(--text-primary);
    }
    .filter-tab.active {
        background: white;
        color: var(--brb-red);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .theme-dark .filter-tab.active {
        background: var(--card-bg);
    }

    /* Meeting Cards Grid */
    .meetings-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 20px;
    }

    .meeting-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .theme-dark .meeting-card {
        background: var(--card-bg);
        border-color: var(--border-color);
    }
    .meeting-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        border-color: var(--brb-red);
    }

    .meeting-card-header {
        padding: 20px 20px 0;
        display: flex;
        justify-content: flex-end;
        align-items: flex-start;
    }

    .meeting-status {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-scheduled {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1d4ed8;
    }
    .status-started {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #16a34a;
        animation: pulse 2s infinite;
    }
    .status-completed {
        background: #f3f4f6;
        color: #6b7280;
    }
    .theme-dark .status-completed {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    .status-cancelled {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }

    .meeting-card-body {
        padding: 16px 20px;
    }

    .meeting-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1f2937;
        margin: 0 0 4px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .theme-dark .meeting-title {
        color: var(--text-primary);
    }

    .meeting-candidate {
        font-size: 0.85rem;
        color: #6b7280;
        margin-bottom: 16px;
    }
    .theme-dark .meeting-candidate {
        color: var(--text-secondary);
    }
    .meeting-candidate i {
        color: var(--brb-red);
        margin-right: 6px;
    }

    .meeting-datetime {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 14px 16px;
        background: #f9fafb;
        border-radius: 10px;
        margin-bottom: 16px;
    }
    .theme-dark .meeting-datetime {
        background: var(--bg-secondary);
    }
    .datetime-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .datetime-item i {
        color: var(--brb-red);
        font-size: 1rem;
    }
    .datetime-item .label {
        font-size: 0.7rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .theme-dark .datetime-item .label {
        color: var(--text-secondary);
    }
    .datetime-item .value {
        font-size: 0.9rem;
        font-weight: 600;
        color: #1f2937;
    }
    .theme-dark .datetime-item .value {
        color: var(--text-primary);
    }

    .meeting-participants-section {
        margin-bottom: 16px;
    }
    .participants-label {
        font-size: 0.7rem;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 10px;
    }
    .theme-dark .participants-label {
        color: var(--text-secondary);
    }
    .participants-avatars {
        display: flex;
        align-items: center;
    }
    .participant-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 3px solid white;
        margin-left: -10px;
        background: linear-gradient(135deg, var(--brb-red) 0%, #ff6b5b 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        position: relative;
        transition: all 0.2s;
    }
    .theme-dark .participant-avatar {
        border-color: var(--card-bg);
    }
    .participant-avatar:first-child {
        margin-left: 0;
    }
    .participant-avatar:hover {
        transform: scale(1.1);
        z-index: 10;
    }
    .participant-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }
    .participant-avatar.more {
        background: #e5e7eb;
        color: #6b7280;
        font-weight: 600;
    }
    .theme-dark .participant-avatar.more {
        background: var(--bg-secondary);
        color: var(--text-secondary);
    }
    .participants-count {
        margin-left: 12px;
        font-size: 0.85rem;
        color: #6b7280;
    }
    .theme-dark .participants-count {
        color: var(--text-secondary);
    }

    .meeting-card-footer {
        padding: 16px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 10px;
    }
    .theme-dark .meeting-card-footer {
        border-color: var(--border-color);
    }
    .meeting-card-footer .btn {
        flex: 1;
        padding: 10px 16px;
        font-size: 0.85rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border: 2px dashed #e5e7eb;
        border-radius: 16px;
    }
    .theme-dark .empty-state {
        background: var(--card-bg);
        border-color: var(--border-color);
    }
    .empty-state-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(229, 39, 22, 0.1) 0%, rgba(229, 39, 22, 0.05) 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
    }
    .empty-state-icon i {
        font-size: 2.5rem;
        color: var(--brb-red);
    }
    .empty-state h4 {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 8px;
    }
    .theme-dark .empty-state h4 {
        color: var(--text-primary);
    }
    .empty-state p {
        color: #6b7280;
        margin-bottom: 24px;
        max-width: 400px;
        margin-left: auto;
        margin-right: auto;
    }
    .theme-dark .empty-state p {
        color: var(--text-secondary);
    }

    /* Pagination */
    .pagination-wrapper {
        margin-top: 32px;
        display: flex;
        justify-content: center;
    }
    .pagination {
        display: flex;
        gap: 4px;
    }
    .page-link {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: white;
        border: 1px solid #e5e7eb;
        color: #1f2937;
        text-decoration: none;
        font-weight: 500;
        transition: all 0.2s;
    }
    .theme-dark .page-link {
        background: var(--card-bg);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
    .page-link:hover {
        border-color: var(--brb-red);
        color: var(--brb-red);
    }
    .page-item.active .page-link {
        background: var(--brb-red);
        border-color: var(--brb-red);
        color: white;
    }
    .page-item.disabled .page-link {
        opacity: 0.5;
        pointer-events: none;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 768px) {
        .page-intro-content {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }
        .stats-row {
            grid-template-columns: 1fr;
        }
        .meetings-grid {
            grid-template-columns: 1fr;
        }
        .toolbar {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

{{-- Page Header --}}
<div class="page-intro">
    <div class="page-intro-content">
        <div>
            <h2><i class="fa-solid fa-video me-2"></i> Видеовстречи</h2>
            <p>Проводите собеседования и встречи с кандидатами онлайн</p>
        </div>
        <a href="{{ route('admin.meetings.create') }}" class="btn-white">
            <i class="fa-solid fa-plus"></i>
            Создать встречу
        </a>
    </div>
</div>

{{-- Stats --}}
@php
    $totalMeetings = \App\Models\VideoMeeting::count();
    $scheduledCount = \App\Models\VideoMeeting::where('status', 'scheduled')->count();
    $activeCount = \App\Models\VideoMeeting::where('status', 'started')->count();
    $completedCount = \App\Models\VideoMeeting::where('status', 'completed')->count();
@endphp

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $scheduledCount }}</h3>
            <span>Запланировано</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="fa-solid fa-circle-play"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $activeCount }}</h3>
            <span>Активные</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="fa-solid fa-check-circle"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $completedCount }}</h3>
            <span>Завершено</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon gray">
            <i class="fa-solid fa-video"></i>
        </div>
        <div class="stat-info">
            <h3>{{ $totalMeetings }}</h3>
            <span>Всего встреч</span>
        </div>
    </div>
</div>

{{-- Toolbar --}}
<div class="toolbar">
    <div class="filter-tabs">
        <a href="{{ route('admin.meetings.index') }}" class="filter-tab {{ !request('filter') ? 'active' : '' }}">
            <i class="fa-solid fa-list me-1"></i> Все
        </a>
        <a href="{{ route('admin.meetings.index', ['filter' => 'upcoming']) }}" class="filter-tab {{ request('filter') === 'upcoming' ? 'active' : '' }}">
            <i class="fa-solid fa-clock me-1"></i> Предстоящие
        </a>
        <a href="{{ route('admin.meetings.index', ['filter' => 'past']) }}" class="filter-tab {{ request('filter') === 'past' ? 'active' : '' }}">
            <i class="fa-solid fa-history me-1"></i> Прошедшие
        </a>
    </div>
</div>

{{-- Meetings Grid --}}
@if($meetings->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon">
            <i class="fa-solid fa-video-slash"></i>
        </div>
        <h4>Нет видеовстреч</h4>
        <p>Создайте первую видеовстречу для проведения собеседований с кандидатами в режиме онлайн</p>
        <a href="{{ route('admin.meetings.create') }}" class="btn btn-brb">
            <i class="fa-solid fa-plus me-2"></i> Создать встречу
        </a>
    </div>
@else
    <div class="meetings-grid">
        @foreach($meetings as $meeting)
            <div class="meeting-card">
                <div class="meeting-card-header">
                    <span class="meeting-status status-{{ $meeting->status }}">
                        @if($meeting->status === 'started')
                            <i class="fa-solid fa-circle fa-beat-fade me-1" style="font-size: 8px;"></i>
                        @endif
                        {{ $meeting->status_label }}
                    </span>
                </div>

                <div class="meeting-card-body">
                    <h4 class="meeting-title">{{ $meeting->title }}</h4>

                    @if($meeting->application)
                        <div class="meeting-candidate">
                            <i class="fa-solid fa-user-tie"></i>
                            {{ $meeting->application->user->name ?? 'Кандидат' }}
                            @if($meeting->application->vacancy)
                                — {{ $meeting->application->vacancy->title }}
                            @endif
                        </div>
                    @else
                        <div class="meeting-candidate" style="margin-bottom: 16px;"></div>
                    @endif

                    <div class="meeting-datetime">
                        <div class="datetime-item">
                            <i class="fa-regular fa-calendar"></i>
                            <div>
                                <div class="label">Дата</div>
                                <div class="value">{{ $meeting->scheduled_at->format('d.m.Y') }}</div>
                            </div>
                        </div>
                        <div class="datetime-item">
                            <i class="fa-regular fa-clock"></i>
                            <div>
                                <div class="label">Время</div>
                                <div class="value">{{ $meeting->scheduled_at->format('H:i') }}</div>
                            </div>
                        </div>
                        <div class="datetime-item">
                            <i class="fa-solid fa-hourglass-half"></i>
                            <div>
                                <div class="label">Длительность</div>
                                <div class="value">{{ $meeting->duration_minutes }} мин</div>
                            </div>
                        </div>
                    </div>

                    <div class="meeting-participants-section">
                        <div class="participants-label">Участники</div>
                        <div class="participants-avatars">
                            @forelse($meeting->participants->take(4) as $participant)
                                <div class="participant-avatar" title="{{ $participant->user->name ?? 'Участник' }}">
                                    @if($participant->user->avatar ?? false)
                                        <img src="{{ asset('storage/' . $participant->user->avatar) }}" alt="">
                                    @else
                                        {{ $participant->user->initials ?? '?' }}
                                    @endif
                                </div>
                            @empty
                                <span class="participants-count">Нет участников</span>
                            @endforelse
                            @if($meeting->participants->count() > 4)
                                <div class="participant-avatar more">
                                    +{{ $meeting->participants->count() - 4 }}
                                </div>
                            @endif
                            @if($meeting->participants->count() > 0)
                                <span class="participants-count">
                                    {{ $meeting->participants->count() }} чел.
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="meeting-card-footer">
                    @if($meeting->status === 'scheduled')
                        <a href="{{ route('admin.meetings.room', $meeting) }}" class="btn btn-brb">
                            <i class="fa-solid fa-video"></i> Начать
                        </a>
                        <a href="{{ route('admin.meetings.edit', $meeting) }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                        <form action="{{ route('admin.meetings.cancel', $meeting) }}" method="POST"
                              onsubmit="return confirm('Отменить встречу?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </form>
                    @elseif($meeting->status === 'started')
                        <a href="{{ route('admin.meetings.room', $meeting) }}" class="btn btn-success">
                            <i class="fa-solid fa-right-to-bracket"></i> Присоединиться
                        </a>
                    @else
                        <a href="{{ route('admin.meetings.show', $meeting) }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-eye"></i> Подробнее
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    @if($meetings->hasPages())
        <div class="pagination-wrapper">
            {{ $meetings->links() }}
        </div>
    @endif
@endif
@endsection
