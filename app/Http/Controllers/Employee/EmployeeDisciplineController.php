<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DisciplinaryAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeDisciplineController extends Controller
{
    /**
     * Мои дисциплинарные действия (read-only)
     */
    public function index(Request $request): View
    {
        $employee = auth()->user()->employeeProfile;

        $actions = DisciplinaryAction::forEmployee($employee->id)
            ->with(['createdBy'])
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderByDesc('action_date')
            ->paginate(10);

        // Статистика
        $statistics = [
            'total' => DisciplinaryAction::forEmployee($employee->id)->count(),
            'active' => DisciplinaryAction::forEmployee($employee->id)->active()->count(),
            'appealed' => DisciplinaryAction::forEmployee($employee->id)->where('status', 'appealed')->count(),
            'revoked' => DisciplinaryAction::forEmployee($employee->id)->where('status', 'revoked')->count(),
        ];

        // Логируем просмотр
        AuditLog::logView('discipline_list', $employee->id);

        return view('employee.discipline.index', [
            'actions' => $actions,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Просмотр деталей
     */
    public function show(DisciplinaryAction $disciplinaryAction): View
    {
        $employee = auth()->user()->employeeProfile;

        // Проверяем, что это действие принадлежит текущему сотруднику
        if ($disciplinaryAction->employee_profile_id !== $employee->id) {
            abort(403, 'Рухсат йўқ');
        }

        // Логируем просмотр
        AuditLog::logView('discipline_detail', $disciplinaryAction->id);

        return view('employee.discipline.show', [
            'action' => $disciplinaryAction->load(['createdBy', 'approvedBy']),
        ]);
    }

    /**
     * Подтвердить ознакомление
     */
    public function acknowledge(DisciplinaryAction $disciplinaryAction): JsonResponse
    {
        $employee = auth()->user()->employeeProfile;

        if ($disciplinaryAction->employee_profile_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'error' => 'Рухсат йўқ',
            ], 403);
        }

        if ($disciplinaryAction->employee_acknowledged) {
            return response()->json([
                'success' => false,
                'error' => 'Аллақачон тасдиқланган',
            ], 400);
        }

        $disciplinaryAction->acknowledge();

        // Логируем
        AuditLog::logAction('discipline_acknowledge', $disciplinaryAction->id);

        return response()->json([
            'success' => true,
            'message' => 'Танишганлигингиз тасдиқланди',
        ]);
    }

    /**
     * Подать апелляцию
     */
    public function submitAppeal(Request $request, DisciplinaryAction $disciplinaryAction): JsonResponse
    {
        $employee = auth()->user()->employeeProfile;

        if ($disciplinaryAction->employee_profile_id !== $employee->id) {
            return response()->json([
                'success' => false,
                'error' => 'Рухсат йўқ',
            ], 403);
        }

        $request->validate([
            'appeal_text' => 'required|string|min:50|max:5000',
        ]);

        try {
            $disciplinaryAction->submitAppeal($request->input('appeal_text'));

            // Логируем
            AuditLog::logAction('discipline_appeal', $disciplinaryAction->id);

            return response()->json([
                'success' => true,
                'message' => 'Шикоят қабул қилинди',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
