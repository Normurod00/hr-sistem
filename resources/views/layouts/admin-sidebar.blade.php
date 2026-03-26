{{-- Admin Sidebar --}}
<aside id="sidebar" class="ui-sidebar" aria-label="Sidebar">
    <div id="sidebarOverlay" class="ui-sidebar__overlay" hidden></div>

    <div class="ui-sidebar__content">
        <div class="ui-sidebar__head">
            <a href="{{ route('admin.dashboard') }}" class="brand">
                <span class="brand__logo">HR</span>
                <span class="brand__title"><span>BRB</span> HR</span>
            </a>

            <button id="sidebarToggleRail" class="ui-toggle" type="button" aria-label="Свернуть/развернуть">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
        </div>

        <nav class="ui-menu">
            <div class="ui-menu__section">Главная</div>

            <a class="ui-menu__item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"
               href="{{ route('admin.dashboard') }}" data-tooltip="Дашборд">
                <i class="fa-solid fa-chart-line"></i>
                <span>Дашборд</span>
            </a>

            <div class="ui-menu__section">Рекрутинг</div>

            {{-- Vacancies --}}
            <details class="ui-group" {{ request()->routeIs('admin.vacancies.*') ? 'open' : '' }} data-tooltip="Вакансии">
                <summary class="ui-group__summary">
                    <i class="fa-solid fa-briefcase"></i>
                    <span>Вакансии</span>
                    <i class="chev fa-solid fa-chevron-down"></i>
                </summary>
                <div class="ui-submenu">
                    <a class="ui-submenu__item {{ request()->routeIs('admin.vacancies.index') ? 'is-active' : '' }}"
                       href="{{ route('admin.vacancies.index') }}">
                        <i class="fa-solid fa-list"></i><span>Все вакансии</span>
                    </a>
                    <a class="ui-submenu__item {{ request()->routeIs('admin.vacancies.create') ? 'is-active' : '' }}"
                       href="{{ route('admin.vacancies.create') }}">
                        <i class="fa-solid fa-plus"></i><span>Создать вакансию</span>
                    </a>
                </div>
            </details>

            {{-- Applications --}}
            <a class="ui-menu__item {{ request()->routeIs('admin.applications.*') ? 'is-active' : '' }}"
               href="{{ route('admin.applications.index') }}" data-tooltip="Заявки">
                <i class="fa-solid fa-file-lines"></i>
                <span>Заявки кандидатов</span>
            </a>

            {{-- Qualified Candidates --}}
            @php
                $qualifiedCount = \App\Models\Application::whereNotNull('match_score')
                    ->where('match_score', '>=', 50)
                    ->whereIn('status', [\App\Enums\ApplicationStatus::New, \App\Enums\ApplicationStatus::InReview])
                    ->count();
            @endphp
            <a class="ui-menu__item {{ request()->routeIs('admin.qualified.*') ? 'is-active' : '' }}"
               href="{{ route('admin.qualified.index') }}" data-tooltip="Подходящие">
                <i class="fa-solid fa-user-check"></i>
                <span>Подходящие</span>
                @if($qualifiedCount > 0)
                    <span class="ui-menu__badge">{{ $qualifiedCount }}</span>
                @endif
            </a>

            {{-- Chat --}}
            @php
                $unreadChats = \App\Models\ChatRoom::active()
                    ->get()
                    ->sum(fn($chat) => $chat->unreadCountFor(auth()->id()));
            @endphp
            <a class="ui-menu__item {{ request()->routeIs('admin.chat.*') ? 'is-active' : '' }}"
               href="{{ route('admin.chat.index') }}" data-tooltip="Чаты">
                <i class="fa-solid fa-comments"></i>
                <span>Чаты с кандидатами</span>
                @if($unreadChats > 0)
                    <span class="ui-menu__badge">{{ $unreadChats }}</span>
                @endif
            </a>

            {{-- Video Meetings --}}
            @php
                $upcomingMeetings = \App\Models\VideoMeeting::where('status', 'scheduled')
                    ->where('scheduled_at', '>=', now())
                    ->where('scheduled_at', '<=', now()->addDay())
                    ->count();
            @endphp
            <a class="ui-menu__item {{ request()->routeIs('admin.meetings.*') ? 'is-active' : '' }}"
               href="{{ route('admin.meetings.index') }}" data-tooltip="Видеовстречи">
                <i class="fa-solid fa-video"></i>
                <span>Видеовстречи</span>
                @if($upcomingMeetings > 0)
                    <span class="ui-menu__badge">{{ $upcomingMeetings }}</span>
                @endif
            </a>

            <div class="ui-menu__section">Управление</div>

            {{-- Users --}}
            <details class="ui-group" {{ request()->routeIs('admin.users.*') ? 'open' : '' }} data-tooltip="Пользователи">
                <summary class="ui-group__summary">
                    <i class="fa-solid fa-users"></i>
                    <span>Пользователи</span>
                    <i class="chev fa-solid fa-chevron-down"></i>
                </summary>
                <div class="ui-submenu">
                    <a class="ui-submenu__item {{ request()->routeIs('admin.users.index') ? 'is-active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <i class="fa-solid fa-list"></i><span>Все пользователи</span>
                    </a>
                    <a class="ui-submenu__item {{ request()->routeIs('admin.users.create') ? 'is-active' : '' }}"
                       href="{{ route('admin.users.create') }}">
                        <i class="fa-solid fa-user-plus"></i><span>Добавить</span>
                    </a>
                </div>
            </details>

            <div class="ui-menu__section">AI Робот</div>

            {{-- AI Settings --}}
            <a class="ui-menu__item {{ request()->routeIs('admin.ai.settings') ? 'is-active' : '' }}"
               href="{{ route('admin.ai.settings') }}" data-tooltip="Настройки AI">
                <i class="fa-solid fa-robot"></i>
                <span>Настройки AI</span>
            </a>

            {{-- AI Logs --}}
            <a class="ui-menu__item {{ request()->routeIs('admin.ai.logs') ? 'is-active' : '' }}"
               href="{{ route('admin.ai.logs') }}" data-tooltip="Логи AI">
                <i class="fa-solid fa-scroll"></i>
                <span>Логи AI</span>
            </a>

            <div class="ui-menu__section">Быстрые ссылки</div>

            {{-- View Site --}}
            <a class="ui-menu__item" href="{{ route('home') }}" target="_blank" data-tooltip="Открыть сайт">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Открыть сайт</span>
            </a>
        </nav>
    </div>

    @php
        $user = auth()->user();
        $roleName = $user->role->label();
        $initial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1));
    @endphp

    <footer class="ui-user">
        <button class="ui-user__btn" type="button" id="userMenuBtn" aria-haspopup="true" aria-expanded="false"
                aria-controls="userMenu">
            <span class="ui-user__avatar" aria-hidden="true">{{ $initial }}</span>
            <span class="ui-user__meta">
                <span class="ui-user__name">{{ $user->name }}</span>
                <span class="ui-user__role">{{ $roleName }}</span>
            </span>
            <i class="fa-solid fa-chevron-up ui-user__chev" aria-hidden="true"></i>
        </button>

        <div class="ui-user__menu" id="userMenu" hidden>
            {{-- Theme Toggle --}}
            <button class="ui-user__menu-item" onclick="toggleTheme()">
                <i id="themeIcon" class="fa-solid fa-moon"></i>
                <span id="themeText">Тёмная тема</span>
            </button>

            {{-- Profile --}}
            <a class="ui-user__menu-item" href="{{ route('profile.show') }}">
                <i class="fa-solid fa-user"></i>
                <span>Мой профиль</span>
            </a>

            {{-- Logout --}}
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="ui-user__menu-item ui-user__logout" type="submit">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Выйти</span>
                </button>
            </form>
        </div>
    </footer>
</aside>

<script>
    // Initialize theme icon on load
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.body.classList.contains('theme-dark');
        const icon = document.getElementById('themeIcon');
        const text = document.getElementById('themeText');

        if (icon && text) {
            icon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            text.textContent = isDark ? 'Светлая тема' : 'Тёмная тема';
        }
    });
</script>
