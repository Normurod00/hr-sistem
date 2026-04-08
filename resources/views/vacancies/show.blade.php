@extends('layouts.app')

@section('title', $vacancy->title . ' — ' . config('app.name'))

@section('content')
    @push('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .vacancy-page-header {
            background: linear-gradient(135deg, var(--brb-primary) 0%, #8B0012 100%);
            padding: 32px 0 48px;
        }

        .breadcrumbs {
            margin-bottom: 20px;
        }

        .breadcrumbs a {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
        }

        .breadcrumbs a:hover {
            color: #fff;
        }

        .breadcrumbs span {
            color: rgba(255,255,255,0.6);
            font-size: 14px;
        }

        .vacancy-main-title {
            color: #fff;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .vacancy-main-salary {
            color: #fff;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .badge-new-header {
            background: #fff;
            color: var(--brb-primary);
            padding: 4px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
        }

        .vacancy-meta-header {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
            color: rgba(255,255,255,0.9);
            font-size: 15px;
        }

        .vacancy-meta-header span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ===== CONTENT LAYOUT ===== */
        .vacancy-layout {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 32px;
            margin-top: -32px;
        }

        /* ===== MAIN CONTENT ===== */
        .vacancy-main-content {
            position: relative;
            z-index: 1;
        }

        .content-card {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .content-section {
            padding: 28px 32px;
            border-bottom: 1px solid var(--brb-border-light);
        }

        .content-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--brb-text);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--brb-primary);
            font-size: 20px;
        }

        .vacancy-desc {
            font-size: 15px;
            line-height: 1.8;
            color: var(--brb-text);
            white-space: pre-line;
        }

        /* ===== SKILLS ===== */
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-item {
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 500;
        }

        .skill-item.required {
            background: var(--brb-primary-light);
            color: var(--brb-primary);
        }

        .skill-item.optional {
            background: var(--brb-bg);
            color: var(--brb-text-secondary);
        }

        .language-item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--brb-bg);
            border-radius: var(--radius-md);
            font-size: 14px;
        }

        .language-level {
            padding: 3px 10px;
            background: var(--brb-primary);
            color: #fff;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
        }

        /* ===== SIDEBAR ===== */
        .vacancy-sidebar {
            position: relative;
            z-index: 1;
        }

        .sidebar-sticky {
            position: sticky;
            top: 88px;
        }

        .apply-card {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            padding: 28px;
            margin-bottom: 20px;
        }

        .apply-card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--brb-text);
            margin-bottom: 12px;
        }

        .apply-card-text {
            color: var(--brb-text-secondary);
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .btn-apply-full {
            display: block;
            width: 100%;
            padding: 14px 24px;
            background: var(--brb-primary);
            color: #fff;
            border: none;
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s;
        }

        .btn-apply-full:hover {
            background: var(--brb-primary-dark);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        .btn-apply-outline-full {
            display: block;
            width: 100%;
            padding: 13px 24px;
            background: transparent;
            color: var(--brb-primary);
            border: 2px solid var(--brb-primary);
            border-radius: var(--radius-md);
            font-size: 16px;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s;
            margin-top: 12px;
        }

        .btn-apply-outline-full:hover {
            background: var(--brb-primary-light);
            color: var(--brb-primary);
        }

        .applied-success {
            text-align: center;
            padding: 20px 0;
        }

        .applied-success i {
            font-size: 56px;
            color: var(--brb-success);
            margin-bottom: 16px;
        }

        .applied-success h5 {
            font-weight: 600;
            color: var(--brb-text);
            margin-bottom: 8px;
        }

        /* ===== COMPANY CARD ===== */
        .company-card {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            padding: 28px;
        }

        .company-header {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--brb-border-light);
        }

        .company-logo {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, var(--brb-primary) 0%, #8B0012 100%);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
        }

        .company-info h4 {
            font-size: 18px;
            font-weight: 600;
            color: var(--brb-text);
            margin-bottom: 4px;
        }

        .company-verified {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 13px;
            color: var(--brb-success);
        }

        .company-detail {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            font-size: 14px;
            color: var(--brb-text-secondary);
            border-bottom: 1px solid var(--brb-border-light);
        }

        .company-detail:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .company-detail i {
            width: 20px;
            color: var(--brb-text-muted);
        }

        .company-detail a {
            color: var(--brb-primary);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .vacancy-layout {
                grid-template-columns: 1fr;
            }

            .sidebar-sticky {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .vacancy-main-title {
                font-size: 28px;
            }

            .vacancy-main-salary {
                font-size: 20px;
            }

            .content-section {
                padding: 24px 20px;
            }

            .vacancy-meta-header {
                gap: 16px;
            }
        }
    </style>
    @endpush

    <!-- Page Header -->
    <section class="vacancy-page-header">
        <div class="container-brb">
            <nav class="breadcrumbs">
                <a href="{{ route('vacant.index') }}">Вакансии</a>
                <span> / {{ Str::limit($vacancy->title, 40) }}</span>
            </nav>

            <h1 class="vacancy-main-title">{{ $vacancy->title }}</h1>

            @if($vacancy->salary_formatted)
                <div class="vacancy-main-salary">
                    {{ $vacancy->salary_formatted }}
                    @if($vacancy->created_at->gt(now()->subDays(3)))
                        <span class="badge-new-header">Новая</span>
                    @endif
                </div>
            @endif

            <div class="vacancy-meta-header">
                <span><i class="bi bi-building"></i> {{ config('app.name') }}</span>
                @if($vacancy->location || $vacancy->city)
                    <span><i class="bi bi-geo-alt"></i> {{ $vacancy->location ?? $vacancy->city }}</span>
                @endif
                <span><i class="bi bi-briefcase"></i> {{ $vacancy->employment_type_label ?? 'Полная занятость' }}</span>
                @if($vacancy->min_experience_years)
                    <span><i class="bi bi-clock-history"></i> Опыт от {{ $vacancy->min_experience_years }} {{ trans_choice('год|года|лет', $vacancy->min_experience_years) }}</span>
                @endif
                <span><i class="bi bi-clock"></i> {{ $vacancy->created_at->diffForHumans() }}</span>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container-brb" style="padding-top: 0; padding-bottom: 48px;">
        <div class="vacancy-layout">
            <!-- Main Content -->
            <main class="vacancy-main-content">
                <article class="content-card">
                    <!-- Description -->
                    <section class="content-section">
                        <h2 class="section-title">
                            <i class="bi bi-file-text"></i>
                            Описание вакансии
                        </h2>
                        <div class="vacancy-desc">{{ $vacancy->description }}</div>
                    </section>

                    <!-- Required Skills -->
                    @if($vacancy->must_have_skills && count($vacancy->must_have_skills))
                        <section class="content-section">
                            <h3 class="section-title">
                                <i class="bi bi-check-circle-fill"></i>
                                Обязательные навыки
                            </h3>
                            <div class="skills-list">
                                @foreach($vacancy->must_have_skills as $skill)
                                    <span class="skill-item required">{{ $skill }}</span>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <!-- Nice to have Skills -->
                    @if($vacancy->nice_to_have_skills && count($vacancy->nice_to_have_skills))
                        <section class="content-section">
                            <h3 class="section-title">
                                <i class="bi bi-star-fill"></i>
                                Будет плюсом
                            </h3>
                            <div class="skills-list">
                                @foreach($vacancy->nice_to_have_skills as $skill)
                                    <span class="skill-item optional">{{ $skill }}</span>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    <!-- Languages -->
                    @if($vacancy->language_requirements && count($vacancy->language_requirements))
                        <section class="content-section">
                            <h3 class="section-title">
                                <i class="bi bi-translate"></i>
                                Знание языков
                            </h3>
                            <div class="skills-list">
                                @foreach($vacancy->language_requirements as $lang)
                                    <div class="language-item">
                                        <span>{{ is_array($lang) ? ($lang['name'] ?? '') : $lang }}</span>
                                        @if(is_array($lang) && isset($lang['level']))
                                            <span class="language-level">{{ $lang['level'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </article>

                <!-- Back to vacancies -->
                <a href="{{ route('vacant.index') }}" class="btn-outline-brb">
                    <i class="bi bi-arrow-left me-2"></i>
                    Все вакансии
                </a>
            </main>

            <!-- Sidebar -->
            <aside class="vacancy-sidebar">
                <div class="sidebar-sticky">
                    <!-- Apply Card -->
                    <div class="apply-card">
                        @guest
                            <h4 class="apply-card-title">Откликнитесь на вакансию</h4>
                            <p class="apply-card-text">
                                Войдите или создайте резюме, чтобы отправить отклик и отслеживать статус заявки.
                            </p>
                            <a href="{{ route('candidate.login') }}" class="btn-apply-full">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                            </a>
                            <a href="{{ route('candidate.register') }}" class="btn-apply-outline-full">
                                <i class="bi bi-person-plus me-2"></i>Создать профиль
                            </a>
                        @else
                            @if($hasApplied)
                                <div class="applied-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                    <h5>Вы уже откликнулись!</h5>
                                    <p style="color: var(--brb-text-secondary); margin-bottom: 16px;">
                                        Статус:
                                        <span class="badge-brb {{ $application->status_bg_class ?? 'badge-secondary' }}">
                                            {{ $application->status_label ?? 'На рассмотрении' }}
                                        </span>
                                    </p>
                                    <a href="{{ route('profile.applications.show', $application) }}" class="btn-apply-outline-full" style="margin-top: 0;">
                                        <i class="bi bi-eye me-2"></i>Посмотреть заявку
                                    </a>
                                </div>
                            @elseif(auth()->user()->isCandidate())
                                <h4 class="apply-card-title">Откликнитесь первым!</h4>
                                <p class="apply-card-text">
                                    Работодатели обращают внимание на первые отклики. Не упустите шанс!
                                </p>
                                <a href="{{ route('applications.create', $vacancy) }}" class="btn-apply-full">
                                    <i class="bi bi-send-fill me-2"></i>Откликнуться
                                </a>
                            @else
                                <div style="text-align: center; padding: 20px 0;">
                                    <i class="bi bi-info-circle" style="font-size: 40px; color: var(--brb-text-muted);"></i>
                                    <p style="color: var(--brb-text-secondary); margin-top: 12px; margin-bottom: 0;">
                                        Откликаться могут только кандидаты
                                    </p>
                                </div>
                            @endif
                        @endguest
                    </div>

                    <!-- Company Card -->
                    <div class="company-card">
                        <div class="company-header">
                            <div class="company-logo">HR</div>
                            <div class="company-info">
                                <h4>{{ config('app.name') }}</h4>
                                <div class="company-verified">
                                    <i class="bi bi-patch-check-fill"></i>
                                    Проверенный работодатель
                                </div>
                            </div>
                        </div>

                        <div class="company-detail">
                            <i class="bi bi-people"></i>
                            <span>1000+ сотрудников</span>
                        </div>
                        <div class="company-detail">
                            <i class="bi bi-geo-alt"></i>
                            <span>Ташкент, Узбекистан</span>
                        </div>
                        <div class="company-detail">
                            <i class="bi bi-bank"></i>
                            <span>Банки / Финансы</span>
                        </div>
                        <div class="company-detail">
                            <i class="bi bi-globe"></i>
                            <a href="#" target="_blank">hr-system.uz</a>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
