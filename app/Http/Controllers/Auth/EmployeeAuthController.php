<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployeeAuthController extends Controller
{
    /**
     * Показать форму входа для сотрудников
     */
    public function showLoginForm(): View
    {
        return view('auth.employee-login');
    }

    /**
     * Обработка входа сотрудника (email/пароль)
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Проверяем что пользователь - сотрудник
        if (!$user->role->isEmployee()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Доступ только для сотрудников BRB.',
            ]);
        }

        return redirect()->intended(route('employee.dashboard'))
            ->with('success', 'Добро пожаловать, ' . $user->name . '!');
    }
}
