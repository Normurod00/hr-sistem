@extends('layouts.app')

@section('title', 'Мой профиль — BRB Bank')

@section('content')
<style>
    .profile-page {
        background: #f9fafb;
        min-height: calc(100vh - 200px);
        padding: 40px 0 60px;
    }

    .profile-header {
        background: linear-gradient(135deg, var(--brb-red) 0%, #8B0012 100%);
        padding: 40px 0;
        color: #fff;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 40px;
        font-weight: 700;
        color: var(--brb-red);
        border: 4px solid rgba(255,255,255,0.3);
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .profile-name {
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .profile-email {
        font-size: 16px;
        opacity: 0.9;
    }

    .profile-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
    }

    .btn-profile {
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .btn-profile-primary {
        background: #fff;
        color: var(--brb-red);
    }

    .btn-profile-primary:hover {
        background: rgba(255,255,255,0.9);
        color: var(--brb-red);
    }

    .btn-profile-outline {
        background: transparent;
        color: #fff;
        border: 1px solid rgba(255,255,255,0.5);
    }

    .btn-profile-outline:hover {
        background: rgba(255,255,255,0.1);
        color: #fff;
    }

    .profile-stats {
        display: flex;
        gap: 40px;
        margin-top: 24px;
    }

    .profile-stat {
        text-align: center;
    }

    .profile-stat-value {
        font-size: 32px;
        font-weight: 700;
    }

    .profile-stat-label {
        font-size: 13px;
        opacity: 0.8;
    }

    .profile-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .profile-card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profile-card-header i {
        font-size: 20px;
        color: var(--brb-red);
    }

    .profile-card-title {
        font-size: 18px;
        font-weight: 700;
        color: #222;
        margin: 0;
    }

    .profile-card-body {
        padding: 24px;
    }

    .profile-info-row {
        display: flex;
        padding: 16px 0;
        border-bottom: 1px solid #f5f5f5;
    }

    .profile-info-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .profile-info-row:first-child {
        padding-top: 0;
    }

    .profile-info-label {
        width: 140px;
        color: #888;
        font-size: 14px;
        flex-shrink: 0;
    }

    .profile-info-value {
        font-size: 15px;
        color: #222;
        font-weight: 500;
    }

    .skill-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        margin: 3px;
    }

    .skill-badge.strong {
        background: rgba(35, 169, 66, 0.1);
        color: #1a8f3e;
        border: 1px solid rgba(35, 169, 66, 0.2);
    }

    .skill-badge.middle {
        background: rgba(255, 193, 7, 0.15);
        color: #997404;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .skill-badge.basic {
        background: #f5f5f5;
        color: #666;
        border: 1px solid #e0e0e0;
    }

    .language-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #f8f9fa;
        border-radius: 8px;
        margin: 4px;
    }

    .language-level {
        padding: 2px 8px;
        background: var(--brb-red);
        color: #fff;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }

    .resume-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: linear-gradient(135deg, #e8f4fd 0%, #f5faff 100%);
        border-radius: 10px;
        border: 1px solid #c5e3f6;
    }

    .resume-badge i {
        font-size: 32px;
        color: #0095ff;
    }

    .resume-badge-content {
        flex: 1;
    }

    .resume-badge-title {
        font-weight: 600;
        color: #222;
        margin-bottom: 2px;
    }

    .resume-badge-meta {
        font-size: 12px;
        color: #666;
    }

    .quick-links {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .quick-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px;
        background: #f8f9fa;
        border-radius: 10px;
        text-decoration: none;
        color: #333;
        transition: all 0.2s;
    }

    .quick-link:hover {
        background: #f0f0f0;
        color: var(--brb-red);
    }

    .quick-link i {
        font-size: 24px;
        color: var(--brb-red);
    }

    .quick-link-text {
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .profile-stats {
            gap: 24px;
        }

        .profile-info-row {
            flex-direction: column;
            gap: 4px;
        }

        .profile-info-label {
            width: auto;
        }

        .quick-links {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Profile Header -->
<div class="profile-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-auto">
                <div class="profile-avatar">
                    @if($user->avatar_url && !str_contains($user->avatar_url, 'ui-avatars'))
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                    @else
                        {{ mb_substr($user->name, 0, 1) }}
                    @endif
                </div>
            </div>
            <div class="col">
                <h1 class="profile-name">{{ $user->name }}</h1>
                <div class="profile-email">{{ $user->email }}</div>
                <div class="profile-actions">
                    <a href="{{ route('profile.edit') }}" class="btn-profile btn-profile-primary">
                        <i class="bi bi-pencil me-1"></i> Редактировать
                    </a>
                    <a href="{{ route('profile.password') }}" class="btn-profile btn-profile-outline">
                        <i class="bi bi-key me-1"></i> Изменить пароль
                    </a>
                </div>
            </div>
            <div class="col-auto">
                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $applicationsCount }}</div>
                        <div class="profile-stat-label">{{ trans_choice('отклик|отклика|откликов', $applicationsCount) }}</div>
                    </div>
                    <div class="profile-stat">
                        <div class="profile-stat-value">{{ $invitedCount }}</div>
                        <div class="profile-stat-label">{{ trans_choice('приглашение|приглашения|приглашений', $invitedCount) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="profile-page">
    <div class="container">
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Personal Info -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <i class="bi bi-person-circle"></i>
                        <h2 class="profile-card-title">Личные данные</h2>
                    </div>
                    <div class="profile-card-body">
                        <div class="profile-info-row">
                            <div class="profile-info-label">Полное имя</div>
                            <div class="profile-info-value">{{ $user->name }}</div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label">Email</div>
                            <div class="profile-info-value">{{ $user->email }}</div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label">Телефон</div>
                            <div class="profile-info-value">{{ $user->phone ?? '—' }}</div>
                        </div>
                        <div class="profile-info-row">
                            <div class="profile-info-label">Регистрация</div>
                            <div class="profile-info-value">{{ $user->created_at->format('d.m.Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Candidate Profile from Resume -->
                @if($user->candidateProfile && !$user->candidateProfile->isEmpty())
                    @php $profile = $user->candidateProfile; @endphp

                    <div class="profile-card">
                        <div class="profile-card-header">
                            <i class="bi bi-robot"></i>
                            <h2 class="profile-card-title">Профиль из резюме</h2>
                        </div>
                        <div class="profile-card-body">
                            <div class="resume-badge mb-4">
                                <i class="bi bi-file-earmark-text"></i>
                                <div class="resume-badge-content">
                                    @if($profile->position_title)
                                        <div class="resume-badge-title">{{ $profile->position_title }}</div>
                                    @endif
                                    <div class="resume-badge-meta">
                                        @if($profile->years_of_experience)
                                            Опыт: {{ $profile->years_of_experience }} {{ trans_choice('год|года|лет', $profile->years_of_experience) }}
                                        @endif
                                        @if($profile->last_generated_at)
                                            · Обновлено {{ $profile->last_generated_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($profile->skills && count($profile->skills))
                                <h6 class="mb-3" style="font-weight: 600; color: #333;">Навыки</h6>
                                <div class="mb-4">
                                    @foreach($profile->skills as $skill)
                                        @php
                                            $level = is_array($skill) ? ($skill['level'] ?? 'basic') : 'basic';
                                            $name = is_array($skill) ? ($skill['name'] ?? $skill) : $skill;
                                        @endphp
                                        <span class="skill-badge {{ $level }}">{{ $name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if($profile->languages && count($profile->languages))
                                <h6 class="mb-3" style="font-weight: 600; color: #333;">Языки</h6>
                                <div>
                                    @foreach($profile->languages as $lang)
                                        <div class="language-item">
                                            <span>{{ is_array($lang) ? ($lang['name'] ?? $lang) : $lang }}</span>
                                            @if(is_array($lang) && isset($lang['level']))
                                                <span class="language-level">{{ $lang['level'] }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Quick Links -->
                <div class="profile-card">
                    <div class="profile-card-header">
                        <i class="bi bi-lightning"></i>
                        <h2 class="profile-card-title">Быстрые действия</h2>
                    </div>
                    <div class="profile-card-body">
                        <div class="quick-links">
                            <a href="{{ route('profile.applications') }}" class="quick-link">
                                <i class="bi bi-file-earmark-text"></i>
                                <span class="quick-link-text">Мои отклики</span>
                            </a>
                            <a href="{{ route('vacant.index') }}" class="quick-link">
                                <i class="bi bi-search"></i>
                                <span class="quick-link-text">Вакансии</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="quick-link">
                                <i class="bi bi-pencil"></i>
                                <span class="quick-link-text">Редактировать</span>
                            </a>
                            <a href="{{ route('profile.password') }}" class="quick-link">
                                <i class="bi bi-shield-lock"></i>
                                <span class="quick-link-text">Безопасность</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tip Card -->
                <div class="profile-card" style="background: linear-gradient(135deg, var(--brb-red) 0%, #8B0012 100%); color: #fff;">
                    <div class="profile-card-body">
                        <h5 style="font-weight: 700; margin-bottom: 12px;">
                            <i class="bi bi-lightbulb me-2"></i>Совет
                        </h5>
                        <p style="font-size: 14px; opacity: 0.9; margin-bottom: 16px;">
                            Полностью заполненный профиль повышает шансы на приглашение на собеседование на 40%!
                        </p>
                        <a href="{{ route('profile.edit') }}" class="btn-profile btn-profile-primary" style="display: inline-block;">
                            Заполнить профиль
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
