<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки, что пользователь — сотрудник банка
 */
class EnsureUserIsEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->isEmployee()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Access denied',
                    'message' => 'Доступ только для сотрудников банка',
                ], 403);
            }

            abort(403, 'Доступ только для сотрудников банка');
        }

        // Проверяем статус сотрудника (если есть профиль)
        $employeeProfile = $user->employeeProfile;

        if ($employeeProfile && !$employeeProfile->status->canLogin()) {
            auth()->logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Account disabled',
                    'message' => 'Ваш аккаунт деактивирован. Обратитесь в HR отдел.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Ваш аккаунт деактивирован. Обратитесь в HR отдел.');
        }

        return $next($request);
    }
}
