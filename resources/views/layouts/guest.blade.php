<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Авторизация') — BRB Bank Careers</title>

    <!-- Inter Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --brb-primary: #D6001C;
            --brb-primary-dark: #B80018;
            --brb-primary-light: #FFE8EB;
            --brb-secondary: #1A1A2E;
            --brb-success: #00A86B;
            --brb-text: #1A1A2E;
            --brb-text-secondary: #6B7280;
            --brb-bg: #F8F9FA;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.1);
            --shadow-lg: 0 20px 60px rgba(0,0,0,0.15);
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--brb-secondary) 0%, #16213e 50%, #0f0f23 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative background elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 60%;
            height: 100%;
            background: radial-gradient(circle, rgba(214, 0, 28, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 50%;
            height: 80%;
            background: radial-gradient(circle, rgba(214, 0, 28, 0.08) 0%, transparent 60%);
            pointer-events: none;
        }

        .auth-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .auth-logo a {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-size: 26px;
            font-weight: 700;
            color: #fff;
            transition: transform 0.2s;
        }

        .auth-logo a:hover {
            transform: scale(1.02);
        }

        .auth-logo .logo-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--brb-primary) 0%, var(--brb-primary-dark) 100%);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            box-shadow: 0 4px 20px rgba(214, 0, 28, 0.4);
        }

        .auth-card {
            background: #fff;
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            overflow: hidden;
        }

        .auth-header {
            padding: 36px 36px 28px;
            text-align: center;
            background: linear-gradient(180deg, rgba(248, 249, 250, 0.5) 0%, transparent 100%);
        }

        .auth-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--brb-text);
            margin-bottom: 8px;
        }

        .auth-subtitle {
            font-size: 15px;
            color: var(--brb-text-secondary);
            margin: 0;
        }

        .auth-body {
            padding: 0 36px 36px;
        }

        .alert-danger {
            background: var(--brb-primary-light);
            border: 1px solid rgba(214, 0, 28, 0.2);
            color: var(--brb-primary);
            border-radius: var(--radius-md);
            padding: 14px 18px;
            font-size: 14px;
        }

        .alert-danger i {
            font-size: 16px;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: var(--brb-text);
            margin-bottom: 8px;
        }

        .form-control {
            padding: 14px 16px;
            border: 2px solid #E5E7EB;
            border-radius: var(--radius-md);
            font-size: 15px;
            transition: all 0.2s;
            background: #FAFAFA;
        }

        .form-control:hover {
            border-color: #D1D5DB;
        }

        .form-control:focus {
            background: #fff;
            border-color: var(--brb-primary);
            box-shadow: 0 0 0 4px rgba(214, 0, 28, 0.1);
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        .input-group-text {
            background: #F3F4F6;
            border: 2px solid #E5E7EB;
            border-right: none;
            border-radius: var(--radius-md) 0 0 var(--radius-md);
            padding-left: 16px;
            padding-right: 12px;
            color: var(--brb-text-secondary);
        }

        .input-group .form-control {
            border-radius: 0 var(--radius-md) var(--radius-md) 0;
            border-left: none;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: var(--brb-primary);
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin-top: 2px;
        }

        .form-check-input:checked {
            background-color: var(--brb-primary);
            border-color: var(--brb-primary);
        }

        .form-check-input:focus {
            box-shadow: 0 0 0 3px rgba(214, 0, 28, 0.15);
        }

        .form-check-label {
            font-size: 14px;
            color: var(--brb-text-secondary);
        }

        .btn-primary-brb {
            background: linear-gradient(135deg, var(--brb-primary) 0%, var(--brb-primary-dark) 100%);
            border: none;
            color: #fff;
            padding: 14px 24px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(214, 0, 28, 0.3);
        }

        .btn-primary-brb:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(214, 0, 28, 0.4);
            color: #fff;
        }

        .btn-primary-brb:active {
            transform: translateY(0);
        }

        .btn-outline-brb {
            background: transparent;
            border: 2px solid #E5E7EB;
            color: var(--brb-text);
            padding: 14px 24px;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 16px;
            transition: all 0.2s;
        }

        .btn-outline-brb:hover {
            border-color: var(--brb-primary);
            color: var(--brb-primary);
            background: var(--brb-primary-light);
        }

        .auth-footer {
            padding: 20px 36px;
            background: #F9FAFB;
            text-align: center;
            font-size: 14px;
            border-top: 1px solid #F3F4F6;
        }

        .auth-footer a {
            color: var(--brb-primary);
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            color: #9CA3AF;
            font-size: 13px;
            font-weight: 500;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #E5E7EB;
        }

        .divider span {
            padding: 0 16px;
        }

        .bottom-links {
            text-align: center;
            margin-top: 28px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .bottom-links a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s;
        }

        .bottom-links a:hover {
            color: #fff;
        }

        .bottom-links a i {
            margin-right: 6px;
        }

        /* Security badge */
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            font-size: 12px;
            color: rgba(255,255,255,0.6);
        }

        .security-badge i {
            color: var(--brb-success);
        }

        @media (max-width: 480px) {
            .auth-header,
            .auth-body,
            .auth-footer {
                padding-left: 24px;
                padding-right: 24px;
            }

            .auth-header {
                padding-top: 28px;
                padding-bottom: 20px;
            }

            .auth-title {
                font-size: 24px;
            }

            .auth-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-logo">
                <a href="{{ route('home') }}">
                    <span class="logo-icon">BRB</span>
                    Careers
                </a>
            </div>

            <div class="auth-card">
                <div class="auth-header">
                    <h1 class="auth-title">@yield('auth-title', 'Добро пожаловать')</h1>
                    <p class="auth-subtitle">@yield('auth-subtitle', '')</p>
                </div>

                <div class="auth-body">
                    @if($errors->any())
                        <div class="alert alert-danger mb-4">
                            @foreach($errors->all() as $error)
                                <div><i class="bi bi-exclamation-circle me-2"></i>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    @yield('content')
                </div>

                @hasSection('footer')
                <div class="auth-footer">
                    @yield('footer')
                </div>
                @endif
            </div>

            <div class="bottom-links">
                <a href="{{ route('home') }}">
                    <i class="bi bi-arrow-left"></i>
                    Вернуться на главную
                </a>
                <div class="security-badge">
                    <i class="bi bi-shield-check"></i>
                    Безопасное соединение
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
