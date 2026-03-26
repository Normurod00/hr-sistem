@extends('layouts.app')

@section('title', 'Вакансии — BRB Bank HR')

@section('content')
    @push('styles')
    <style>
        /* ===== PAGE HEADER - FlexJob style ===== */
        .page-header {
            background: linear-gradient(135deg, var(--brb-secondary) 0%, #16213e 50%, #0f0f23 100%);
            padding: 60px 0 100px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(214, 0, 28, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(252, 188, 69, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .page-header-content {
            position: relative;
            z-index: 1;
        }

        .page-header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
        }

        .page-title {
            color: #fff;
            font-size: 40px;
            font-weight: 800;
            margin: 0 0 12px;
            letter-spacing: -0.5px;
        }

        .page-stats {
            color: rgba(255,255,255,0.7);
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .page-stats strong {
            color: var(--brb-accent);
            font-weight: 700;
        }

        .page-stats-badge {
            background: rgba(252, 188, 69, 0.2);
            color: var(--brb-accent);
            padding: 6px 14px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* ===== SEARCH BOX - Glass morphism ===== */
        .search-form {
            display: flex;
            gap: 12px;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(20px);
            padding: 10px;
            border-radius: var(--radius-xl);
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .search-field {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 20px;
            background: var(--brb-bg);
            border-radius: var(--radius-lg);
            transition: all 0.25s ease;
        }

        .search-field:focus-within {
            background: var(--brb-bg-white);
            box-shadow: 0 0 0 2px var(--brb-primary);
        }

        .search-field i {
            color: var(--brb-text-muted);
            font-size: 18px;
        }

        .search-field input {
            flex: 1;
            border: none;
            padding: 16px 0;
            font-size: 15px;
            outline: none;
            background: transparent;
            color: var(--brb-text);
        }

        .search-field input::placeholder {
            color: var(--brb-text-muted);
        }

        .search-submit {
            background: linear-gradient(135deg, var(--brb-accent) 0%, var(--brb-accent-dark) 100%);
            color: var(--brb-secondary);
            border: none;
            padding: 16px 36px;
            border-radius: var(--radius-lg);
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(252, 188, 69, 0.3);
        }

        .search-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(252, 188, 69, 0.4);
        }

        /* ===== CONTENT LAYOUT ===== */
        .vacancies-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 32px;
            margin-top: -60px;
            position: relative;
            z-index: 10;
        }

        /* ===== SIDEBAR FILTERS ===== */
        .filters-sidebar {
            position: sticky;
            top: 88px;
            height: fit-content;
        }

        .filters-card {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-md);
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--brb-border-light);
        }

        .filters-title {
            font-weight: 700;
            font-size: 18px;
            color: var(--brb-text);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filters-title i {
            color: var(--brb-primary);
        }

        .filters-reset {
            color: var(--brb-primary);
            font-size: 13px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: var(--radius-pill);
            transition: all 0.2s;
        }

        .filters-reset:hover {
            background: var(--brb-primary-light);
        }

        .filter-group {
            margin-bottom: 24px;
        }

        .filter-group:last-child {
            margin-bottom: 0;
        }

        .filter-label {
            font-weight: 600;
            font-size: 13px;
            color: var(--brb-text);
            margin-bottom: 10px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--brb-border);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--brb-text);
            background: var(--brb-bg-white);
            cursor: pointer;
            transition: all 0.25s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236B7280' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .filter-select:hover {
            border-color: #D1D5DB;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--brb-primary);
            box-shadow: 0 0 0 4px var(--brb-primary-light);
        }

        /* Company Info Card */
        .info-card {
            background: linear-gradient(135deg, var(--brb-secondary) 0%, #16213e 100%);
            border-radius: var(--radius-lg);
            padding: 24px;
            margin-top: 20px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(252, 188, 69, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .info-card-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }

        .info-card-title i {
            color: var(--brb-accent);
        }

        .info-card-desc {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            line-height: 1.6;
            margin-bottom: 16px;
            position: relative;
        }

        .info-card-stats {
            position: relative;
        }

        .info-stat {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            margin-bottom: 10px;
        }

        .info-stat:last-child {
            margin-bottom: 0;
        }

        .info-stat i {
            color: var(--brb-accent);
            width: 18px;
        }

        .info-stat a {
            color: var(--brb-accent);
        }

        /* ===== RESULTS ===== */
        .results-section {
            position: relative;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            background: var(--brb-bg-white);
            padding: 18px 24px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--brb-border);
            box-shadow: var(--shadow-sm);
        }

        .results-count {
            font-size: 14px;
            color: var(--brb-text-secondary);
        }

        .results-count strong {
            color: var(--brb-text);
            font-weight: 700;
        }

        .results-sort {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .results-sort span {
            font-size: 14px;
            color: var(--brb-text-secondary);
        }

        .sort-select {
            padding: 8px 14px;
            border: 2px solid var(--brb-border);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--brb-text);
            background: var(--brb-bg-white);
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sort-select:hover {
            border-color: var(--brb-primary);
        }

        /* ===== VACANCY CARD - hh.ru + FlexJob hybrid ===== */
        .vacancy-item {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            padding: 28px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .vacancy-item::before {
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

        .vacancy-item:hover {
            border-color: var(--brb-primary);
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .vacancy-item:hover::before {
            opacity: 1;
        }

        .vacancy-item.featured {
            border-color: var(--brb-accent);
            background: linear-gradient(135deg, var(--brb-accent-light) 0%, var(--brb-bg-white) 100%);
        }

        .vacancy-item.featured::before {
            background: var(--brb-accent);
            opacity: 1;
        }

        .vacancy-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 16px;
        }

        .vacancy-item-salary {
            color: var(--brb-success);
            font-size: 22px;
            font-weight: 800;
            white-space: nowrap;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .vacancy-item-salary small {
            font-size: 12px;
            font-weight: 500;
            color: var(--brb-text-muted);
        }

        .vacancy-item-title {
            font-size: 22px;
            font-weight: 700;
            color: var(--brb-text);
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .vacancy-item-title a {
            color: inherit;
            transition: color 0.2s;
        }

        .vacancy-item-title a:hover {
            color: var(--brb-primary);
        }

        .vacancy-item-company {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .company-logo {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--brb-primary) 0%, var(--brb-primary-dark) 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(214, 0, 28, 0.25);
        }

        .company-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .company-name {
            font-weight: 600;
            font-size: 15px;
            color: var(--brb-text);
        }

        .company-location {
            font-size: 14px;
            color: var(--brb-text-secondary);
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .company-location i {
            font-size: 14px;
        }

        .vacancy-item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
            padding: 16px 0;
            border-top: 1px solid var(--brb-border-light);
            border-bottom: 1px solid var(--brb-border-light);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: var(--brb-text-secondary);
        }

        .meta-item i {
            font-size: 16px;
            color: var(--brb-text-muted);
        }

        .vacancy-item-desc {
            color: var(--brb-text-secondary);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .vacancy-item-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }

        .skill-badge {
            background: var(--brb-bg);
            color: var(--brb-text-secondary);
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .skill-badge.required {
            background: var(--brb-primary-light);
            color: var(--brb-primary);
        }

        .skill-badge:hover {
            transform: translateY(-1px);
        }

        .vacancy-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .vacancy-footer-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .vacancy-date {
            font-size: 13px;
            color: var(--brb-text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .vacancy-badges {
            display: flex;
            gap: 8px;
        }

        .vacancy-badge {
            padding: 6px 12px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 600;
        }

        .vacancy-badge.new {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .vacancy-badge.hot {
            background: #FEF3C7;
            color: #92400E;
        }

        .btn-apply {
            background: transparent;
            color: var(--brb-primary);
            padding: 12px 24px;
            border: 2px solid var(--brb-primary);
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-apply:hover {
            background: var(--brb-primary);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(214, 0, 28, 0.25);
        }

        /* ===== EMPTY STATE ===== */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            background: var(--brb-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
        }

        .empty-state-icon i {
            font-size: 36px;
            color: var(--brb-text-muted);
        }

        .empty-state h3 {
            font-size: 22px;
            font-weight: 700;
            color: var(--brb-text);
            margin-bottom: 8px;
        }

        .empty-state p {
            color: var(--brb-text-secondary);
            margin-bottom: 28px;
            font-size: 15px;
        }

        /* ===== PAGINATION ===== */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination {
            display: flex;
            gap: 8px;
        }

        .pagination .page-link {
            padding: 10px 16px;
            border-radius: var(--radius-md);
            border: 2px solid var(--brb-border);
            color: var(--brb-text);
            font-weight: 500;
            transition: all 0.2s;
        }

        .pagination .page-link:hover {
            border-color: var(--brb-primary);
            color: var(--brb-primary);
        }

        .pagination .page-item.active .page-link {
            background: var(--brb-primary);
            border-color: var(--brb-primary);
            color: #fff;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .vacancies-layout {
                grid-template-columns: 1fr;
                margin-top: -40px;
            }

            .filters-sidebar {
                position: static;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
            }

            .info-card {
                margin-top: 0;
            }

            .search-form {
                flex-direction: column;
            }

            .search-field {
                padding: 0 16px;
            }

            .search-submit {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 40px 0 80px;
            }

            .page-title {
                font-size: 28px;
            }

            .page-header-top {
                flex-direction: column;
                gap: 16px;
            }

            .filters-sidebar {
                grid-template-columns: 1fr;
            }

            .vacancy-item-header {
                flex-direction: column;
                gap: 12px;
            }

            .vacancy-item-salary {
                align-items: flex-start;
            }

            .vacancy-item-footer {
                flex-direction: column;
                gap: 16px;
                align-items: stretch;
            }

            .vacancy-footer-left {
                flex-wrap: wrap;
            }

            .btn-apply {
                justify-content: center;
            }

            .results-header {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
        }
    </style>
    @endpush

    <!-- Page Header with Search -->
    <section class="page-header">
        <div class="container-brb">
            <div class="page-header-content">
                <div class="page-header-top">
                    <div>
                        <h1 class="page-title">Вакансии BRB Bank</h1>
                        <p class="page-stats">
                            <span class="page-stats-badge">
                                <i class="bi bi-briefcase-fill"></i>
                                <strong>{{ $vacancies->total() }}</strong> {{ trans_choice('вакансия|вакансии|вакансий', $vacancies->total()) }}
                            </span>
                        </p>
                    </div>
                </div>

                <form action="{{ route('vacant.index') }}" method="GET" class="search-form">
                    <div class="search-field">
                        <i class="bi bi-search"></i>
                        <input type="text"
                               name="search"
                               placeholder="Должность или ключевые слова"
                               value="{{ request('search') }}">
                    </div>
                    <div class="search-field">
                        <i class="bi bi-geo-alt"></i>
                        <input type="text"
                               name="location"
                               placeholder="Город"
                               value="{{ request('location') }}">
                    </div>
                    <button type="submit" class="search-submit">
                        <i class="bi bi-search"></i>
                        Найти вакансии
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container-brb" style="padding-bottom: 60px;">
        <div class="vacancies-layout">
            <!-- Filters Sidebar -->
            <aside class="filters-sidebar">
                <div class="filters-card">
                    <div class="filters-header">
                        <h3 class="filters-title">
                            <i class="bi bi-sliders"></i>
                            Фильтры
                        </h3>
                        @if(request()->hasAny(['search', 'type', 'location', 'department', 'experience']))
                            <a href="{{ route('vacant.index') }}" class="filters-reset">
                                <i class="bi bi-x-lg"></i>
                                Сбросить
                            </a>
                        @endif
                    </div>

                    <form action="{{ route('vacant.index') }}" method="GET" id="filtersForm">
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <input type="hidden" name="location" value="{{ request('location') }}">

                        <div class="filter-group">
                            <label class="filter-label">Тип занятости</label>
                            <select name="type" class="filter-select" onchange="document.getElementById('filtersForm').submit()">
                                <option value="">Все типы</option>
                                @foreach($employmentTypes as $type)
                                    <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Опыт работы</label>
                            <select name="experience" class="filter-select" onchange="document.getElementById('filtersForm').submit()">
                                <option value="">Любой опыт</option>
                                <option value="0" {{ request('experience') === '0' ? 'selected' : '' }}>Без опыта</option>
                                <option value="1" {{ request('experience') == '1' ? 'selected' : '' }}>От 1 года</option>
                                <option value="3" {{ request('experience') == '3' ? 'selected' : '' }}>От 3 лет</option>
                                <option value="5" {{ request('experience') == '5' ? 'selected' : '' }}>От 5 лет</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label">Отдел</label>
                            <select name="department" class="filter-select" onchange="document.getElementById('filtersForm').submit()">
                                <option value="">Все отделы</option>
                                <option value="IT" {{ request('department') == 'IT' ? 'selected' : '' }}>IT и Разработка</option>
                                <option value="Финансы" {{ request('department') == 'Финансы' ? 'selected' : '' }}>Финансы</option>
                                <option value="Маркетинг" {{ request('department') == 'Маркетинг' ? 'selected' : '' }}>Маркетинг</option>
                                <option value="HR" {{ request('department') == 'HR' ? 'selected' : '' }}>HR</option>
                                <option value="Розница" {{ request('department') == 'Розница' ? 'selected' : '' }}>Розничный бизнес</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Company Info Card -->
                <div class="info-card">
                    <h4 class="info-card-title">
                        <i class="bi bi-building"></i>
                        О BRB Bank
                    </h4>
                    <p class="info-card-desc">
                        Один из ведущих банков Узбекистана с фокусом на инновации и качественное обслуживание клиентов.
                    </p>
                    <div class="info-card-stats">
                        <div class="info-stat">
                            <i class="bi bi-people-fill"></i>
                            <span>1000+ сотрудников</span>
                        </div>
                        <div class="info-stat">
                            <i class="bi bi-geo-alt-fill"></i>
                            <span>50+ филиалов</span>
                        </div>
                        <div class="info-stat">
                            <i class="bi bi-globe"></i>
                            <a href="https://brb.uz" target="_blank">brb.uz</a>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Results -->
            <section class="results-section">
                <div class="results-header">
                    <span class="results-count">
                        Показано <strong>{{ $vacancies->firstItem() ?? 0 }}-{{ $vacancies->lastItem() ?? 0 }}</strong>
                        из <strong>{{ $vacancies->total() }}</strong> вакансий
                    </span>
                    <div class="results-sort">
                        <span>Сортировка:</span>
                        <select class="sort-select" onchange="window.location.href='{{ route('vacant.index') }}?sort='+this.value+'&search={{ request('search') }}&type={{ request('type') }}&location={{ request('location') }}'">
                            <option value="date" {{ request('sort', 'date') == 'date' ? 'selected' : '' }}>По дате</option>
                            <option value="salary" {{ request('sort') == 'salary' ? 'selected' : '' }}>По зарплате</option>
                        </select>
                    </div>
                </div>

                @forelse($vacancies as $index => $vacancy)
                    <article class="vacancy-item {{ $index === 0 && !request()->hasAny(['search', 'type', 'location', 'department', 'experience']) ? 'featured' : '' }}">
                        <div class="vacancy-item-header">
                            <div>
                                <h2 class="vacancy-item-title">
                                    <a href="{{ route('vacant.show', $vacancy) }}">{{ $vacancy->title }}</a>
                                </h2>
                                <div class="vacancy-item-company">
                                    <div class="company-logo">BRB</div>
                                    <div class="company-info">
                                        <span class="company-name">BRB Bank</span>
                                        <span class="company-location">
                                            <i class="bi bi-geo-alt"></i>
                                            {{ $vacancy->location ?? $vacancy->city ?? 'Ташкент' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @if($vacancy->salary_formatted)
                                <div class="vacancy-item-salary">
                                    {{ $vacancy->salary_formatted }}
                                    <small>в месяц</small>
                                </div>
                            @endif
                        </div>

                        <div class="vacancy-item-meta">
                            <span class="meta-item">
                                <i class="bi bi-briefcase"></i>
                                {{ $vacancy->employment_type_label ?? 'Полная занятость' }}
                            </span>
                            @if($vacancy->min_experience_years)
                                <span class="meta-item">
                                    <i class="bi bi-clock-history"></i>
                                    Опыт от {{ $vacancy->min_experience_years }} {{ trans_choice('год|года|лет', $vacancy->min_experience_years) }}
                                </span>
                            @endif
                            @if($vacancy->department)
                                <span class="meta-item">
                                    <i class="bi bi-diagram-3"></i>
                                    {{ $vacancy->department }}
                                </span>
                            @endif
                        </div>

                        <p class="vacancy-item-desc">
                            {{ Str::limit(strip_tags($vacancy->description), 280) }}
                        </p>

                        @if($vacancy->must_have_skills && count($vacancy->must_have_skills))
                            <div class="vacancy-item-skills">
                                @foreach(array_slice($vacancy->must_have_skills, 0, 5) as $skill)
                                    <span class="skill-badge required">{{ $skill }}</span>
                                @endforeach
                                @if(count($vacancy->must_have_skills) > 5)
                                    <span class="skill-badge">+{{ count($vacancy->must_have_skills) - 5 }}</span>
                                @endif
                            </div>
                        @endif

                        <div class="vacancy-item-footer">
                            <div class="vacancy-footer-left">
                                <span class="vacancy-date">
                                    <i class="bi bi-clock"></i>
                                    {{ $vacancy->created_at->diffForHumans() }}
                                </span>
                                <div class="vacancy-badges">
                                    @if($vacancy->created_at->gt(now()->subDays(3)))
                                        <span class="vacancy-badge new">Новая</span>
                                    @endif
                                    @if($vacancy->is_hot ?? false)
                                        <span class="vacancy-badge hot">Срочно</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('vacant.show', $vacancy) }}" class="btn-apply">
                                Подробнее
                                <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="bi bi-search"></i>
                        </div>
                        <h3>Вакансии не найдены</h3>
                        <p>Попробуйте изменить параметры поиска или сбросить фильтры</p>
                        <a href="{{ route('vacant.index') }}" class="btn-accent">
                            <i class="bi bi-arrow-repeat"></i>
                            Показать все вакансии
                        </a>
                    </div>
                @endforelse

                @if($vacancies->hasPages())
                    <div class="pagination-wrapper">
                        {{ $vacancies->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
