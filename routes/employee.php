<?php

use App\Http\Controllers\Employee\EmployeeChatController;
use App\Http\Controllers\Employee\EmployeeDisciplineController;
use App\Http\Controllers\Employee\EmployeeKpiController;
use App\Http\Controllers\Employee\EmployeePolicyController;
use App\Http\Controllers\Employee\EmployeePortalController;
use App\Http\Controllers\Employee\RecognitionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Employee Portal Routes
|--------------------------------------------------------------------------
|
| Маршруты портала сотрудника банка.
| Все маршруты защищены middleware 'employee'.
|
*/

/*
|--------------------------------------------------------------------------
| Employee Portal - Корневые маршруты (/)
|--------------------------------------------------------------------------
*/
Route::name('employee.')->group(function () {
    // Страница входа для сотрудников
    Route::get('login', [App\Http\Controllers\Auth\EmployeeAuthController::class, 'showLoginForm'])
        ->name('login')
        ->middleware('guest');

    // Защищённые маршруты
    Route::middleware(['auth', 'employee'])->group(function () {
        // Dashboard - корневой URL
        Route::get('/dashboard', [EmployeePortalController::class, 'index'])
            ->name('dashboard');

        // Настройки
        Route::get('settings', [EmployeePortalController::class, 'settings'])
            ->name('settings');
        Route::put('settings/notifications', [EmployeePortalController::class, 'updateNotifications'])
            ->name('settings.notifications');

        // ===== DOCUMENTS =====
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Employee\DocumentController::class, 'index'])
                ->name('index');
            Route::post('/', [\App\Http\Controllers\Employee\DocumentController::class, 'store'])
                ->name('store');
        });

        // ===== CHAT (AI) =====
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/', [EmployeeChatController::class, 'index'])
                ->name('index');
            Route::post('/', [EmployeeChatController::class, 'store'])
                ->name('store');
            Route::get('{conversation}', [EmployeeChatController::class, 'show'])
                ->name('show');
            Route::post('{conversation}/message', [EmployeeChatController::class, 'sendMessage'])
                ->name('message');
            Route::get('{conversation}/messages', [EmployeeChatController::class, 'getMessages'])
                ->name('messages');
            Route::post('{conversation}/close', [EmployeeChatController::class, 'close'])
                ->name('close');
        });

        // ===== KPI =====
        Route::prefix('kpi')->name('kpi.')->group(function () {
            Route::get('/', [EmployeeKpiController::class, 'index'])
                ->name('index');
            Route::get('{snapshot}', [EmployeeKpiController::class, 'show'])
                ->name('show');
            Route::post('{snapshot}/explain', [EmployeeKpiController::class, 'explain'])
                ->name('explain');
            Route::get('{snapshot}/recommendations', [EmployeeKpiController::class, 'recommendations'])
                ->name('recommendations');
            Route::patch('recommendations/{recommendation}', [EmployeeKpiController::class, 'updateRecommendation'])
                ->name('recommendations.update');
        });

        // ===== POLICIES =====
        Route::prefix('policies')->name('policies.')->group(function () {
            Route::get('/', [EmployeePolicyController::class, 'index'])
                ->name('index');
            Route::get('search', [EmployeePolicyController::class, 'search'])
                ->name('search');
            Route::get('{policy}', [EmployeePolicyController::class, 'show'])
                ->name('show');
            Route::get('{policy}/download', [EmployeePolicyController::class, 'download'])
                ->name('download');
        });

        // ===== RECOGNITION (Эътироф тизими) =====
        Route::prefix('recognition')->name('recognition.')->group(function () {
            Route::get('/', [RecognitionController::class, 'index'])
                ->name('index');
            Route::get('leaderboard', [RecognitionController::class, 'leaderboard'])
                ->name('leaderboard');
            Route::get('nominate', [RecognitionController::class, 'nominate'])
                ->name('nominate');
            Route::post('nominate', [RecognitionController::class, 'storeNomination'])
                ->name('store-nomination');
            Route::get('my-nominations', [RecognitionController::class, 'myNominations'])
                ->name('my-nominations');
            Route::get('my-points', [RecognitionController::class, 'myPoints'])
                ->name('my-points');
            Route::get('hall-of-fame', [RecognitionController::class, 'hallOfFame'])
                ->name('hall-of-fame');
            Route::get('profile/{user}', [RecognitionController::class, 'profile'])
                ->name('profile');
        });

        // ===== DISCIPLINE (Интизомий чоралар) =====
        Route::prefix('discipline')->name('discipline.')->group(function () {
            Route::get('/', [EmployeeDisciplineController::class, 'index'])
                ->name('index');
            Route::get('{disciplinaryAction}', [EmployeeDisciplineController::class, 'show'])
                ->name('show');
            Route::post('{disciplinaryAction}/acknowledge', [EmployeeDisciplineController::class, 'acknowledge'])
                ->name('acknowledge');
            Route::post('{disciplinaryAction}/appeal', [EmployeeDisciplineController::class, 'submitAppeal'])
                ->name('appeal');
        });
    });

    // Маршруты для менеджеров
    Route::middleware(['auth', 'employee', 'employee.role:manager,hr,sysadmin'])->group(function () {
        Route::get('team', [EmployeePortalController::class, 'team'])
            ->name('team');
        Route::get('team/{employee}', [EmployeePortalController::class, 'teamMember'])
            ->name('team.member');
    });
});
