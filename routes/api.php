<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AiGatewayController;
use App\Http\Controllers\Employee\EmployeeKpiController;
use App\Http\Controllers\Employee\EmployeeChatController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Маршруты для клиентского приложения (мобильное приложение, SPA)
| Все маршруты имеют префикс /api
|
*/

// Публичные маршруты (без авторизации)
Route::prefix('auth')->group(function () {
    // Авторизация (email/пароль)
    Route::post('/login', [AuthController::class, 'login']);
});

// Защищённые маршруты (требуют api_token)
Route::middleware('auth.api')->group(function () {
    // Текущий пользователь
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Выход
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // TODO: Добавить другие API endpoints
    // Route::get('/vacancies', [VacancyController::class, 'index']);
    // Route::get('/vacancies/{id}', [VacancyController::class, 'show']);
    // Route::post('/applications', [ApplicationController::class, 'store']);
    // Route::get('/profile', [ProfileController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| AI Gateway API Routes
|--------------------------------------------------------------------------
|
| Универсальные эндпоинты для AI (кандидаты и сотрудники)
|
*/

// Health check (публичный)
Route::get('/ai/health', [AiGatewayController::class, 'health']);

// Защищённые AI маршруты
Route::middleware('auth:sanctum')->prefix('ai')->group(function () {
    Route::post('/chat', [AiGatewayController::class, 'chat']);
    Route::post('/explain', [AiGatewayController::class, 'explain']);
    Route::post('/analyze', [AiGatewayController::class, 'analyze']);
});

/*
|--------------------------------------------------------------------------
| Employee API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'employee'])->prefix('employee')->name('api.employee.')->group(function () {
    // KPI
    Route::get('/kpi', [EmployeeKpiController::class, 'apiIndex'])->name('kpi.index');

    // Conversations
    Route::get('/conversations', function () {
        $employee = auth()->user()->employeeProfile;
        return response()->json([
            'conversations' => $employee->aiConversations()->recent()->limit(20)->get(),
        ]);
    })->name('conversations.index');

    Route::post('/conversations/{conversation}/messages', [EmployeeChatController::class, 'sendMessage'])
        ->name('conversations.message');
});
