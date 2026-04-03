<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Если у пользователя включён 2FA и он ещё не прошёл проверку в этой сессии,
 * перенаправляет на страницу ввода OTP.
 */
class Enforce2FA
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Если таблица ещё не создана — пропускаем
        try {
            $setting = $user->twoFactorSetting;
        } catch (\Illuminate\Database\QueryException $e) {
            return $next($request);
        }

        // Если 2FA не включён — пропускаем
        if (!$setting || !$setting->isConfirmed()) {
            return $next($request);
        }

        // Если уже верифицирован в сессии — пропускаем
        if ($request->session()->get('2fa_verified')) {
            return $next($request);
        }

        // Не редиректить на саму страницу 2FA (loop prevention)
        if ($request->routeIs('security.2fa.challenge', 'security.2fa.verify', 'logout')) {
            return $next($request);
        }

        return redirect()->route('security.2fa.challenge');
    }
}
