{{-- Employee Sidebar — same design system as admin --}}
<aside id="sidebar" class="ui-sidebar" aria-label="Sidebar">
    <div id="sidebarOverlay" class="ui-sidebar__overlay" hidden></div>

    <div class="ui-sidebar__content">
        <div class="ui-sidebar__head">
            <a href="{{ route('employee.dashboard') }}" class="brand">
                <span class="brand__logo">HR</span>
                <span class="brand__title">HR Portal</span>
            </a>

            <button id="sidebarToggleRail" class="ui-toggle" type="button" aria-label="Свернуть/развернуть">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>

        <nav class="ui-menu">
            <div class="ui-menu__section">Основное</div>

            <a class="ui-menu__item {{ request()->routeIs('employee.dashboard') ? 'is-active' : '' }}"
               href="{{ route('employee.dashboard') }}" data-tooltip="Главная">
                <i class="fa-solid fa-house"></i>
                <span>Главная</span>
            </a>

            <a class="ui-menu__item {{ request()->routeIs('employee.chat.*') ? 'is-active' : '' }}"
               href="{{ route('employee.chat.index') }}" data-tooltip="AI Ассистент">
                <i class="fa-solid fa-robot"></i>
                <span>AI Ассистент</span>
            </a>

            <a class="ui-menu__item {{ request()->routeIs('employee.kpi.*') ? 'is-active' : '' }}"
               href="{{ route('employee.kpi.index') }}" data-tooltip="Мои KPI">
                <i class="fa-solid fa-chart-line"></i>
                <span>Мои KPI</span>
            </a>

            <a class="ui-menu__item {{ request()->routeIs('employee.documents.*') ? 'is-active' : '' }}"
               href="{{ route('employee.documents.index') }}" data-tooltip="Мои документы">
                <i class="fa-solid fa-file-shield"></i>
                <span>Мои документы</span>
            </a>

            {{-- Staff Chat --}}
            @php
                try {
                    $empStaffUnread = \App\Models\StaffChat::where('employee_id', auth()->id())
                        ->withCount(['messages as unread' => fn($q) => $q->where('sender_id', '!=', auth()->id())->whereNull('read_at')])
                        ->get()->sum('unread');
                } catch (\Exception $e) {
                    $empStaffUnread = 0;
                }
            @endphp
            <a class="ui-menu__item {{ request()->routeIs('employee.staff-chat.*') ? 'is-active' : '' }}"
               href="{{ route('employee.staff-chat.index') }}" data-tooltip="Чат с HR">
                <i class="fa-solid fa-comments"></i>
                <span>Чат с HR</span>
                @if($empStaffUnread > 0)
                    <span class="ui-menu__badge">{{ $empStaffUnread }}</span>
                @endif
            </a>

            <div class="ui-menu__section">Компания</div>

            <a class="ui-menu__item {{ request()->routeIs('employee.recognition.*') ? 'is-active' : '' }}"
               href="{{ route('employee.recognition.index') }}" data-tooltip="Признание">
                <i class="fa-solid fa-award"></i>
                <span>Признание</span>
            </a>

            <a class="ui-menu__item {{ request()->routeIs('employee.discipline.*') ? 'is-active' : '' }}"
               href="{{ route('employee.discipline.index') }}" data-tooltip="Дисциплина">
                <i class="fa-solid fa-scale-balanced"></i>
                <span>Дисциплина</span>
            </a>

            <a class="ui-menu__item {{ request()->routeIs('employee.policies.*') ? 'is-active' : '' }}"
               href="{{ route('employee.policies.index') }}" data-tooltip="Политики">
                <i class="fa-solid fa-book"></i>
                <span>Политики</span>
            </a>

            @if(auth()->user()->employeeProfile?->isManager())
            <div class="ui-menu__section">Команда</div>

            <a class="ui-menu__item {{ request()->routeIs('employee.team*') ? 'is-active' : '' }}"
               href="{{ route('employee.team') }}" data-tooltip="Моя команда">
                <i class="fa-solid fa-people-group"></i>
                <span>Моя команда</span>
            </a>
            @endif

            @if(auth()->user()->employeeProfile?->role?->canViewAllEmployees())
            <div class="ui-menu__section">HR</div>

            <a class="ui-menu__item" href="{{ route('admin.dashboard') }}" data-tooltip="HR Панель">
                <i class="fa-solid fa-gauge-high"></i>
                <span>HR Панель</span>
            </a>
            @endif
        </nav>

        <!-- User Footer -->
        <div class="ui-user">
            <button class="ui-user__btn" id="userMenuBtn" aria-expanded="false">
                <span class="ui-user__avatar">{{ auth()->user()->initials }}</span>
                <span class="ui-user__meta">
                    <span class="ui-user__name">{{ auth()->user()->name }}</span>
                    <span class="ui-user__role">{{ auth()->user()->employeeProfile?->role?->label() ?? 'Сотрудник' }}</span>
                </span>
                <i class="fa-solid fa-chevron-up ui-user__chev"></i>
            </button>

            <div class="ui-user__menu" id="userMenu" hidden>
                <button class="ui-user__menu-item" onclick="toggleTheme()">
                    <i class="fa-solid fa-moon" id="themeIcon"></i>
                    <span id="themeText">Тёмная тема</span>
                </button>
                <a class="ui-user__menu-item" href="{{ route('employee.settings') }}">
                    <i class="fa-solid fa-gear"></i>
                    <span>Настройки</span>
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button class="ui-user__menu-item ui-user__logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Выход</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>
