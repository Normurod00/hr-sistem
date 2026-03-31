@extends('employee.layouts.app')

@section('title', 'Настройки')
@section('page-title', 'Настройки профиля')

@section('content')
<div class="row g-4">
    <!-- Profile Info -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="avatar bg-primary text-white mx-auto mb-3"
                     style="width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 600;">
                    {{ $user->initials }}
                </div>
                <h5 class="mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-1">{{ $employee->position ?? 'Сотрудник' }}</p>
                <p class="text-muted small mb-0">{{ $employee->department ?? '' }}</p>
            </div>
            <div class="list-group list-group-flush">
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Табельный номер</span>
                    <strong>{{ $employee->employee_number ?? '—' }}</strong>
                </div>
                <div class="list-group-item d-flex justify-content-between">
                    <span class="text-muted">Email</span>
                    <strong>{{ $user->email }}</strong>
                </div>
                @if($employee->phone_internal)
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Внутренний телефон</span>
                        <strong>{{ $employee->phone_internal }}</strong>
                    </div>
                @endif
                @if($employee->office_location)
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Офис</span>
                        <strong>{{ $employee->office_location }}</strong>
                    </div>
                @endif
                @if($employee->hire_date)
                    <div class="list-group-item d-flex justify-content-between">
                        <span class="text-muted">Дата приёма</span>
                        <strong>{{ $employee->hire_date->format('d.m.Y') }}</strong>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Notifications Settings -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="bi bi-bell me-2"></i>Уведомления</h5>
            </div>
            <div class="card-body">
                <form id="notificationsForm">
                    @csrf
                    @php
                        $prefs = $user->notification_preferences ?? [];
                    @endphp

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="emailNotifications"
                               name="email_notifications" value="1"
                               {{ ($prefs['email_notifications'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="emailNotifications">
                            Email-уведомления
                        </label>
                        <div class="form-text">Получать уведомления на email</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="kpiAlerts"
                               name="kpi_alerts" value="1"
                               {{ ($prefs['kpi_alerts'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="kpiAlerts">
                            Оповещения о KPI
                        </label>
                        <div class="form-text">Уведомления при изменении KPI показателей</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="recommendationReminders"
                               name="recommendation_reminders" value="1"
                               {{ ($prefs['recommendation_reminders'] ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="recommendationReminders">
                            Напоминания о рекомендациях
                        </label>
                        <div class="form-text">Напоминания о невыполненных рекомендациях AI</div>
                    </div>

                    <button type="submit" class="btn btn-brb">
                        <i class="bi bi-check-lg me-2"></i>Сохранить
                    </button>
                </form>
            </div>
        </div>

        <!-- Security -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Безопасность</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Для смены пароля или других настроек безопасности обратитесь в HR отдел.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('notificationsForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Сохранение...';

        try {
            const response = await fetch('{{ route("employee.settings.notifications") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    email_notifications: document.getElementById('emailNotifications').checked,
                    kpi_alerts: document.getElementById('kpiAlerts').checked,
                    recommendation_reminders: document.getElementById('recommendationReminders').checked,
                }),
            });

            if (response.ok) {
                btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Сохранено!';
                btn.classList.remove('btn-brb');
                btn.classList.add('btn-success');
                setTimeout(() => {
                    btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Сохранить';
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-brb');
                    btn.disabled = false;
                }, 2000);
            } else {
                throw new Error('Ошибка сохранения');
            }
        } catch (error) {
            btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Сохранить';
            btn.disabled = false;
            alert('Не удалось сохранить настройки');
        }
    });
</script>
@endpush
