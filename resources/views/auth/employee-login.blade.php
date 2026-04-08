@extends('layouts.guest')

@section('title', 'Вход для сотрудников')
@section('auth-title', 'Вход для сотрудников')
@section('auth-subtitle', 'Введите ваши учётные данные')

@section('content')
    @if($errors->any())
        <div class="alert alert-danger" style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px; color: #DC2626;">
                <i class="bi bi-exclamation-circle-fill"></i>
                <span style="font-weight: 500;">{{ $errors->first() }}</span>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
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
                    placeholder="employee@hr-system.uz"
                    style="width: 100%; padding: 14px 14px 14px 44px; border: 1px solid #D1D5DB; border-radius: 10px; font-size: 15px; transition: all 0.2s; box-sizing: border-box;"
                    onfocus="this.style.borderColor='#059669'; this.style.boxShadow='0 0 0 3px rgba(5, 150, 105, 0.1)';"
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
                    onfocus="this.style.borderColor='#059669'; this.style.boxShadow='0 0 0 3px rgba(5, 150, 105, 0.1)';"
                    onblur="this.style.borderColor='#D1D5DB'; this.style.boxShadow='none';"
                >
                <button type="button" onclick="togglePassword()" style="position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9CA3AF; cursor: pointer; padding: 0;">
                    <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
            </div>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                <input type="checkbox" name="remember" style="width: 16px; height: 16px; accent-color: #059669;">
                <span style="font-size: 14px; color: #6B7280;">Запомнить меня</span>
            </label>
        </div>

        <button type="submit" style="width: 100%; padding: 14px 24px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(5, 150, 105, 0.4)';"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(5, 150, 105, 0.3)';">
            <i class="bi bi-box-arrow-in-right me-2"></i>
            Войти
        </button>
    </form>

    <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #E5E7EB; text-align: center; display: flex; flex-direction: column; gap: 10px;">
        <p style="font-size: 13px; color: #9CA3AF; margin: 0 0 4px 0;">
            <i class="bi bi-building me-1"></i>
            Доступ только для сотрудников
        </p>
        <a href="{{ route('candidate.login') }}" style="font-size: 13px; color: #6B7280; text-decoration: none;">
            <i class="bi bi-person me-1"></i>
            Вход для кандидатов
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
