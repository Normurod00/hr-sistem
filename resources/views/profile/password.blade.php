@extends('layouts.app')

@section('title', 'Сменить пароль — BRB Bank')

@section('content')
<style>
    .password-page {
        background: var(--brb-bg);
        min-height: calc(100vh - 200px);
        padding: 40px 0 60px;
    }

    .password-header {
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 20px 0;
    }

    .password-header .breadcrumb {
        margin-bottom: 0;
        font-size: 14px;
    }

    .password-header .breadcrumb a {
        color: var(--brb-primary);
        text-decoration: none;
    }

    .password-card {
        background: #fff;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-md);
        overflow: hidden;
    }

    .password-card-header {
        padding: 28px 32px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .password-card-icon {
        width: 56px;
        height: 56px;
        background: linear-gradient(135deg, var(--brb-primary) 0%, var(--brb-primary-dark) 100%);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 24px;
    }

    .password-card-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--brb-text);
        margin: 0 0 4px;
    }

    .password-card-subtitle {
        font-size: 14px;
        color: var(--brb-text-secondary);
        margin: 0;
    }

    .password-card-body {
        padding: 32px;
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        font-size: 14px;
        color: var(--brb-text);
        margin-bottom: 8px;
    }

    .form-label i {
        color: var(--brb-text-secondary);
    }

    .form-input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #E5E7EB;
        border-radius: var(--radius-md);
        font-size: 15px;
        transition: all 0.2s;
        background: #FAFAFA;
    }

    .form-input:hover {
        border-color: #D1D5DB;
    }

    .form-input:focus {
        outline: none;
        background: #fff;
        border-color: var(--brb-primary);
        box-shadow: 0 0 0 4px rgba(214, 0, 28, 0.1);
    }

    .form-input.is-invalid {
        border-color: var(--brb-primary);
    }

    .invalid-feedback {
        color: var(--brb-primary);
        font-size: 13px;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .password-hint {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        background: #F0F9FF;
        border-radius: var(--radius-md);
        border: 1px solid #BAE6FD;
        margin-bottom: 28px;
    }

    .password-hint i {
        color: #0284C7;
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }

    .password-hint-content {
        font-size: 14px;
        color: #0369A1;
        line-height: 1.5;
    }

    .password-hint-content strong {
        display: block;
        margin-bottom: 4px;
    }

    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
        border-top: 1px solid #f0f0f0;
    }

    .btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        color: var(--brb-text-secondary);
        text-decoration: none;
        font-weight: 500;
        border-radius: var(--radius-md);
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #F3F4F6;
        color: var(--brb-text);
    }

    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 32px;
        background: linear-gradient(135deg, var(--brb-primary) 0%, var(--brb-primary-dark) 100%);
        color: #fff;
        border: none;
        border-radius: var(--radius-md);
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 15px rgba(214, 0, 28, 0.3);
    }

    .btn-save:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(214, 0, 28, 0.4);
    }

    .security-tip {
        background: linear-gradient(135deg, var(--brb-secondary) 0%, #16213e 100%);
        border-radius: var(--radius-lg);
        padding: 24px;
        margin-top: 24px;
        color: #fff;
    }

    .security-tip-title {
        font-weight: 700;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .security-tip-title i {
        color: var(--brb-success);
    }

    .security-tip-list {
        margin: 0;
        padding-left: 20px;
        font-size: 14px;
        opacity: 0.9;
        line-height: 1.8;
    }

    @media (max-width: 768px) {
        .password-card-header,
        .password-card-body {
            padding-left: 20px;
            padding-right: 20px;
        }

        .form-actions {
            flex-direction: column-reverse;
            gap: 12px;
        }

        .btn-save {
            width: 100%;
            justify-content: center;
        }

        .btn-cancel {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Header -->
<div class="password-header">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('profile.show') }}">Профиль</a></li>
                <li class="breadcrumb-item text-muted">Смена пароля</li>
            </ol>
        </nav>
    </div>
</div>

<!-- Main Content -->
<div class="password-page">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5">
                <div class="password-card">
                    <div class="password-card-header">
                        <div class="password-card-icon">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        <div>
                            <h1 class="password-card-title">Смена пароля</h1>
                            <p class="password-card-subtitle">Обновите пароль для безопасности</p>
                        </div>
                    </div>

                    <div class="password-card-body">
                        <div class="password-hint">
                            <i class="bi bi-info-circle-fill"></i>
                            <div class="password-hint-content">
                                <strong>Требования к паролю:</strong>
                                Минимум 8 символов, рекомендуется использовать буквы, цифры и специальные символы.
                            </div>
                        </div>

                        <form action="{{ route('profile.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="current_password" class="form-label">
                                    <i class="bi bi-key"></i>
                                    Текущий пароль
                                </label>
                                <input type="password"
                                       class="form-input @error('current_password') is-invalid @enderror"
                                       id="current_password"
                                       name="current_password"
                                       placeholder="Введите текущий пароль"
                                       required>
                                @error('current_password')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock"></i>
                                    Новый пароль
                                </label>
                                <input type="password"
                                       class="form-input @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="Придумайте новый пароль"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">
                                        <i class="bi bi-exclamation-circle"></i>
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="password_confirmation" class="form-label">
                                    <i class="bi bi-lock-fill"></i>
                                    Подтвердите новый пароль
                                </label>
                                <input type="password"
                                       class="form-input"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Повторите новый пароль"
                                       required>
                            </div>

                            <div class="form-actions">
                                <a href="{{ route('profile.show') }}" class="btn-cancel">
                                    <i class="bi bi-arrow-left"></i>
                                    Отмена
                                </a>
                                <button type="submit" class="btn-save">
                                    <i class="bi bi-check-lg"></i>
                                    Сменить пароль
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="security-tip">
                    <div class="security-tip-title">
                        <i class="bi bi-lightbulb-fill"></i>
                        Советы по безопасности
                    </div>
                    <ul class="security-tip-list">
                        <li>Никогда не сообщайте пароль третьим лицам</li>
                        <li>Используйте уникальный пароль для каждого сервиса</li>
                        <li>Регулярно меняйте пароль (раз в 3-6 месяцев)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
