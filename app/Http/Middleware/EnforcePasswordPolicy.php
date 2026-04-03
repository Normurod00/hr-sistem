<?php

namespace App\Http\Middleware;

use App\Services\Security\PasswordPolicyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Проверяет, не истёк ли пароль. Если да — перенаправляет на смену пароля.
 */
class EnforcePasswordPolicy
{
    public function __construct(
        protected PasswordPolicyService $passwordPolicy,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Не применяем для кандидатов
        if ($user->isCandidate()) {
            return $next($request);
        }

        // Не редиректить на саму страницу смены пароля
        if ($request->routeIs('security.password.expired', 'security.password.update', 'logout')) {
            return $next($request);
        }

        try {
            if ($this->passwordPolicy->isExpired($user)) {
                return redirect()->route('security.password.expired')
                    ->with('warning', 'Ваш пароль истёк. Необходимо сменить пароль.');
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Таблица password_histories ещё не создана — пропускаем
        }

        return $next($request);
    }
}
