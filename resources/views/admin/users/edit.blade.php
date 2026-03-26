@extends('layouts.admin')

@section('title', 'Редактировать пользователя')
@section('header', 'Редактировать: ' . $user->name)

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <h6 class="text-muted mb-3">Основные данные</h6>

                    <div class="mb-3">
                        <label for="name" class="form-label">Полное имя <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Роль в системе <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->value }}" {{ old('role', $user->role->value) == $role->value ? 'selected' : '' }}>
                                    {{ $role->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Поля сотрудника --}}
                    <div id="employee-fields">
                        <hr>
                        <h6 class="text-muted mb-3">Данные сотрудника</h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Отдел</label>
                                <input type="text" class="form-control @error('department') is-invalid @enderror"
                                       id="department" name="department"
                                       value="{{ old('department', $user->employeeProfile?->department) }}">
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Должность</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                       id="position" name="position"
                                       value="{{ old('position', $user->employeeProfile?->position) }}">
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row" id="employee-role-row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_role" class="form-label">Роль сотрудника</label>
                                <select class="form-select" id="employee_role" name="employee_role">
                                    @foreach($employeeRoles as $eRole)
                                        <option value="{{ $eRole->value }}"
                                            {{ old('employee_role', $user->employeeProfile?->role?->value) == $eRole->value ? 'selected' : '' }}>
                                            {{ $eRole->label() }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="manager_id" class="form-label">Руководитель</label>
                                <select class="form-select" id="manager_id" name="manager_id">
                                    <option value="">-- Нет --</option>
                                    @foreach($managers as $manager)
                                        <option value="{{ $manager->id }}"
                                            {{ old('manager_id', $user->employeeProfile?->manager_id) == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->user?->name }} ({{ $manager->department }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-brb">
                            <i class="bi bi-check me-1"></i> Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <!-- Reset Password -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-key me-2"></i>Сбросить пароль
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Новый пароль</label>
                        <input type="password" class="form-control" id="new_password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="form-label">Подтвердите пароль</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key me-1"></i> Сбросить пароль
                    </button>
                </form>
            </div>
        </div>

        @if($user->employeeProfile)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-person-badge me-2"></i>Информация
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Табельный номер:</strong> {{ $user->employeeProfile->employee_number }}</p>
                    <p class="mb-1"><strong>Статус:</strong>
                        <span class="badge bg-{{ $user->employeeProfile->status->color() }}">
                            {{ $user->employeeProfile->status->label() }}
                        </span>
                    </p>
                    @if($user->employeeProfile->hire_date)
                        <p class="mb-0"><strong>Дата найма:</strong> {{ $user->employeeProfile->hire_date->format('d.m.Y') }}</p>
                    @endif
                </div>
            </div>
        @endif

        <!-- Delete -->
        @if($user->id !== auth()->id())
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <i class="bi bi-exclamation-triangle me-2"></i>Удаление
                </div>
                <div class="card-body">
                    @if($user->applications()->exists())
                        <p class="text-muted mb-0">Нельзя удалить пользователя с заявками.</p>
                    @else
                        <p class="text-muted">Пользователь будет удалён безвозвратно.</p>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                              onsubmit="return confirm('Удалить пользователя?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Удалить
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const employeeRoleRow = document.getElementById('employee-role-row');

    function toggleFields() {
        const role = roleSelect.value;
        employeeRoleRow.style.display = role === 'employee' ? '' : 'none';
    }

    roleSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endsection
