<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Страница профиля
     */
    public function show(): View
    {
        $user = auth()->user();
        $user->load('candidateProfile');

        $applicationsCount = $user->applications()->count();
        $invitedCount = $user->applications()->where('status', 'invited')->count();

        return view('profile.show', compact('user', 'applicationsCount', 'invitedCount'));
    }

    /**
     * Форма редактирования профиля
     */
    public function edit(): View
    {
        $user = auth()->user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Обновление профиля
     */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ], [
            'name.required' => 'Введите ваше имя',
            'avatar.image' => 'Файл должен быть изображением',
            'avatar.max' => 'Размер изображения не должен превышать 2 МБ',
        ]);

        // Обновляем аватар
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Профиль успешно обновлён.');
    }

    /**
     * Форма смены пароля
     */
    public function editPassword(): View
    {
        return view('profile.password');
    }

    /**
     * Смена пароля
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'Введите текущий пароль',
            'current_password.current_password' => 'Неверный текущий пароль',
            'password.required' => 'Введите новый пароль',
            'password.confirmed' => 'Пароли не совпадают',
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Пароль успешно изменён.');
    }
}
