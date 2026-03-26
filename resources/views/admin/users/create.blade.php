@extends('layouts.admin')

@section('title', 'Создать пользователя')
@section('header', 'Создать пользователя')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf

                    <h6 class="text-muted mb-3">Основные данные</h6>

                    <div class="mb-3">
                        <label for="name" class="form-label">Полное имя <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">Роль в системе <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->value }}" {{ old('role') == $role->value ? 'selected' : '' }}>
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
                                       id="department" name="department" value="{{ old('department') }}">
                                @error('department')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="position" class="form-label">Должность</label>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                       id="position" name="position" value="{{ old('position') }}">
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
                                        <option value="{{ $eRole->value }}" {{ old('employee_role') == $eRole->value ? 'selected' : '' }}>
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
                                        <option value="{{ $manager->id }}" {{ old('manager_id') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->user?->name }} ({{ $manager->department }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="text-muted mb-3">Пароль для входа</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Пароль <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Подтвердите пароль <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle me-1"></i>
                        После создания сообщите сотруднику логин (<strong>email</strong>) и пароль для входа в систему.
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Назад
                        </a>
                        <button type="submit" class="btn btn-brb">
                            <i class="bi bi-check me-1"></i> Создать
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const employeeFields = document.getElementById('employee-fields');
    const employeeRoleRow = document.getElementById('employee-role-row');

    function toggleFields() {
        const role = roleSelect.value;
        // employee_role selector is only relevant for 'employee' role
        // For hr/admin the role is auto-determined
        employeeRoleRow.style.display = role === 'employee' ? '' : 'none';
    }

    roleSelect.addEventListener('change', toggleFields);
    toggleFields();
});
</script>
@endsection
