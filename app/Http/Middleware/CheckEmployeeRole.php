<?php

namespace App\Http\Middleware;

use App\Enums\EmployeeRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для проверки роли сотрудника
 *
 * Использование: ->middleware('employee.role:hr,sysadmin')
 */
class CheckEmployeeRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check() || !auth()->user()->isEmployee()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('employee.login');
        }

        $employeeProfile = auth()->user()->employeeProfile;

        if (!$employeeProfile) {
            abort(403, 'Профиль сотрудника не найден');
        }

        // Преобразуем строки в enum
        $allowedRoles = array_map(
            fn($role) => EmployeeRole::tryFrom($role),
            $roles
        );
        $allowedRoles = array_filter($allowedRoles);

        if (!in_array($employeeProfile->role, $allowedRoles)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Недостаточно прав для этого действия',
                ], 403);
            }

            abort(403, 'Недостаточно прав для этого действия');
        }

        return $next($request);
    }
}
