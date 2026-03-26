<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Список audit логов
     */
    public function index(Request $request): View
    {
        $query = AuditLog::with(['user', 'employeeProfile']);

        // Фильтр по действию
        if ($action = $request->query('action')) {
            $query->byAction($action);
        }

        // Фильтр по типу ресурса
        if ($resourceType = $request->query('resource_type')) {
            $query->byResourceType($resourceType);
        }

        // Фильтр по пользователю
        if ($userId = $request->query('user_id')) {
            $query->byUser($userId);
        }

        // Фильтр по дате
        if ($dateFrom = $request->query('date_from')) {
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $logs = $query->orderByDesc('created_at')->paginate(50);

        // Уникальные значения для фильтров
        $actions = AuditLog::select('action')->distinct()->pluck('action');
        $resourceTypes = AuditLog::select('resource_type')->distinct()->pluck('resource_type');

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'actions' => $actions,
            'resourceTypes' => $resourceTypes,
            'filters' => $request->only(['action', 'resource_type', 'user_id', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Детали записи
     */
    public function show(AuditLog $auditLog): View
    {
        $auditLog->load(['user', 'employeeProfile']);

        return view('admin.audit-logs.show', [
            'log' => $auditLog,
        ]);
    }

    /**
     * Экспорт логов
     */
    public function export(Request $request)
    {
        $query = AuditLog::with(['user']);

        // Применяем те же фильтры
        if ($action = $request->query('action')) {
            $query->byAction($action);
        }
        if ($resourceType = $request->query('resource_type')) {
            $query->byResourceType($resourceType);
        }
        if ($userId = $request->query('user_id')) {
            $query->byUser($userId);
        }

        $logs = $query->orderByDesc('created_at')->limit(10000)->get();

        $filename = 'audit_logs_' . now()->format('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');

            // Заголовки
            fputcsv($file, [
                'ID', 'User', 'Action', 'Resource Type', 'Resource ID',
                'IP Address', 'Created At',
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'N/A',
                    $log->action,
                    $log->resource_type,
                    $log->resource_id,
                    $log->ip_address,
                    $log->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
