<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для аудита действий сотрудника
 *
 * Использование: ->middleware('audit:view,kpi_snapshot')
 */
class AuditEmployeeAction
{
    public function handle(Request $request, Closure $next, string $action, string $resourceType): Response
    {
        $response = $next($request);

        // Логируем только успешные запросы
        if ($response->isSuccessful() && auth()->check()) {
            $resourceId = $request->route('id')
                ?? $request->route('snapshot')
                ?? $request->route('conversation')
                ?? $request->route('policy')
                ?? null;

            AuditLog::log(
                $action,
                $resourceType,
                $resourceId ? (int) $resourceId : null,
                null,
                null,
                [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]
            );
        }

        return $response;
    }
}
