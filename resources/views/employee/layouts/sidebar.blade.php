<aside class="employee-sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">HR</div>
        <div>
            <div class="sidebar-title">HR Portal</div>
            <div class="sidebar-subtitle">Для сотрудников</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-title">Основное</div>

        <a href="{{ route('employee.dashboard') }}" class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house"></i>
            <span>Главная</span>
        </a>

        <a href="{{ route('employee.chat.index') }}" class="nav-link {{ request()->routeIs('employee.chat.*') ? 'active' : '' }}">
            <i class="bi bi-chat-dots"></i>
            <span>AI Ассистент</span>
            @if(($unreadCount ?? 0) > 0)
                <span class="nav-badge">{{ $unreadCount }}</span>
            @endif
        </a>

        <a href="{{ route('employee.kpi.index') }}" class="nav-link {{ request()->routeIs('employee.kpi.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i>
            <span>Мои KPI</span>
        </a>

        <a href="{{ route('employee.recognition.index') }}" class="nav-link {{ request()->routeIs('employee.recognition.*') ? 'active' : '' }}">
            <i class="bi bi-award"></i>
            <span>Эътироф</span>
        </a>

        <a href="{{ route('employee.discipline.index') }}" class="nav-link {{ request()->routeIs('employee.discipline.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i>
            <span>Интизом</span>
        </a>

        <div class="nav-section-title">Информация</div>

        <a href="{{ route('employee.policies.index') }}" class="nav-link {{ request()->routeIs('employee.policies.*') ? 'active' : '' }}">
            <i class="bi bi-file-text"></i>
            <span>Политики</span>
        </a>

        <a href="#" class="nav-link">
            <i class="bi bi-calendar-check"></i>
            <span>Отпуска</span>
            <span class="nav-badge bg-secondary">Скоро</span>
        </a>

        <a href="#" class="nav-link">
            <i class="bi bi-currency-dollar"></i>
            <span>Бонусы</span>
            <span class="nav-badge bg-secondary">Скоро</span>
        </a>

        @if(auth()->user()->employeeProfile?->isManager())
            <div class="nav-section-title">Команда</div>

            <a href="{{ route('employee.team') }}" class="nav-link {{ request()->routeIs('employee.team*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Моя команда</span>
            </a>
        @endif

        @if(auth()->user()->employeeProfile?->role?->canViewAllEmployees())
            <div class="nav-section-title">HR</div>

            <a href="{{ route('admin.dashboard') }}" class="nav-link">
                <i class="bi bi-speedometer2"></i>
                <span>HR Панель</span>
            </a>
        @endif

        <div class="nav-section-title">Аккаунт</div>

        <a href="{{ route('employee.settings') }}" class="nav-link {{ request()->routeIs('employee.settings') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span>Настройки</span>
        </a>
    </nav>
</aside>
