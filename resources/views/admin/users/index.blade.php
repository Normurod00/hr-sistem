@extends('layouts.admin')

@section('title', 'Пользователи')
@section('header', 'Управление пользователями')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <form action="{{ route('admin.users.index') }}" method="GET" class="d-flex gap-2">
        <input type="text" name="search" class="form-control" placeholder="Поиск по имени или email..." value="{{ request('search') }}" style="width: 300px;">
        <select name="role" class="form-select" style="width: 180px;" onchange="this.form.submit()">
            <option value="">Все роли</option>
            @foreach($roles as $role)
                <option value="{{ $role->value }}" {{ request('role') == $role->value ? 'selected' : '' }}>
                    {{ $role->label() }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline-secondary">
            <i class="bi bi-search"></i>
        </button>
    </form>
    <a href="{{ route('admin.users.create') }}" class="btn btn-brb">
        <i class="bi bi-plus-lg me-1"></i> Добавить сотрудника
    </a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Сотрудник</th>
                    <th>Email</th>
                    <th>Роль</th>
                    <th>Отдел / Должность</th>
                    <th>Регистрация</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $user->avatar_url }}" class="rounded-circle me-2" width="40" height="40">
                                <div>
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    @if($user->phone)
                                        <small class="text-muted">{{ $user->phone }}</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @php
                                $roleColors = [
                                    'employee' => 'bg-info',
                                    'candidate' => 'bg-primary',
                                    'hr' => 'bg-success',
                                    'admin' => 'bg-danger',
                                ];
                            @endphp
                            <span class="badge {{ $roleColors[$user->role->value] ?? 'bg-secondary' }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td>
                            @if($user->employeeProfile)
                                <div>{{ $user->employeeProfile->department ?? '—' }}</div>
                                <small class="text-muted">{{ $user->employeeProfile->position ?? '' }}</small>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $user->created_at->format('d.m.Y') }}</small></td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.users.show', $user) }}">
                                            <i class="bi bi-eye me-2"></i> Просмотр
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                            <i class="bi bi-pencil me-2"></i> Редактировать
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0">Пользователи не найдены</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection
