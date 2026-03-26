@extends('layouts.admin')

@section('title', 'Вакансии')
@section('header', 'Управление вакансиями')

@section('header-actions')
<a href="{{ route('admin.vacancies.create') }}" class="btn btn-primary">
    <i class="fa-solid fa-plus"></i> Создать вакансию
</a>
@endsection

@section('content')
<!-- Filters -->
<div class="card mb-3">
    <div class="card-body" style="padding: 16px 20px;">
        <form action="{{ route('admin.vacancies.index') }}" method="GET" class="d-flex gap-2 align-items-center" style="flex-wrap: wrap;">
            <div style="position: relative; flex: 1; min-width: 200px; max-width: 300px;">
                <i class="fa-solid fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--fg-3);"></i>
                <input type="text" name="search" class="form-control" placeholder="Поиск вакансий..." value="{{ request('search') }}" style="padding-left: 40px;">
            </div>
            <select name="active" class="form-control" style="width: 160px;" onchange="this.form.submit()">
                <option value="">Все статусы</option>
                <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Активные</option>
                <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Неактивные</option>
            </select>
            <button type="submit" class="btn btn-secondary">
                <i class="fa-solid fa-filter"></i> Фильтр
            </button>
            @if(request('search') || request('active'))
            <a href="{{ route('admin.vacancies.index') }}" class="btn btn-outline">
                <i class="fa-solid fa-times"></i> Сбросить
            </a>
            @endif
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Вакансия</th>
                    <th>Локация</th>
                    <th>Тип</th>
                    <th style="text-align: center;">Заявок</th>
                    <th>Статус</th>
                    <th>Создано</th>
                    <th style="width: 50px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($vacancies as $vacancy)
                    <tr class="clickable" onclick="window.location='{{ route('admin.vacancies.show', $vacancy) }}'">
                        <td>
                            <div style="font-weight: 600; color: var(--fg-1);">{{ $vacancy->title }}</div>
                            @if($vacancy->salary_formatted)
                                <small style="color: var(--good);">{{ $vacancy->salary_formatted }}</small>
                            @endif
                        </td>
                        <td>
                            @if($vacancy->location)
                                <span style="display: flex; align-items: center; gap: 6px;">
                                    <i class="fa-solid fa-location-dot" style="color: var(--fg-3); font-size: 12px;"></i>
                                    {{ $vacancy->location }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge" style="background: var(--grid); color: var(--fg-2); border: 1px solid var(--br);">
                                {{ $vacancy->employment_type_label }}
                            </span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge" style="background: var(--accent); color: white; min-width: 28px;">
                                {{ $vacancy->applications_count }}
                            </span>
                        </td>
                        <td>
                            @if($vacancy->is_active)
                                <span class="badge badge-hired">
                                    <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Активна
                                </span>
                            @else
                                <span class="badge" style="background: var(--grid); color: var(--fg-3);">
                                    <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Неактивна
                                </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $vacancy->created_at->format('d.m.Y') }}</small>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline" onclick="this.nextElementSibling.classList.toggle('show')">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('admin.vacancies.show', $vacancy) }}">
                                        <i class="fa-solid fa-eye"></i> Просмотр
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.vacancies.edit', $vacancy) }}">
                                        <i class="fa-solid fa-pen"></i> Редактировать
                                    </a>
                                    <form action="{{ route('admin.vacancies.toggle', $vacancy) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fa-solid fa-{{ $vacancy->is_active ? 'pause' : 'play' }}"></i>
                                            {{ $vacancy->is_active ? 'Деактивировать' : 'Активировать' }}
                                        </button>
                                    </form>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="{{ route('vacant.show', $vacancy) }}" target="_blank">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i> Открыть на сайте
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fa-solid fa-briefcase"></i>
                                <h3>Вакансии не найдены</h3>
                                <p class="text-muted">Создайте первую вакансию, чтобы начать поиск кандидатов</p>
                                <a href="{{ route('admin.vacancies.create') }}" class="btn btn-primary" style="margin-top: 16px;">
                                    <i class="fa-solid fa-plus"></i> Создать вакансию
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($vacancies->hasPages())
<div class="mt-3" style="display: flex; justify-content: center;">
    {{ $vacancies->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
    }
});
</script>
@endpush
