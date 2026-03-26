<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\ApplicationFile;
use App\Observers\ApplicationObserver;
use App\Observers\ApplicationFileObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Автоматическая обработка новых заявок
        Application::observe(ApplicationObserver::class);

        // При загрузке нового файла - сбросить старый анализ
        ApplicationFile::observe(ApplicationFileObserver::class);
    }
}
