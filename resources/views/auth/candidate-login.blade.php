@extends('layouts.guest')

@section('title', 'Вход для кандидатов')
@section('auth-title', 'Вход для кандидатов')
@section('auth-subtitle', 'Войдите в систему или создайте аккаунт')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; color: #DC2626;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span style="font-weight: 500;">{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('candidate.login') }}">
        @csrf

        <div class="form-group" style="margin-bottom: 20px;">
            <label for="email" style="display: block; font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px;">
                Email
            </label>
            <div style="position: relative;">
                <i class="bi bi-envelope" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 18px;"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="candidate@mail.com"
                    style="width: 100%; padding: 14px 14px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30, 58, 95, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label for="password" style="display: block; font-weight: 600; font-size: 14px; color: #374151; margin-bottom: 8px;">
                Пароль
            </label>
            <div style="position: relative;">
                <i class="bi bi-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9CA3AF; font-size: 18px;"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    style="width: 100%; padding: 14px 44px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#1e3a5f'; this.style.boxShadow='0 0 0 3px rgba(30, 58, 95, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
                <button type="button" onclick="togglePassword()" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 0;">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>
        </div>

        <button type="submit" style="width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #1e3a5f 0%, #152a47 100%); color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(30, 58, 95, 0.3);"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(30, 58, 95, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(30, 58, 95, 0.3)';">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            Войти
        </button>
    </form>

    <div style="margin-top: 20px; text-align: center;">
        <p style="font-size: 14px; color: #6B7280; margin: 0 0 12px 0;">Ещё нет аккаунта?</p>
        <a href="{{ route('candidate.register') }}" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px 24px; background: #fff; border: 2px solid #1e3a5f; color: #1e3a5f; border-radius: 10px; font-size: 16px; font-weight: 600; text-decoration: none; transition: all 0.2s;"
           onmouseover="this.style.background='#1e3a5f'; this.style.color='#fff';"
           onmouseout="this.style.background='#fff'; this.style.color='#1e3a5f';">
            <i class="bi bi-person-plus"></i>
            Зарегистрироваться
        </a>
    </div>

    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #E5E7EB; text-align: center; display: flex; flex-direction: column; gap: 10px;">
        <a href="{{ route('login') }}" style="font-size: 13px; color: #6B7280; text-decoration: none;">
            <i class="bi bi-briefcase me-1"></i>
            Вход для сотрудников BRB
        </a>
        <a href="{{ route('admin.login') }}" style="font-size: 13px; color: #6B7280; text-decoration: none;">
            <i class="bi bi-gear me-1"></i>
            Вход для администраторов
        </a>
    </div>
@endsection

@section('scripts')
<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon = document.getElementById('toggleIcon');

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