<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Портал сотрудника') - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brb-primary: #D6001C;
            --brb-accent: #FCBC45;
            --brb-dark: #1A1A2E;
            --brb-success: #00A86B;
            --brb-warning: #FF9500;
            --brb-info: #0066FF;
            --sidebar-width: 260px;
            --sidebar-collapsed: 70px;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f8f9fa;
            min-height: 100vh;
        }

        /* Sidebar */
        .employee-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--brb-dark) 0%, #16213E 100%);
            color: #fff;
            transition: width 0.3s ease;
            z-index: 1000;
            overflow-x: hidden;
        }

        .employee-sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-logo {
            width: 40px;
            height: 40px;
            background: var(--brb-primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }

        .sidebar-title {
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }

        .sidebar-subtitle {
            font-size: 11px;
            opacity: 0.7;
        }

        .collapsed .sidebar-title,
        .collapsed .sidebar-subtitle {
            display: none;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-section-title {
            padding: 0.5rem 1.5rem;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            margin-top: 1rem;
        }

        .collapsed .nav-section-title {
            display: none;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }

        .nav-link.active {
            background: rgba(214, 0, 28, 0.2);
            color: #fff;
            border-left-color: var(--brb-primary);
        }

        .nav-link i {
            font-size: 18px;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }

        .collapsed .nav-link span {
            display: none;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--brb-primary);
            color: #fff;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
        }

        /* Main content */
        .employee-main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar-collapsed .employee-main {
            margin-left: var(--sidebar-collapsed);
        }

        /* Header */
        .employee-header {
            background: #fff;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .toggle-sidebar {
            background: none;
            border: none;
            font-size: 20px;
            color: #6c757d;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .toggle-sidebar:hover {
            background: #f8f9fa;
            color: var(--brb-primary);
        }

        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--brb-dark);
            margin: 0;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-notifications {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--brb-primary);
            color: #fff;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: background 0.2s;
        }

        .user-dropdown:hover {
            background: #f8f9fa;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--brb-primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info {
            text-align: left;
        }

        .user-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--brb-dark);
        }

        .user-role {
            font-size: 11px;
            color: #6c757d;
        }

        /* Content area */
        .employee-content {
            padding: 2rem;
        }

        /* Cards */
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }

        .stat-card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .stat-card-icon.primary { background: rgba(214,0,28,0.1); color: var(--brb-primary); }
        .stat-card-icon.success { background: rgba(0,168,107,0.1); color: var(--brb-success); }
        .stat-card-icon.warning { background: rgba(255,149,0,0.1); color: var(--brb-warning); }
        .stat-card-icon.info { background: rgba(0,102,255,0.1); color: var(--brb-info); }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--brb-dark);
        }

        .stat-label {
            font-size: 13px;
            color: #6c757d;
        }

        .stat-trend {
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .stat-trend.up { color: var(--brb-success); }
        .stat-trend.down { color: var(--brb-primary); }

        /* Buttons */
        .btn-brb {
            background: var(--brb-primary);
            color: #fff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-brb:hover {
            background: #b80018;
            color: #fff;
        }

        .btn-outline-brb {
            background: transparent;
            color: var(--brb-primary);
            border: 1px solid var(--brb-primary);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-outline-brb:hover {
            background: var(--brb-primary);
            color: #fff;
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            .employee-sidebar {
                transform: translateX(-100%);
            }

            .employee-sidebar.mobile-open {
                transform: translateX(0);
            }

            .employee-main {
                margin-left: 0;
            }
        }

        /* Alerts */
        .alert-brb {
            background: rgba(214,0,28,0.1);
            border: 1px solid rgba(214,0,28,0.2);
            color: var(--brb-primary);
            border-radius: 8px;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Sidebar -->
    @include('employee.layouts.sidebar')

    <!-- Main Content -->
    <div class="employee-main">
        <!-- Header -->
        <header class="employee-header">
            <div class="header-left">
                <button class="toggle-sidebar" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <h1 class="page-title">@yield('page-title', 'Дашборд')</h1>
            </div>

            <div class="header-right">
                <div class="header-notifications dropdown">
                    <button class="btn btn-link text-muted" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-3" style="width: 300px;">
                        <h6 class="mb-3">Уведомления</h6>
                        <p class="text-muted small mb-0">Новых уведомлений нет</p>
                    </div>
                </div>

                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown">
                        <div class="user-avatar">
                            {{ auth()->user()->initials }}
                        </div>
                        <div class="user-info d-none d-md-block">
                            <div class="user-name">{{ auth()->user()->name }}</div>
                            <div class="user-role">{{ auth()->user()->employeeProfile?->role?->label() }}</div>
                        </div>
                        <i class="bi bi-chevron-down text-muted"></i>
                    </div>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a href="{{ route('employee.settings') }}" class="dropdown-item">
                            <i class="bi bi-gear me-2"></i> Настройки
                        </a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Выход
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content -->
        <main class="employee-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.querySelector('.employee-sidebar').classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', document.body.classList.contains('sidebar-collapsed'));
        }

        // Restore sidebar state
        if (localStorage.getItem('sidebar-collapsed') === 'true') {
            document.querySelector('.employee-sidebar').classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        }

        // Mobile sidebar
        function toggleMobileSidebar() {
            document.querySelector('.employee-sidebar').classList.toggle('mobile-open');
        }
    </script>

    @stack('scripts')
</body>
</html>
