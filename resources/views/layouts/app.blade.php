<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Вакансии') — BRB Bank HR</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            /* Primary - BRB Red */
            --brb-primary: #D6001C;
            --brb-primary-dark: #B80018;
            --brb-primary-light: #FFE8EB;
            /* Accent - FlexJob Orange */
            --brb-accent: #FCBC45;
            --brb-accent-dark: #E5A93D;
            --brb-accent-light: #FFF8EB;
            /* Secondary - Dark Navy */
            --brb-secondary: #1A1A2E;
            --brb-secondary-light: #2D2D44;
            /* Status colors */
            --brb-success: #00A86B;
            --brb-warning: #FF9500;
            --brb-info: #0066FF;
            /* Text colors */
            --brb-text: #1A1A2E;
            --brb-text-secondary: #6B7280;
            --brb-text-muted: #9CA3AF;
            /* Background colors */
            --brb-bg: #F8F9FA;
            --brb-bg-white: #FFFFFF;
            /* Border colors */
            --brb-border: #E5E7EB;
            --brb-border-light: #F3F4F6;
            /* Shadows */
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 24px rgba(0,0,0,0.12);
            --shadow-xl: 0 20px 40px rgba(0,0,0,0.15);
            /* Radius - Pill style from FlexJob */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --radius-pill: 50px;
            /* Backwards compatibility */
            --brb-red: var(--brb-primary);
            --brb-red-dark: var(--brb-primary-dark);
            --brb-green: var(--brb-success);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background: var(--brb-bg);
            color: var(--brb-text);
            font-size: 15px;
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        a {
            color: var(--brb-primary);
            text-decoration: none;
            transition: all 0.25s ease;
        }

        a:hover {
            color: var(--brb-primary-dark);
        }

        /* ===== HEADER - FlexJob inspired ===== */
        .site-header {
            background: var(--brb-bg-white);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .site-header.scrolled {
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            height: 72px;
            max-width: 1320px;
            margin: 0 auto;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 48px;
        }

        .site-logo {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 800;
            font-size: 24px;
            color: var(--brb-text);
        }

        .site-logo .logo-brb {
            color: var(--brb-primary);
        }

        .site-logo .logo-hr {
            font-size: 11px;
            font-weight: 700;
            color: #fff;
            background: var(--brb-accent);
            padding: 3px 8px;
            border-radius: var(--radius-sm);
            margin-left: 8px;
            letter-spacing: 0.5px;
        }

        .header-nav {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            color: var(--brb-text);
            font-weight: 500;
            font-size: 15px;
            border-radius: var(--radius-pill);
            transition: all 0.25s ease;
            position: relative;
        }

        .nav-link:hover {
            background: var(--brb-bg);
            color: var(--brb-primary);
        }

        .nav-link.active {
            color: var(--brb-primary);
            background: var(--brb-primary-light);
        }

        .nav-link i {
            font-size: 18px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* ===== BUTTONS - Pill style from FlexJob ===== */
        .btn-primary-brb {
            background: linear-gradient(135deg, var(--brb-primary) 0%, var(--brb-primary-dark) 100%);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(214, 0, 28, 0.25);
        }

        .btn-primary-brb:hover {
            background: linear-gradient(135deg, var(--brb-primary-dark) 0%, #900010 100%);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(214, 0, 28, 0.35);
        }

        .btn-primary-brb:active {
            transform: translateY(0) scale(0.98);
        }

        /* Accent button - Orange */
        .btn-accent {
            background: linear-gradient(135deg, var(--brb-accent) 0%, var(--brb-accent-dark) 100%);
            color: var(--brb-secondary);
            border: none;
            padding: 12px 28px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(252, 188, 69, 0.35);
        }

        .btn-accent:hover {
            background: linear-gradient(135deg, var(--brb-accent-dark) 0%, #D49A35 100%);
            color: var(--brb-secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(252, 188, 69, 0.45);
        }

        .btn-outline-brb {
            background: transparent;
            color: var(--brb-text);
            border: 2px solid var(--brb-border);
            padding: 10px 24px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .btn-outline-brb:hover {
            border-color: var(--brb-primary);
            color: var(--brb-primary);
            background: var(--brb-primary-light);
        }

        .btn-secondary-brb {
            background: var(--brb-secondary);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: var(--radius-pill);
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary-brb:hover {
            background: var(--brb-secondary-light);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .btn-ghost {
            background: transparent;
            color: var(--brb-text-secondary);
            border: none;
            padding: 10px 16px;
            border-radius: var(--radius-pill);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.25s ease;
        }

        .btn-ghost:hover {
            background: var(--brb-bg);
            color: var(--brb-text);
        }

        /* Small buttons */
        .btn-sm {
            padding: 8px 18px;
            font-size: 13px;
        }

        /* Large buttons */
        .btn-lg {
            padding: 16px 36px;
            font-size: 16px;
        }

        /* ===== USER MENU ===== */
        .user-menu {
            position: relative;
        }

        .user-menu-toggle {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 6px 14px 6px 6px;
            border-radius: var(--radius-pill);
            cursor: pointer;
            transition: all 0.25s ease;
            border: 2px solid transparent;
            background: var(--brb-bg);
        }

        .user-menu-toggle:hover {
            border-color: var(--brb-border);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--brb-border);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--brb-text);
            line-height: 1.2;
        }

        .user-role {
            font-size: 12px;
            color: var(--brb-text-muted);
        }

        .dropdown-menu {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 8px;
            min-width: 220px;
            margin-top: 8px;
        }

        .dropdown-item {
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--brb-text);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--brb-bg);
            color: var(--brb-text);
        }

        .dropdown-item i {
            color: var(--brb-text-secondary);
            font-size: 18px;
            width: 20px;
        }

        .dropdown-item.text-danger {
            color: var(--brb-primary);
        }

        .dropdown-item.text-danger i {
            color: var(--brb-primary);
        }

        .dropdown-divider {
            margin: 8px 0;
            border-color: var(--brb-border-light);
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            flex: 1;
        }

        .container-brb {
            max-width: 1320px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ===== CARDS - Enhanced ===== */
        .card-brb {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            padding: 24px;
            transition: all 0.3s ease;
        }

        .card-brb:hover {
            box-shadow: var(--shadow-lg);
            border-color: transparent;
            transform: translateY(-2px);
        }

        /* Job cards - hh.ru inspired */
        .job-card {
            background: var(--brb-bg-white);
            border: 1px solid var(--brb-border);
            border-radius: var(--radius-lg);
            padding: 24px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .job-card::before {
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

        .job-card:hover {
            border-color: var(--brb-primary);
            box-shadow: var(--shadow-lg);
        }

        .job-card:hover::before {
            opacity: 1;
        }

        .job-card-featured {
            border-color: var(--brb-accent);
            background: linear-gradient(135deg, var(--brb-accent-light) 0%, var(--brb-bg-white) 100%);
        }

        .job-card-featured::before {
            background: var(--brb-accent);
            opacity: 1;
        }

        /* ===== ALERTS ===== */
        .alert-brb {
            padding: 16px 20px;
            border-radius: var(--radius-md);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-danger {
            background: var(--brb-primary-light);
            color: var(--brb-primary-dark);
            border: 1px solid #FECACA;
        }

        .alert-info {
            background: #EFF6FF;
            color: #1E40AF;
            border: 1px solid #BFDBFE;
        }

        .alert-warning {
            background: var(--brb-accent-light);
            color: #92400E;
            border: 1px solid #FDE68A;
        }

        /* ===== BADGES - Pill style ===== */
        .badge-brb {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 6px 14px;
            border-radius: var(--radius-pill);
            font-size: 12px;
            font-weight: 600;
        }

        .badge-success {
            background: #ECFDF5;
            color: #065F46;
        }

        .badge-primary {
            background: var(--brb-primary-light);
            color: var(--brb-primary);
        }

        .badge-accent {
            background: var(--brb-accent-light);
            color: #92400E;
        }

        .badge-info {
            background: #EFF6FF;
            color: #1E40AF;
        }

        .badge-warning {
            background: #FFFBEB;
            color: #92400E;
        }

        .badge-secondary {
            background: var(--brb-bg);
            color: var(--brb-text-secondary);
        }

        /* ===== FORMS ===== */
        .form-control-brb {
            border: 2px solid var(--brb-border);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            font-size: 15px;
            transition: all 0.25s ease;
            background: var(--brb-bg-white);
        }

        .form-control-brb:hover {
            border-color: #D1D5DB;
        }

        .form-control-brb:focus {
            outline: none;
            border-color: var(--brb-primary);
            box-shadow: 0 0 0 4px var(--brb-primary-light);
        }

        .form-control-brb::placeholder {
            color: var(--brb-text-muted);
        }

        .form-label-brb {
            font-weight: 600;
            font-size: 14px;
            color: var(--brb-text);
            margin-bottom: 8px;
        }

        /* Search box - FlexJob style */
        .search-box {
            background: var(--brb-bg-white);
            border-radius: var(--radius-xl);
            padding: 8px;
            box-shadow: var(--shadow-lg);
            display: flex;
            gap: 8px;
        }

        .search-box input {
            border: none;
            padding: 14px 20px;
            font-size: 15px;
            flex: 1;
            background: transparent;
        }

        .search-box input:focus {
            outline: none;
        }

        .search-box .btn-primary-brb {
            padding: 14px 32px;
        }

        /* ===== FOOTER - FlexJob inspired ===== */
        .site-footer {
            background: var(--brb-secondary);
            color: #fff;
            padding: 60px 0 24px;
            margin-top: auto;
            position: relative;
            overflow: hidden;
        }

        /* Pattern overlay */
        .site-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: radial-gradient(circle at 20% 80%, rgba(214, 0, 28, 0.08) 0%, transparent 50%),
                              radial-gradient(circle at 80% 20%, rgba(252, 188, 69, 0.06) 0%, transparent 50%);
            pointer-events: none;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 48px;
            margin-bottom: 48px;
            position: relative;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 800;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .footer-brand .logo-brb {
            color: var(--brb-primary);
        }

        .footer-brand .logo-hr {
            font-size: 11px;
            font-weight: 700;
            color: var(--brb-secondary);
            background: var(--brb-accent);
            padding: 3px 8px;
            border-radius: var(--radius-sm);
            margin-left: 8px;
        }

        .footer-desc {
            color: #9CA3AF;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 24px;
        }

        .footer-socials {
            display: flex;
            gap: 12px;
        }

        .footer-social-link {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.08);
            border-radius: var(--radius-md);
            color: #fff;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        .footer-social-link:hover {
            background: var(--brb-accent);
            color: var(--brb-secondary);
            transform: translateY(-3px);
        }

        .footer-title {
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 24px;
            color: #fff;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 14px;
        }

        .footer-links a {
            color: #9CA3AF;
            font-size: 14px;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .footer-links a:hover {
            color: var(--brb-accent);
            transform: translateX(4px);
        }

        .footer-bottom {
            padding-top: 24px;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #6B7280;
            font-size: 13px;
            position: relative;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .header-nav {
                display: none;
            }

            .header-container {
                padding: 0 16px;
                height: 64px;
            }

            .user-info {
                display: none;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .footer-bottom {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }

            .container-brb {
                padding: 0 16px;
            }

            .btn-primary-brb,
            .btn-accent,
            .btn-secondary-brb {
                padding: 10px 20px;
            }
        }

        /* Mobile menu button */
        .mobile-menu-btn {
            display: none;
            background: var(--brb-bg);
            border: none;
            font-size: 22px;
            color: var(--brb-text);
            padding: 10px 12px;
            border-radius: var(--radius-md);
            transition: all 0.25s ease;
        }

        .mobile-menu-btn:hover {
            background: var(--brb-border);
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Selection */
        ::selection {
            background: var(--brb-primary-light);
            color: var(--brb-primary);
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header class="site-header" id="siteHeader">
        <div class="header-container">
            <div class="header-left">
                <a href="{{ url('/') }}" class="site-logo">
                    <span class="logo-brb">BRB</span>Bank
                    <span class="logo-hr">HR</span>
                </a>

                <nav class="header-nav">
                    <a href="{{ route('vacant.index') }}" class="nav-link {{ request()->routeIs('vacant.*') ? 'active' : '' }}">
                        <i class="bi bi-briefcase"></i>
                        Вакансии
                    </a>
                    @auth
                        @if(auth()->user()->isCandidate())
                            <a href="{{ route('profile.applications') }}" class="nav-link {{ request()->routeIs('profile.applications*') ? 'active' : '' }}">
                                <i class="bi bi-file-earmark-text"></i>
                                Мои отклики
                            </a>
                        @endif
                        @if(auth()->user()->canAccessAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                                <i class="bi bi-grid"></i>
                                Админ-панель
                            </a>
                        @endif
                    @endauth
                </nav>
            </div>

            <div class="header-right">
                @guest
                    <a href="{{ route('candidate.login') }}" class="btn-accent">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Войти
                    </a>
                @else
                    <div class="dropdown user-menu">
                        <div class="user-menu-toggle" data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->avatar_url }}" alt="" class="user-avatar">
                            <div class="user-info">
                                <span class="user-name">{{ auth()->user()->name }}</span>
                                <span class="user-role">{{ auth()->user()->role->label() }}</span>
                            </div>
                            <i class="bi bi-chevron-down" style="color: var(--brb-text-muted); font-size: 12px;"></i>
                        </div>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('profile.show') }}">
                                    <i class="bi bi-person"></i>
                                    Мой профиль
                                </a>
                            </li>
                            @if(auth()->user()->isCandidate())
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.applications') }}">
                                        <i class="bi bi-file-earmark-text"></i>
                                        Мои отклики
                                    </a>
                                </li>
                            @endif
                            @if(auth()->user()->canAccessAdmin())
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.dashboard') }}">
                                        <i class="bi bi-grid"></i>
                                        Админ-панель
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right"></i>
                                        Выйти
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endguest

                <button class="mobile-menu-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                    <i class="bi bi-list"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    @if(session('success') || session('error') || session('info') || session('warning'))
        <div class="container-brb" style="margin-top: 16px;">
            @if(session('success'))
                <div class="alert-brb alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-brb alert-danger">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('info'))
                <div class="alert-brb alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    {{ session('info') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="alert-brb alert-warning">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ session('warning') }}
                </div>
            @endif
        </div>
    @endif

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="site-footer">
        <div class="container-brb">
            <div class="footer-grid">
                <div>
                    <div class="footer-brand">
                        <span class="logo-brb">BRB</span>Bank
                        <span class="logo-hr">HR</span>
                    </div>
                    <p class="footer-desc">
                        Присоединяйтесь к команде одного из ведущих банков Узбекистана.
                        Мы создаём возможности для профессионального роста и развития каждого сотрудника.
                    </p>
                    <div class="footer-socials">
                        <a href="https://t.me/brb_uz" class="footer-social-link" target="_blank">
                            <i class="bi bi-telegram"></i>
                        </a>
                        <a href="https://instagram.com/brb.uz" class="footer-social-link" target="_blank">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="https://facebook.com/brb.uz" class="footer-social-link" target="_blank">
                            <i class="bi bi-facebook"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h6 class="footer-title">Соискателям</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('vacant.index') }}">Все вакансии</a></li>
                        <li><a href="{{ route('candidate.login') }}">Войти</a></li>
                        @auth
                            <li><a href="{{ route('profile.applications') }}">Мои отклики</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h6 class="footer-title">О банке</h6>
                    <ul class="footer-links">
                        <li><a href="https://brb.uz" target="_blank">Официальный сайт</a></li>
                        <li><a href="https://brb.uz/about" target="_blank">О компании</a></li>
                        <li><a href="https://brb.uz/contacts" target="_blank">Контакты</a></li>
                    </ul>
                </div>

                <div>
                    <h6 class="footer-title">HR отдел</h6>
                    <ul class="footer-links">
                        <li style="color: #9CA3AF;">
                            <i class="bi bi-geo-alt"></i>
                            г. Ташкент, ул. Шахрисабз, 21
                        </li>
                        <li>
                            <a href="mailto:hr@brb.uz">
                                <i class="bi bi-envelope"></i>
                                hr@brb.uz
                            </a>
                        </li>
                        <li>
                            <a href="tel:+998712000000">
                                <i class="bi bi-telephone"></i>
                                +998 71 200 00 00
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} BRB Bank. Все права защищены.</span>
                <span>Сделано с <i class="bi bi-heart-fill" style="color: var(--brb-primary);"></i> в Узбекистане</span>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Меню</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="d-flex flex-column gap-2">
                <a href="{{ route('vacant.index') }}" class="nav-link {{ request()->routeIs('vacant.*') ? 'active' : '' }}">
                    <i class="bi bi-briefcase"></i>
                    Вакансии
                </a>
                @auth
                    @if(auth()->user()->isCandidate())
                        <a href="{{ route('profile.applications') }}" class="nav-link {{ request()->routeIs('profile.applications*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            Мои отклики
                        </a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="nav-link">
                        <i class="bi bi-person"></i>
                        Мой профиль
                    </a>
                    @if(auth()->user()->canAccessAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="nav-link">
                            <i class="bi bi-grid"></i>
                            Админ-панель
                        </a>
                    @endif
                    <hr>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link text-danger w-100 text-start border-0 bg-transparent">
                            <i class="bi bi-box-arrow-right"></i>
                            Выйти
                        </button>
                    </form>
                @else
                    <hr>
                    <a href="{{ route('candidate.login') }}" class="btn-accent text-center">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Войти
                    </a>
                @endauth
            </nav>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Header scroll effect -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.getElementById('siteHeader');
            let lastScroll = 0;

            window.addEventListener('scroll', function() {
                const currentScroll = window.pageYOffset;

                if (currentScroll > 10) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                lastScroll = currentScroll;
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
