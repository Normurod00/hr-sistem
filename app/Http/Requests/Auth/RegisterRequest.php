<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Введите ваше имя',
            'name.max' => 'Имя не должно превышать 255 символов',
            'email.required' => 'Введите email',
            'email.email' => 'Введите корректный email',
            'email.unique' => 'Этот email уже зарегистрирован',
            'password.required' => 'Введите пароль',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }

    public function getUserData(): array
    {
        return [
            'name' => $this->input('name'),
            'email' => $this->input('email'),
            'phone' => $this->input('phone'),
            'password' => $this->input('password'),
            'role' => UserRole::Candidate, // По умолчанию регистрируются кандидаты
        ];
    }
}
