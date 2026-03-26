@extends('layouts.app')

@section('title', 'Время теста истекло')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-8">
    <div class="max-w-md mx-auto px-4 text-center">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Icon -->
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-red-100 flex items-center justify-center">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Время истекло</h1>
            <p class="text-gray-600 mb-6">
                К сожалению, время на прохождение теста закончилось. Тест был автоматически завершён.
            </p>

            <!-- Test Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Вакансия:</span>
                        <span class="font-medium text-gray-900">{{ $application->vacancy?->title ?? 'Вакансия удалена' }}</span>
                    </div>
                    @if($test->score !== null)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Результат:</span>
                        <span class="font-bold @if($test->score >= 60) text-green-600 @else text-red-600 @endif">{{ $test->score }}%</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500">Лимит времени:</span>
                        <span class="text-gray-900">{{ floor($test->time_limit / 60) }} минут</span>
                    </div>
                </div>
            </div>

            <!-- Message -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6 text-left">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm text-yellow-800">
                            Ваша заявка всё равно будет рассмотрена HR-специалистом. Результаты теста учитываются как дополнительный фактор при принятии решения.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3">
                <a href="{{ route('profile.applications') }}" class="inline-flex items-center justify-center px-6 py-3 bg-[#d6001c] text-white rounded-lg font-medium hover:bg-[#b8001a] transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Мои заявки
                </a>
                <a href="{{ route('vacant.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Другие вакансии
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
