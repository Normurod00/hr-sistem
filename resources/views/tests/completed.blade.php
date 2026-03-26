@extends('layouts.app')

@section('title', 'Тест уже пройден')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-8">
    <div class="max-w-md mx-auto px-4 text-center">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <!-- Icon -->
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-green-100 flex items-center justify-center">
                <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Тест уже пройден</h1>
            <p class="text-gray-600 mb-6">
                Вы уже прошли тест для этой вакансии. Повторное прохождение невозможно.
            </p>

            <!-- Test Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6 text-left">
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Вакансия:</span>
                        <span class="font-medium text-gray-900">{{ $application->vacancy?->title ?? 'Вакансия удалена' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Результат:</span>
                        <span class="font-bold @if($test->score >= 60) text-green-600 @else text-red-600 @endif">{{ $test->score }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Дата прохождения:</span>
                        <span class="text-gray-900">{{ $test->completed_at?->format('d.m.Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col gap-3">
                <a href="{{ route('tests.results', $application) }}" class="inline-flex items-center justify-center px-6 py-3 bg-[#d6001c] text-white rounded-lg font-medium hover:bg-[#b8001a] transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Посмотреть результаты
                </a>
                <a href="{{ route('profile.applications') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                    </svg>
                    Мои заявки
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
