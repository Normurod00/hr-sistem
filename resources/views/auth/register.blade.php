@extends('layouts.guest')

@section('title', 'Регистрация кандидата')
@section('auth-title', 'Регистрация')
@section('auth-subtitle', 'Создайте аккаунт для подачи заявок на вакансии')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
            @foreach($errors->all() as $error)
                <div style="display: flex; align-items: center; gap: 10px; color: #DC2626; margin-bottom: 4px;">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span style="font-weight: 500;">{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('candidate.register') }}">
        @csrf

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="name" style="display: block; font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px;">
                ФИО <span style="color: #DC2626;">*</span>
            </label>
            <div style="position: relative;">
                <i class="bi bi-person" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 18px;"></i>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    autocomplete="name"
                    placeholder="Иванов Иван Иванович"
                    style="width: 100%; padding: 14px 14px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30, 58, 95, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="email" style="display: block; font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px;">
                Email <span style="color: #DC2626;">*</span>
            </label>
            <div style="position: relative;">
                <i class="bi bi-envelope" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 18px;"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="email"
                    placeholder="candidate@mail.com"
                    style="width: 100%; padding: 14px 14px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30, 58, 95, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="phone" style="display: block; font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px;">
                Телефон
            </label>
            <div style="position: relative;">
                <i class="bi bi-phone" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 18px;"></i>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ old('phone') }}"
                    autocomplete="tel"
                    placeholder="+998 90 123 45 67"
                    style="width: 100%; padding: 14px 14px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30, 58, 95, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 16px;">
            <label for="password" style="display: block; font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px;">
                Пароль <span style="color: #DC2626;">*</span>
            </label>
            <div style="position: relative;">
                <i class="bi bi-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 18px;"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Минимум 8 символов"
                    style="width: 100%; padding: 14px 44px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30, 58, 95, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
                <button type="button" onclick="togglePassword('password', 'toggleIcon1')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 0;">
                    <i class="bi bi-eye" id="toggleIcon1"></i>
                </button>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label for="password_confirmation" style="display: block; font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px;">
                Подтверждение пароля <span style="color: #DC2626;">*</span>
            </label>
            <div style="position: relative;">
                <i class="bi bi-lock-fill" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 18px;"></i>
                <input
                    type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Повторите пароль"
                    style="width: 100%; padding: 14px 44px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30, 58, 95, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
                <button type="button" onclick="togglePassword('password_confirmation', 'toggleIcon2')" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 0;">
                    <i class="bi bi-eye" id="toggleIcon2"></i>
                </button>
            </div>
        </div>

        <button type="submit" style="width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #1e3a5f 0%, #152a47 100%); color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(30, 58, 95, 0.3);"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(30, 58, 95, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(30, 58, 95, 0.3)';">
            <i class="bi bi-person-plus me-2"></i>
            Зарегистрироваться
        </button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <p style="font-size: 14px; color: #6B7280; margin: 0;">
            Уже есть аккаунт?
            <a href="{{ route('candidate.login') }}" style="color: #1e3a5f; font-weight: 600; text-decoration: none;">Войти</a>
        </p>
    </div>

    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #E5E7EB; text-align: center; display: flex; flex-direction: column; gap: 10px;">
        <a href="{{ route('login') }}" style="font-size: 13px; color: #6B7280; text-decoration: none;">
            <i class="bi bi-briefcase me-1"></i>
            Вход для сотрудников BRB
        </a>
    </div>
@endsection

@section('scripts')
<script>
    function togglePassword(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
</script>
@endsection
