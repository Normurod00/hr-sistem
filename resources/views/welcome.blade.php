@extends('layouts.app')

@section('title', 'Работа в BRB Bank — Вакансии')

@section('content')
    @push('styles')
    <style>
        /* ===== HERO SECTION - FlexJob inspired ===== */
        .hero-section {
            background: linear-gradient(135deg, var(--brb-secondary) 0%, #16213e 50%, #0f0f23 100%);
            padding: 100px 0 80px;
            position: relative;
            overflow: hidden;
        }

        /* Abstract 3D circles pattern - FlexJob style */
        .hero-section::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(214, 0, 28, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(252, 188, 69, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-pattern {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 80% 20%, rgba(255,255,255,0.03) 0%, transparent 25%),
                radial-gradient(circle at 20% 80%, rgba(255,255,255,0.02) 0%, transparent 25%),
                radial-gradient(circle at 60% 60%, rgba(255,255,255,0.02) 0%, transparent 20%);
            pointer-events: none;
        }

        .hero-floating-shapes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.03);
            animation: float 20s infinite ease-in-out;
        }

        .floating-shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: 10%;
            right: 15%;
            animation-delay: 0s;
        }

        .floating-shape:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 50%;
            right: 25%;
            animation-delay: -5s;
        }

        .floating-shape:nth-child(3) {
            width: 150px;
            height: 150px;
            bottom: 20%;
            left: 10%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        .hero-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-text {
            color: #fff;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(252, 188, 69, 0.15);
            border: 1px solid rgba(252, 188, 69, 0.3);
            color: var(--brb-accent);
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .hero-badge i {
            font-size: 14px;
        }

        .hero-title {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.15;
            margin-bottom: 20px;
            letter-spacing: -0.5px;
        }

        .hero-title span {
            color: var(--brb-accent);
        }

        .hero-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 18px;
            line-height: 1.7;
            margin-bottom: 36px;
            max-width: 500px;
        }

        .hero-buttons {
            display: flex;
            gap: 16px;
            margin-bottom: 48px;
        }

        .hero-stats {
            display: flex;
            gap: 48px;
        }

        .hero-stat {
            text-align: left;
        }

        .hero-stat-value {
            font-size: 36px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            margin-bottom: 4px;
        }

        .hero-stat-value span {
            color: var(--brb-accent);
        }

        .hero-stat-label {
            font-size: 14px;
            color: rgba(255,255,255,0.5);
        }

        /* Search card */
        .hero-search-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-xl);
            padding: 32px;
        }

        .search-card-title {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .search-form-group {
            margin-bottom: 16px;
        }

        .search-form-label {
            display: block;
            color: rgba(255,255,255,0.6);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .search-form-input {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-md);
            color: #fff;
            font-size: 15px;
            transition: all 0.25s ease;
        }

        .search-form-input::placeholder {
            color: rgba(255,255,255,0.4);
        }

        .search-form-input:focus {
            outline: none;
            background: rgba(255,255,255,0.12);
            border-color: var(--brb-accent);
        }

        .search-form-select {
            width: 100%;
            padding: 14px 18px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-md);
            color: #fff;
            font-size: 15px;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='rgba(255,255,255,0.5)' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
        }

        .search-form-select option {
            background: var(--brb-secondary);
            color: #fff;
        }

        .search-form-btn {
            width: 100%;
            margin-top: 8px;
        }

        .popular-searches {
            margin-top: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .popular-search-tag {
            padding: 6px 14px;
            background: rgba(255,255,255,0.08);
            border-radius: var(--radius-pill);
            color: rgba(255,255,255,0.7);
            font-size: 13px;
            transition: all 0.2s;
        }

        .popular-search-tag:hover {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        /* ===== BENEFITS SECTION - FlexJob style ===== */
        .benefits-section {
            padding: 80px 0;
            background: var(--brb-bg-white);
            position: relative;
        }

        .benefits-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 1px;
            height: 60px;
            background: linear-gradient(to bottom, var(--brb-border), transparent);
        }

        .section-header-center {
            text-align: center;
            margin-bottom: 56px;
        }

        .section-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--brb-primary-light);
            color: var(--brb-primary);
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 16px;
        }

        .section-title {
            font-size: 36px;
            font-weight: 700;
            color: var(--brb-text);
            margin-bottom: 12px;
        }

        .section-subtitle {
            font-size: 16px;
            color: var(--brb-text-secondary);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.7;
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .benefit-card {
            background: var(--brb-bg);
            border-radius: var(--radius-lg);
            padding: 32px;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .benefit-card:hover {
            background: var(--brb-bg-white);
            border-color: var(--brb-border);
            box-shadow: var(--shadow-lg);
            transform: translateY(-4px);
        }

        .benefit-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .benefit-icon.primary {
            background: var(--brb-primary-light);
            color: var(--brb-primary);
        }

        .benefit-icon.accent {
            background: var(--brb-accent-light);
            color: #B45309;
        }

        .benefit-icon.success {
            background: #ECFDF5;
            color: #059669;
        }

        .benefit-icon.info {
            background: #EFF6FF;
            color: #2563EB;
        }

        .benefit-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--brb-text);
            margin-bottom: 8px;
        }

        .benefit-desc {
            font-size: 14px;
            color: var(--brb-text-secondary);
            line-height: 1.7;
        }

        /* ===== VACANCIES SECTION ===== */
        .vacancies-section {
            padding: 80px 0;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .section-header-left h2 {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 8px;
        }

        .section-header-left p {
            color: var(--brb-text-secondary);
            margin: 0;
        }

        .view-all-link {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--brb-primary);
            font-weight: 600;
            font-size: 15px;
            padding: 12px 24px;
            border-radius: var(--radius-pill);
            border: 2px solid var(--brb-primary);
            transition: all 0.25s ease;
        }

        .view-all-link:hover {
            background: var(--brb-primary);
            color: #fff;
        }

        .vacancies-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
        }

        /* Job card - hh.ru + FlexJob hybrid */
        .vacancy-card {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            padding: 28px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .vacancy-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--brb-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .vacancy-card:hover {
            border-color: var(--brb-primary);
            box-shadow: var(--shadow-lg);
        }

        .vacancy-card:hover::before {
            opacity: 1;
        }

        .vacancy-card.featured {
            border-color: var(--brb-accent);
            background: linear-gradient(135deg, var(--brb-accent-light) 0%, var(--brb-bg-white) 100%);
        }

        .vacancy-card.featured::before {
            background: var(--brb-accent);
            opacity: 1;
        }

        .vacancy-card.featured .vacancy-badge {
            background: var(--brb-accent);
            color: var(--brb-secondary);
        }

        .vacancy-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .vacancy-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 600;
            background: var(--brb-primary-light);
            color: var(--brb-primary);
            white-space: nowrap;
        }

        .vacancy-badge.new {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .vacancy-badge.hot {
            background: #FEF3C7;
            color: #92400E;
        }

        .vacancy-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--brb-text);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .vacancy-title a {
            color: inherit;
        }

        .vacancy-title a:hover {
            color: var(--brb-primary);
        }

        .vacancy-company {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--brb-text-secondary);
            font-size: 14px;
        }

        .vacancy-company i {
            color: var(--brb-text-muted);
        }

        .vacancy-salary {
            font-size: 20px;
            font-weight: 700;
            color: var(--brb-success);
            white-space: nowrap;
        }

        .vacancy-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
            padding: 16px 0;
            border-top: 1px solid var(--brb-border-light);
            border-bottom: 1px solid var(--brb-border-light);
        }

        .vacancy-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--brb-text-secondary);
            font-size: 14px;
        }

        .vacancy-meta-item i {
            font-size: 16px;
            color: var(--brb-text-muted);
        }

        .vacancy-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .vacancy-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .vacancy-tag {
            background: var(--brb-bg);
            color: var(--brb-text-secondary);
            padding: 6px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 500;
        }

        .vacancy-action {
            color: var(--brb-primary);
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .vacancy-action:hover {
            gap: 10px;
        }

        /* ===== DEPARTMENTS SECTION ===== */
        .departments-section {
            padding: 80px 0;
            background: var(--brb-bg-white);
        }

        .departments-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .department-card {
            background: var(--brb-bg);
            border-radius: var(--radius-lg);
            padding: 28px;
            text-align: center;
            transition: all 0.3s ease;
            text-decoration: none;
            border: 2px solid transparent;
        }

        .department-card:hover {
            background: var(--brb-bg-white);
            border-color: var(--brb-primary);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .department-icon {
            width: 64px;
            height: 64px;
            border-radius: var(--radius-lg);
            background: var(--brb-bg-white);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 28px;
            color: var(--brb-primary);
            transition: all 0.3s ease;
        }

        .department-card:hover .department-icon {
            background: var(--brb-primary);
            color: #fff;
            transform: scale(1.1);
        }

        .department-name {
            font-weight: 700;
            font-size: 16px;
            color: var(--brb-text);
            margin-bottom: 4px;
        }

        .department-count {
            font-size: 13px;
            color: var(--brb-text-muted);
        }

        /* ===== CTA SECTION - FlexJob style ===== */
        .cta-section {
            padding: 100px 0;
            background: linear-gradient(135deg, var(--brb-secondary) 0%, #16213e 100%);
            position: relative;
            overflow: hidden;
        }

        .cta-section::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(214, 0, 28, 0.1) 0%, transparent 50%);
            pointer-events: none;
        }

        .cta-content {
            text-align: center;
            max-width: 700px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .cta-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--brb-accent) 0%, var(--brb-accent-dark) 100%);
            border-radius: var(--radius-xl);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            font-size: 36px;
            color: var(--brb-secondary);
            box-shadow: 0 10px 30px rgba(252, 188, 69, 0.3);
        }

        .cta-title {
            color: #fff;
            font-size: 40px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .cta-subtitle {
            color: rgba(255,255,255,0.7);
            font-size: 18px;
            margin-bottom: 40px;
            line-height: 1.7;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .hero-content {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .hero-search-card {
                max-width: 500px;
                margin: 0 auto;
            }

            .benefits-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .departments-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }

            .hero-title {
                font-size: 36px;
            }

            .hero-subtitle {
                font-size: 16px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-stats {
                flex-wrap: wrap;
                gap: 32px;
            }

            .hero-search-card {
                padding: 24px;
            }

            .benefits-grid,
            .departments-grid {
                grid-template-columns: 1fr;
            }

            .vacancies-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }

            .section-title {
                font-size: 28px;
            }

            .cta-title {
                font-size: 28px;
            }

            .cta-buttons {
                flex-direction: column;
            }
        }
    </style>
    @endpush

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-pattern"></div>
        <div class="hero-floating-shapes">
            <div class="floating-shape"></div>
            <div class="floating-shape"></div>
            <div class="floating-shape"></div>
        </div>

        <div class="container-brb">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="hero-badge">
                        <i class="bi bi-stars"></i>
                        #1 HR Portal в Узбекистане
                    </div>

                    <h1 class="hero-title">
                        Найдите работу<br>мечты в <span>BRB Bank</span>
                    </h1>

                    <p class="hero-subtitle">
                        Присоединяйтесь к команде профессионалов. Мы предлагаем конкурентную
                        зарплату, карьерный рост и современный офис в центре Ташкента.
                    </p>

                    <div class="hero-buttons">
                        <a href="{{ route('vacant.index') }}" class="btn-accent btn-lg">
                            <i class="bi bi-search"></i>
                            Найти вакансию
                        </a>
                        <a href="{{ route('candidate.login') }}" class="btn-outline-brb btn-lg" style="border-color: rgba(255,255,255,0.3); color: #fff;">
                            Войти / Создать резюме
                        </a>
                    </div>

                    <div class="hero-stats">
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ \App\Models\Vacancy::where('status', 'published')->count() }}<span>+</span></div>
                            <div class="hero-stat-label">Открытых вакансий</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">{{ \App\Models\User::where('role', 'candidate')->count() }}<span>+</span></div>
                            <div class="hero-stat-label">Соискателей</div>
                        </div>
                        <div class="hero-stat">
                            <div class="hero-stat-value">50<span>+</span></div>
                            <div class="hero-stat-label">Филиалов</div>
                        </div>
                    </div>
                </div>

                <div class="hero-search-card">
                    <h3 class="search-card-title">Быстрый поиск вакансий</h3>

                    <form action="{{ route('vacant.index') }}" method="GET">
                        <div class="search-form-group">
                            <label class="search-form-label">Должность или ключевые слова</label>
                            <input type="text" name="search" class="search-form-input" placeholder="Например: менеджер, разработчик">
                        </div>

                        <div class="search-form-group">
                            <label class="search-form-label">Отдел</label>
                            <select name="department" class="search-form-select">
                                <option value="">Все отделы</option>
                                <option value="IT">IT и Разработка</option>
                                <option value="Финансы">Финансы</option>
                                <option value="Маркетинг">Маркетинг</option>
                                <option value="HR">HR</option>
                                <option value="Розница">Розничный бизнес</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-accent search-form-btn btn-lg">
                            <i class="bi bi-search"></i>
                            Найти вакансии
                        </button>
                    </form>

                    <div class="popular-searches">
                        <span style="color: rgba(255,255,255,0.5); font-size: 13px; margin-right: 8px;">Популярное:</span>
                        <a href="{{ route('vacant.index', ['search' => 'менеджер']) }}" class="popular-search-tag">Менеджер</a>
                        <a href="{{ route('vacant.index', ['search' => 'разработчик']) }}" class="popular-search-tag">Разработчик</a>
                        <a href="{{ route('vacant.index', ['search' => 'аналитик']) }}" class="popular-search-tag">Аналитик</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits-section">
        <div class="container-brb">
            <div class="section-header-center">
                <div class="section-label">
                    <i class="bi bi-award"></i>
                    Преимущества
                </div>
                <h2 class="section-title">Почему выбирают BRB Bank</h2>
                <p class="section-subtitle">
                    Мы создаём комфортные условия для работы и развития каждого сотрудника
                </p>
            </div>

            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon primary">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h3 class="benefit-title">Достойная зарплата</h3>
                    <p class="benefit-desc">
                        Конкурентная оплата труда, регулярный пересмотр и бонусы за достижения
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon accent">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h3 class="benefit-title">Карьерный рост</h3>
                    <p class="benefit-desc">
                        Программы развития, внутренние тренинги и возможности для продвижения
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon success">
                        <i class="bi bi-people"></i>
                    </div>
                    <h3 class="benefit-title">Сильная команда</h3>
                    <p class="benefit-desc">
                        Работайте с профессионалами и учитесь у лучших специалистов отрасли
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon info">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h3 class="benefit-title">Социальный пакет</h3>
                    <p class="benefit-desc">
                        ДМС, питание, корпоративные мероприятия и программы лояльности
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon primary">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <h3 class="benefit-title">Современный офис</h3>
                    <p class="benefit-desc">
                        Комфортное рабочее пространство в центре города с зонами отдыха
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon accent">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <h3 class="benefit-title">Обучение</h3>
                    <p class="benefit-desc">
                        Корпоративный университет, курсы и сертификации за счёт компании
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest Vacancies Section -->
    <section class="vacancies-section">
        <div class="container-brb">
            <div class="section-header">
                <div class="section-header-left">
                    <h2>Актуальные вакансии</h2>
                    <p>Найдите идеальную позицию для вашей карьеры</p>
                </div>
                <a href="{{ route('vacant.index') }}" class="view-all-link">
                    Все вакансии
                    <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="vacancies-grid">
                @forelse(\App\Models\Vacancy::where('status', 'published')->latest()->take(6)->get() as $index => $vacancy)
                    <div class="vacancy-card {{ $index === 0 ? 'featured' : '' }}">
                        <div class="vacancy-header">
                            <div>
                                @if($index === 0)
                                    <span class="vacancy-badge">
                                        <i class="bi bi-star-fill"></i>
                                        Топ вакансия
                                    </span>
                                @elseif($vacancy->created_at->diffInDays(now()) < 3)
                                    <span class="vacancy-badge new">Новая</span>
                                @endif
                            </div>
                            @if($vacancy->salary_from || $vacancy->salary_to)
                                <div class="vacancy-salary">
                                    @if($vacancy->salary_from && $vacancy->salary_to)
                                        {{ number_format($vacancy->salary_from, 0, '', ' ') }} - {{ number_format($vacancy->salary_to, 0, '', ' ') }}
                                    @elseif($vacancy->salary_from)
                                        от {{ number_format($vacancy->salary_from, 0, '', ' ') }}
                                    @else
                                        до {{ number_format($vacancy->salary_to, 0, '', ' ') }}
                                    @endif
                                    <small style="font-size: 12px; font-weight: 400;">сум</small>
                                </div>
                            @endif
                        </div>

                        <h3 class="vacancy-title">
                            <a href="{{ route('vacant.show', $vacancy) }}">{{ $vacancy->title }}</a>
                        </h3>

                        <div class="vacancy-company">
                            <i class="bi bi-building"></i>
                            {{ $vacancy->department ?? 'BRB Bank' }}
                        </div>

                        <div class="vacancy-meta">
                            @if($vacancy->city)
                                <span class="vacancy-meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    {{ $vacancy->city }}
                                </span>
                            @endif
                            @if($vacancy->employment_type)
                                <span class="vacancy-meta-item">
                                    <i class="bi bi-clock"></i>
                                    {{ $vacancy->employment_type }}
                                </span>
                            @endif
                            @if($vacancy->experience)
                                <span class="vacancy-meta-item">
                                    <i class="bi bi-briefcase"></i>
                                    {{ $vacancy->experience }}
                                </span>
                            @endif
                        </div>

                        <div class="vacancy-footer">
                            <div class="vacancy-tags">
                                <span class="vacancy-tag">{{ $vacancy->created_at->diffForHumans() }}</span>
                            </div>
                            <a href="{{ route('vacant.show', $vacancy) }}" class="vacancy-action">
                                Подробнее
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5" style="grid-column: 1 / -1;">
                        <i class="bi bi-briefcase" style="font-size: 48px; color: var(--brb-text-muted);"></i>
                        <p class="mt-3 text-muted">Вакансий пока нет</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- Departments Section -->
    <section class="departments-section">
        <div class="container-brb">
            <div class="section-header-center">
                <div class="section-label">
                    <i class="bi bi-grid"></i>
                    Направления
                </div>
                <h2 class="section-title">Выберите направление</h2>
                <p class="section-subtitle">
                    Найдите подходящую вакансию в интересующем вас отделе
                </p>
            </div>

            <div class="departments-grid">
                <a href="{{ route('vacant.index', ['department' => 'IT']) }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-code-slash"></i>
                    </div>
                    <div class="department-name">IT и Разработка</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->where('department', 'like', '%IT%')->count() }} вакансий</div>
                </a>

                <a href="{{ route('vacant.index', ['department' => 'Финансы']) }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div class="department-name">Финансы</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->where('department', 'like', '%Финанс%')->count() }} вакансий</div>
                </a>

                <a href="{{ route('vacant.index', ['department' => 'Маркетинг']) }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <div class="department-name">Маркетинг</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->where('department', 'like', '%Маркетинг%')->count() }} вакансий</div>
                </a>

                <a href="{{ route('vacant.index', ['department' => 'HR']) }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="department-name">HR</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->where('department', 'like', '%HR%')->count() }} вакансий</div>
                </a>

                <a href="{{ route('vacant.index', ['department' => 'Розница']) }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-shop"></i>
                    </div>
                    <div class="department-name">Розничный бизнес</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->where('department', 'like', '%Розниц%')->count() }} вакансий</div>
                </a>

                <a href="{{ route('vacant.index', ['department' => 'Корпоративный']) }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="department-name">Корпоративный бизнес</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->where('department', 'like', '%Корпоратив%')->count() }} вакансий</div>
                </a>

                <a href="{{ route('vacant.index', ['department' => 'Безопасность']) }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div class="department-name">Безопасность</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->where('department', 'like', '%Безопасн%')->count() }} вакансий</div>
                </a>

                <a href="{{ route('vacant.index') }}" class="department-card">
                    <div class="department-icon">
                        <i class="bi bi-grid-3x3-gap"></i>
                    </div>
                    <div class="department-name">Все направления</div>
                    <div class="department-count">{{ \App\Models\Vacancy::where('status', 'published')->count() }} вакансий</div>
                </a>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container-brb">
            <div class="cta-content">
                <div class="cta-icon">
                    <i class="bi bi-rocket-takeoff"></i>
                </div>
                <h2 class="cta-title">Готовы начать карьеру в BRB Bank?</h2>
                <p class="cta-subtitle">
                    Создайте резюме прямо сейчас и откликнитесь на интересующие вакансии.
                    Наши HR-специалисты свяжутся с вами в ближайшее время.
                </p>
                <div class="cta-buttons">
                    <a href="{{ route('candidate.login') }}" class="btn-accent btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Войти в личный кабинет
                    </a>
                    <a href="{{ route('vacant.index') }}" class="btn-outline-brb btn-lg" style="border-color: rgba(255,255,255,0.2); color: #fff;">
                        Смотреть вакансии
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
