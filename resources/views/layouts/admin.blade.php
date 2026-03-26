<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Панель управления') - BRB HR</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />

    <!-- Admin CSS -->
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet" />

    @stack('styles')
</head>

<body class="theme-light">
    <script>
        // Apply saved theme immediately (avoid FOUC)
        (function () {
            const saved = localStorage.getItem('hr-theme');
            if (saved === 'dark') {
                document.body.classList.remove('theme-light');
                document.body.classList.add('theme-dark');
            }
        })();
    </script>

    <!-- Toast Notification -->
    @if(session('success') || session('error'))
    @php
        $toastType = session('success') ? 'success' : 'error';
        $toastMessage = session('success') ?? session('error');
        $toastTitle = session('success') ? 'Успешно!' : 'Ошибка!';
        $toastIcon = session('success') ? 'fa-circle-check' : 'fa-circle-xmark';
    @endphp
    <div id="adminToast" class="admin-toast {{ $toastType }}">
        <div class="admin-toast__content">
            <div class="admin-toast__icon">
                <i class="fa-solid {{ $toastIcon }}"></i>
            </div>
            <div class="admin-toast__message">
                <div class="admin-toast__title">{{ $toastTitle }}</div>
                <div class="admin-toast__text">{{ $toastMessage }}</div>
            </div>
            <button class="admin-toast__close" onclick="closeToast()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="admin-toast__progress"></div>
    </div>
    @endif

    <div id="layoutSidenav">
        <!-- Sidebar -->
        @include('layouts.admin-sidebar')

        <div id="layoutSidenav_content">
            <main>
                <!-- Page Header -->
                @hasSection('header')
                <div class="page-header">
                    <h1>@yield('header')</h1>
                    @hasSection('header-actions')
                    <div class="page-header-actions">
                        @yield('header-actions')
                    </div>
                    @endif
                </div>
                @endif

                @yield('content')
            </main>

            <!-- Footer -->
            <footer class="admin-footer">
                &copy; {{ date('Y') }} BRB Bank HR System. Все права защищены.
            </footer>
        </div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Scripts -->
    <script>
        // Toast notification
        function closeToast() {
            const toast = document.getElementById('adminToast');
            if (toast) {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 400);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-close toast
            const toast = document.getElementById('adminToast');
            if (toast) {
                setTimeout(() => closeToast(), 3000);
            }

            // Sidebar toggle
            const body = document.body;
            const railBtn = document.getElementById('sidebarToggleRail');
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const overlay = document.getElementById('sidebarOverlay');
            const keyMini = 'hr-sidebar-mini';

            // Apply saved sidebar state
            if (localStorage.getItem(keyMini) === '1') {
                body.classList.add('sidebar-mini');
            }

            function updateChevron() {
                const mini = body.classList.contains('sidebar-mini');
                const icon = railBtn?.querySelector('i');
                if (icon) {
                    icon.className = mini ? 'fa-solid fa-chevron-right' : 'fa-solid fa-chevron-left';
                }
            }

            // Rail toggle (collapse/expand)
            if (railBtn) {
                railBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    body.classList.toggle('sidebar-mini');
                    localStorage.setItem(keyMini, body.classList.contains('sidebar-mini') ? '1' : '0');
                    updateChevron();
                });
            }

            // Mobile menu toggle
            if (mobileBtn) {
                mobileBtn.addEventListener('click', () => {
                    body.classList.toggle('sidebar-open');
                    if (overlay) {
                        overlay.toggleAttribute('hidden', !body.classList.contains('sidebar-open'));
                    }
                });
            }

            // Overlay click
            if (overlay) {
                overlay.addEventListener('click', () => {
                    body.classList.remove('sidebar-open');
                    overlay.setAttribute('hidden', '');
                });
            }

            // ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && body.classList.contains('sidebar-open')) {
                    body.classList.remove('sidebar-open');
                    if (overlay) overlay.setAttribute('hidden', '');
                }
            });

            updateChevron();

            // User menu
            const userBtn = document.getElementById('userMenuBtn');
            const userMenu = document.getElementById('userMenu');

            if (userBtn && userMenu) {
                userBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const expanded = userBtn.getAttribute('aria-expanded') === 'true';
                    userBtn.setAttribute('aria-expanded', !expanded);
                    userMenu.hidden = expanded;
                });

                document.addEventListener('click', (e) => {
                    if (!userMenu.hidden && !userBtn.contains(e.target) && !userMenu.contains(e.target)) {
                        userBtn.setAttribute('aria-expanded', 'false');
                        userMenu.hidden = true;
                    }
                });
            }
        });

        // Theme toggle
        function toggleTheme() {
            const body = document.body;
            const isDark = body.classList.contains('theme-dark');

            body.classList.toggle('theme-dark', !isDark);
            body.classList.toggle('theme-light', isDark);

            localStorage.setItem('hr-theme', isDark ? 'light' : 'dark');

            // Update icon
            const icon = document.getElementById('themeIcon');
            const text = document.getElementById('themeText');
            if (icon) {
                icon.className = isDark ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
            }
            if (text) {
                text.textContent = isDark ? 'Тёмная тема' : 'Светлая тема';
            }
        }
    </script>

    @stack('scripts')
</body>
</html>
