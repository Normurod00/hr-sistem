<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    /**
     * Показать форму регистрации
     */
    public function showRegistrationForm(): View
    {
        return view('auth.register');
    }

    /**
     * Обработка регистрации
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $userData = $request->getUserData();
        $userData['password'] = Hash::make($userData['password']);

        $user = new User($userData);
        $user->role = \App\Enums\UserRole::Candidate;
        $user->save();

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('vacant.index')
            ->with('success', 'Регистрация успешна! Добро пожаловать в BRB Careers.');
    }
}
