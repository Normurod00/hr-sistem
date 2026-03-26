<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        // Автоматическая обработка новых резюме и анализ каждые 2 минуты
        $schedule->command('ai:process-pending --batch')
            ->everyTwoMinutes()
            ->withoutOverlapping()
            ->runInBackground()
            ->appendOutputTo(storage_path('logs/ai-scheduler.log'));

        // Очистка старых логов AI раз в день
        $schedule->command('model:prune', ['--model' => 'App\\Models\\AiLog'])
            ->daily()
            ->at('03:00');
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'candidate' => \App\Http\Middleware\EnsureUserIsCandidate::class,
            'admin' => \App\Http\Middleware\EnsureUserCanAccessAdmin::class,
            'auth.api' => \App\Http\Middleware\AuthenticateApiToken::class,
            // Employee Portal middleware
            'employee' => \App\Http\Middleware\EnsureUserIsEmployee::class,
            'employee.role' => \App\Http\Middleware\CheckEmployeeRole::class,
            'audit' => \App\Http\Middleware\AuditEmployeeAction::class,
        ]);

        // Временно ПОЛНОСТЬЮ отключаем CSRF для отладки
        $middleware->validateCsrfTokens(except: [
            '*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
