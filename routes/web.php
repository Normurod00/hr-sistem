<?php

use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\CandidateAuthController;
use App\Http\Controllers\Auth\EmployeeAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VacancyController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\VacancyController as AdminVacancyController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CandidateController;
use App\Http\Controllers\Admin\AiSettingsController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Admin\QualifiedCandidatesController;
use App\Http\Controllers\Admin\VideoMeetingController;
use App\Http\Controllers\Admin\IntegrationController;
use App\Http\Controllers\Admin\PolicyController as AdminPolicyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RecognitionController as AdminRecognitionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Employee Portal Routes (included from separate file)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/employee.php';

/*
|--------------------------------------------------------------------------
| Home - перенаправление по роли
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->role === \App\Enums\UserRole::Candidate) {
            return redirect()->route('vacant.index');
        }
        return redirect()->route('employee.dashboard');
    }
    // Гость -> показать страницу входа для сотрудников
    return app(\App\Http\Controllers\Auth\EmployeeAuthController::class)->showLoginForm();
})->name('home');

/*
|--------------------------------------------------------------------------
| Candidate Routes (Кандидатлар - /vacant)
|--------------------------------------------------------------------------
*/
Route::prefix('vacant')->name('vacant.')->group(function () {
    Route::get('/', [VacancyController::class, 'index'])->name('index');
    Route::get('/{vacancy}', [VacancyController::class, 'show'])->name('show')
        ->where('vacancy', '[0-9]+');
});

// Обратная совместимость - старые URL
Route::get('/vacancies', fn() => redirect()->route('vacant.index'));
Route::get('/vacancies/{vacancy}', fn($vacancy) => redirect()->route('vacant.show', $vacancy));

