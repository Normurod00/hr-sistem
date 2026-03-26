<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CandidateAuthController extends Controller
{
    /**
     * Показать форму входа для кандидатов
     */
    public function showLoginForm(): View
    {
        return view('auth.candidate-login');
    }

    /**
     * Обработка входа кандидата (email/пароль)
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Проверяем что пользователь - кандидат
        if ($user->role !== \App\Enums\UserRole::Candidate) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('candidate.login')->withErrors([
                'email' => 'Этот аккаунт не является аккаунтом кандидата. Используйте вход для сотрудников.',
            ]);
        }

        return redirect()->intended(route('vacant.index'))
            ->with('success', 'Добро пожаловать, ' . $user->name . '!');
    }
}