/*
|--------------------------------------------------------------------------
| Guest Routes (only for non-authenticated users)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Кандидаты - вход и регистрация
    Route::get('/vacant/login', [CandidateAuthController::class, 'showLoginForm'])->name('candidate.login');
    Route::post('/vacant/login', [CandidateAuthController::class, 'login']);
    Route::get('/vacant/register', [RegisterController::class, 'showRegistrationForm'])->name('candidate.register');
    Route::post('/vacant/register', [RegisterController::class, 'register']);

    // Сотрудники - вход (email/пароль)
    Route::get('/login', [EmployeeAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [EmployeeAuthController::class, 'login']);

    // Админ - вход (email/пароль)
    Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Candidate Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('candidate')->group(function () {
        // Apply to vacancy
        Route::get('/vacancies/{vacancy}/apply', [ApplicationController::class, 'create'])->name('applications.create');
        Route::post('/vacancies/{vacancy}/apply', [ApplicationController::class, 'store'])->name('applications.store');
    });

    // Profile (for all authenticated users)
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');

        // My applications (for candidates)
        Route::get('/applications', [ApplicationController::class, 'myApplications'])->name('applications');
        Route::get('/applications/{application}', [ApplicationController::class, 'showMyApplication'])->name('applications.show');
    });

    // Tests for candidates
    Route::prefix('tests')->name('tests.')->group(function () {
        Route::get('/{application}', [TestController::class, 'show'])->name('show');
        Route::post('/{application}/start', [TestController::class, 'start'])->name('start');
        Route::get('/{application}/status', [TestController::class, 'status'])->name('status');
        Route::post('/{application}/submit', [TestController::class, 'submit'])->name('submit');
        Route::get('/{application}/results', [TestController::class, 'results'])->name('results');
    });

    // Chat for candidates (only for invited/hired)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/{application}', [ChatController::class, 'show'])->name('show');
        Route::post('/{application}/send', [ChatController::class, 'sendMessage'])->name('send');
        Route::get('/{application}/messages', [ChatController::class, 'getMessages'])->name('messages');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Routes (HR & Admin only)
|--------------------------------------------------------------------------
*/
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Vacancies CRUD
        Route::resource('vacancies', AdminVacancyController::class);
        Route::post('/vacancies/{vacancy}/toggle', [AdminVacancyController::class, 'toggleActive'])->name('vacancies.toggle');

        // Applications
        Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
        Route::get('/applications/{application}', [AdminApplicationController::class, 'show'])->name('applications.show');
        Route::post('/applications/{application}/status', [AdminApplicationController::class, 'updateStatus'])->name('applications.status');
        Route::post('/applications/{application}/reanalyze', [AdminApplicationController::class, 'reanalyze'])->name('applications.reanalyze');
        Route::delete('/applications/{application}', [AdminApplicationController::class, 'destroy'])->name('applications.destroy');
        Route::post('/applications/bulk-status', [AdminApplicationController::class, 'bulkUpdateStatus'])->name('applications.bulk-status');

        // Qualified Candidates (подходящие кандидаты)
        Route::get('/qualified-candidates', [QualifiedCandidatesController::class, 'index'])->name('qualified.index');
        Route::post('/qualified-candidates/{application}/invite', [QualifiedCandidatesController::class, 'inviteToChat'])->name('qualified.invite');
        Route::post('/qualified-candidates/bulk-invite', [QualifiedCandidatesController::class, 'bulkInvite'])->name('qualified.bulk-invite');

        // Users
        Route::resource('users', UserController::class);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

        // Candidates (Kanban Board)
        Route::get('/candidates', [CandidateController::class, 'index'])->name('candidates.index');
        Route::get('/candidates/{candidate}', [CandidateController::class, 'show'])->name('candidates.show');
        Route::get('/candidates/{candidate}/edit', [CandidateController::class, 'edit'])->name('candidates.edit');
        Route::put('/candidates/{candidate}', [CandidateController::class, 'update'])->name('candidates.update');
        Route::post('/candidates/{candidate}/reset-password', [CandidateController::class, 'resetPassword'])->name('candidates.reset-password');
        Route::delete('/candidates/{candidate}', [CandidateController::class, 'destroy'])->name('candidates.destroy');

        // AI Settings
        Route::get('/ai/settings', [AiSettingsController::class, 'index'])->name('ai.settings');
        Route::post('/ai/settings', [AiSettingsController::class, 'update'])->name('ai.settings.update');
        Route::get('/ai/logs', [AiSettingsController::class, 'logs'])->name('ai.logs');
        Route::post('/ai/health', [AiSettingsController::class, 'checkHealth'])->name('ai.health');

        // Chat with candidates
        Route::prefix('chat')->name('chat.')->group(function () {
            Route::get('/', [AdminChatController::class, 'index'])->name('index');
            Route::get('/{application}', [AdminChatController::class, 'show'])->name('show');
            Route::post('/{application}/send', [AdminChatController::class, 'sendMessage'])->name('send');
            Route::get('/{application}/messages', [AdminChatController::class, 'getMessages'])->name('messages');
            Route::post('/{application}/meeting', [AdminChatController::class, 'createMeeting'])->name('meeting.create');
            Route::post('/meeting/{meeting}/cancel', [AdminChatController::class, 'cancelMeeting'])->name('meeting.cancel');
        });

        // Video Meetings
        Route::prefix('meetings')->name('meetings.')->group(function () {
            Route::get('/', [VideoMeetingController::class, 'index'])->name('index');
            Route::get('/create', [VideoMeetingController::class, 'create'])->name('create');
            Route::post('/', [VideoMeetingController::class, 'store'])->name('store');
            Route::get('/{meeting}', [VideoMeetingController::class, 'show'])->name('show');
            Route::get('/{meeting}/edit', [VideoMeetingController::class, 'edit'])->name('edit');
            Route::put('/{meeting}', [VideoMeetingController::class, 'update'])->name('update');
            Route::post('/{meeting}/cancel', [VideoMeetingController::class, 'cancel'])->name('cancel');

            // Video Room
            Route::get('/{meeting}/room', [VideoMeetingController::class, 'room'])->name('room');

            // WebRTC API
            Route::post('/{meeting}/signal', [VideoMeetingController::class, 'sendSignal'])->name('signal');
            Route::get('/{meeting}/signals', [VideoMeetingController::class, 'getSignals'])->name('signals');
            Route::get('/{meeting}/participants', [VideoMeetingController::class, 'getParticipants'])->name('participants');
            Route::post('/{meeting}/leave', [VideoMeetingController::class, 'leave'])->name('leave');
            Route::post('/{meeting}/end', [VideoMeetingController::class, 'end'])->name('end');
            Route::post('/{meeting}/toggle-media', [VideoMeetingController::class, 'toggleMedia'])->name('toggle-media');
        });

        // ===== NEW: Integrations (SysAdmin only) =====
        Route::prefix('integrations')->name('integrations.')->group(function () {
            Route::get('/', [IntegrationController::class, 'index'])->name('index');
            Route::post('/{type}/test', [IntegrationController::class, 'test'])->name('test');
            Route::get('/{type}/history', [IntegrationController::class, 'history'])->name('history');
            Route::post('/cleanup', [IntegrationController::class, 'cleanup'])->name('cleanup');
        });

        // ===== NEW: Policies Management =====
        Route::resource('policies', AdminPolicyController::class);
        Route::post('/policies/{policy}/toggle', [AdminPolicyController::class, 'toggle'])->name('policies.toggle');

        // ===== NEW: Audit Logs (SysAdmin only) =====
        Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/export', [AuditLogController::class, 'export'])->name('export');
            Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
        });

        // ===== RECOGNITION (Эътироф тизими) =====
        Route::prefix('recognition')->name('recognition.')->group(function () {
            Route::get('/', [AdminRecognitionController::class, 'index'])->name('index');
            Route::get('/nominations', [AdminRecognitionController::class, 'nominations'])->name('nominations');
            Route::post('/nominations/{nomination}/approve', [AdminRecognitionController::class, 'approveNomination'])->name('approve-nomination');
            Route::post('/nominations/{nomination}/reject', [AdminRecognitionController::class, 'rejectNomination'])->name('reject-nomination');
            Route::post('/nominations/bulk-approve', [AdminRecognitionController::class, 'bulkApprove'])->name('bulk-approve');
            Route::get('/nomination-types', [AdminRecognitionController::class, 'nominationTypes'])->name('nomination-types');
            Route::post('/nomination-types', [AdminRecognitionController::class, 'storeNominationType'])->name('store-nomination-type');
            Route::put('/nomination-types/{nominationType}', [AdminRecognitionController::class, 'updateNominationType'])->name('update-nomination-type');
            Route::get('/awards', [AdminRecognitionController::class, 'awards'])->name('awards');
            Route::get('/awards/create', [AdminRecognitionController::class, 'createAward'])->name('create-award');
            Route::post('/awards', [AdminRecognitionController::class, 'storeAward'])->name('store-award');
            Route::post('/awards/{award}/publish', [AdminRecognitionController::class, 'publishAward'])->name('publish-award');
            Route::post('/awards/{award}/unpublish', [AdminRecognitionController::class, 'unpublishAward'])->name('unpublish-award');
            Route::get('/leaderboard', [AdminRecognitionController::class, 'leaderboard'])->name('leaderboard');
            Route::post('/manual-points', [AdminRecognitionController::class, 'manualPoints'])->name('manual-points');
            Route::post('/recalculate', [AdminRecognitionController::class, 'recalculateBalances'])->name('recalculate');
        });
});
